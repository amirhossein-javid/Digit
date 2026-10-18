(() => {
  'use strict';

  const store = window.NovaStore;
  if (!store) return;

  const $ = selector => document.querySelector(selector);
  const params = new URLSearchParams(location.search);
  const stateMarkup = (title, message, action = '') => `<div class="empty-state" role="status"><h2>${title}</h2><p>${message}</p>${action}</div>`;
  const loadingMarkup = message => stateMarkup('در حال بارگذاری', message);
  const errorMarkup = message => stateMarkup(
    'بارگذاری اطلاعات انجام نشد',
    message,
    '<button class="btn btn--primary js-retry" type="button">تلاش دوباره</button>'
  );
  const ORDER_ATTEMPT_KEY = 'nova_order_attempt_v1';
  const ORDER_ATTEMPT_CART = 'nova_order_attempt_cart_v1';
  let memoryOrderAttemptKey = null;
  let memoryOrderAttemptCart = null;
  let checkoutSubmitting = false;
  let orderCompleted = false;

  function generateOrderAttemptKey() {
    if (globalThis.crypto?.randomUUID) return globalThis.crypto.randomUUID();

    const bytes = new Uint8Array(16);
    if (globalThis.crypto?.getRandomValues) globalThis.crypto.getRandomValues(bytes);
    else for (let index = 0; index < bytes.length; index += 1) bytes[index] = Math.floor(Math.random() * 256);
    bytes[6] = (bytes[6] & 0x0f) | 0x40;
    bytes[8] = (bytes[8] & 0x3f) | 0x80;
    const hex = [...bytes].map(value => value.toString(16).padStart(2, '0')).join('');
    return `${hex.slice(0, 8)}-${hex.slice(8, 12)}-${hex.slice(12, 16)}-${hex.slice(16, 20)}-${hex.slice(20)}`;
  }

  function getOrderAttemptKey(cartFingerprint) {
    try {
      const saved = sessionStorage.getItem(ORDER_ATTEMPT_KEY);
      const savedCart = sessionStorage.getItem(ORDER_ATTEMPT_CART);
      if (saved && savedCart === cartFingerprint) return saved;
      const created = generateOrderAttemptKey();
      sessionStorage.setItem(ORDER_ATTEMPT_KEY, created);
      sessionStorage.setItem(ORDER_ATTEMPT_CART, cartFingerprint);
      return created;
    } catch (_) {
      if (!memoryOrderAttemptKey || memoryOrderAttemptCart !== cartFingerprint) {
        memoryOrderAttemptKey = generateOrderAttemptKey();
        memoryOrderAttemptCart = cartFingerprint;
      }
      return memoryOrderAttemptKey;
    }
  }

  function clearOrderAttemptKey() {
    memoryOrderAttemptKey = null;
    memoryOrderAttemptCart = null;
    try {
      sessionStorage.removeItem(ORDER_ATTEMPT_KEY);
      sessionStorage.removeItem(ORDER_ATTEMPT_CART);
    } catch (_) { /* Storage may be unavailable. */ }
  }

  async function populateCategories(selectedCategory) {
    const select = $('#categoryFilter');
    if (!select) return;

    try {
      const categories = await store.fetchCategories();
      if (categories.length) {
        select.innerHTML = '<option value="">همه دسته‌ها</option>' + categories.map(category => {
          const name = store.escapeHtml(category.name);
          return `<option value="${name}">${name}</option>`;
        }).join('');
      }
    } catch (_) {
      // گزینه‌های موجود در HTML در صورت عدم دسترسی موقت به API حفظ می‌شوند.
    }

    select.value = selectedCategory;
  }

  async function renderShop() {
    const grid = $('#shopProducts');
    if (!grid) return;

    const search = params.get('q')?.trim() || '';
    const category = params.get('category') || '';
    const sort = params.get('sort') || 'default';
    const qInput = $('#shopSearch');
    const categorySelect = $('#categoryFilter');
    const sortSelect = $('#sortFilter');
    const count = $('#resultCount');

    if (qInput) qInput.value = search;
    if (categorySelect) categorySelect.value = category;
    if (sortSelect) sortSelect.value = sort;
    grid.innerHTML = loadingMarkup('محصولات فروشگاه در حال دریافت هستند.');
    if (count) count.textContent = 'در حال دریافت…';
    populateCategories(category);

    try {
      const { items, meta } = await store.fetchProducts({
        q: search,
        category,
        sort,
        page: 1,
        limit: 100
      });

      grid.innerHTML = items.length
        ? items.map(store.productCard).join('')
        : stateMarkup('محصولی پیدا نشد', 'عبارت جستجو یا فیلتر دسته‌بندی را تغییر دهید.', '<a class="btn btn--primary" href="shop.html">نمایش همه محصولات</a>');
      if (count) count.textContent = `${store.fa.format(Number(meta.total ?? items.length))} محصول`;
    } catch (_) {
      grid.innerHTML = errorMarkup('ارتباط با سرویس محصولات را بررسی کنید.');
      if (count) count.textContent = 'دریافت ناموفق';
    }
  }

  async function renderRelatedProducts(product) {
    const related = $('#relatedProducts');
    if (!related) return;
    related.innerHTML = loadingMarkup('محصولات مرتبط در حال دریافت هستند.');

    try {
      const categoryResult = await store.fetchProducts({
        category: product.categorySlug || product.category,
        limit: 5
      });
      let items = categoryResult.items.filter(item => item.id !== product.id);

      if (items.length < 4) {
        const catalog = await store.loadCatalog();
        const extra = catalog.items.filter(item => item.id !== product.id && !items.some(relatedItem => relatedItem.id === item.id));
        items = items.concat(extra);
      }

      items = items.slice(0, 4);
      related.innerHTML = items.length
        ? items.map(store.productCard).join('')
        : stateMarkup('محصول مرتبطی وجود ندارد', 'در حال حاضر محصول دیگری برای این دسته ثبت نشده است.');
    } catch (_) {
      related.innerHTML = errorMarkup('دریافت محصولات مرتبط امکان‌پذیر نبود.');
    }
  }

  function setupProductInteractions(root, product) {
    root.querySelectorAll('[data-gallery-view]').forEach(button => button.addEventListener('click', () => {
      const view = button.dataset.galleryView;
      const mainImage = root.querySelector('[data-gallery-main]');
      mainImage.className = `product-main-image view-${view}`;
      root.querySelectorAll('[data-gallery-view]').forEach(item => {
        const selected = item === button;
        item.classList.toggle('active', selected);
        item.setAttribute('aria-pressed', String(selected));
      });
    }));

    root.querySelectorAll('[data-qty-action]').forEach(button => button.addEventListener('click', () => {
      const input = $('#productQuantity');
      const delta = button.dataset.qtyAction === 'plus' ? 1 : -1;
      input.value = String(Math.max(1, Math.min(20, (Number(input.value) || 1) + delta)));
    }));

    $('#productAddForm').addEventListener('submit', event => {
      event.preventDefault();
      store.addToCart(product.id, $('#productQuantity').value);
      showMessage('محصول به سبد خرید افزوده شد.');
    });
  }

  async function renderProduct() {
    const root = $('#productDetail');
    if (!root) return;

    const id = Number(params.get('id'));
    const related = $('#relatedProducts');
    root.innerHTML = loadingMarkup('اطلاعات محصول در حال دریافت است.');
    if (related) related.innerHTML = loadingMarkup('محصولات مرتبط در حال دریافت هستند.');

    if (!Number.isInteger(id) || id < 1) {
      root.innerHTML = stateMarkup('شناسه محصول معتبر نیست', 'از فروشگاه محصول موردنظر را دوباره انتخاب کنید.', '<a class="btn btn--primary" href="shop.html">بازگشت به فروشگاه</a>');
      if (related) related.innerHTML = '';
      return;
    }

    try {
      const product = await store.fetchProduct(id);
      const name = store.escapeHtml(product.name);
      const category = store.escapeHtml(product.category);
      const description = store.escapeHtml(product.description);
      const stock = {
        inquiry: ['وضعیت موجودی: نیازمند استعلام', 'برای اطلاع از موجودی و شرایط عرضه با پشتیبانی تماس بگیرید.'],
        in_stock: ['وضعیت موجودی: موجود', 'این محصول در حال حاضر قابل سفارش است.'],
        out_of_stock: ['وضعیت موجودی: ناموجود', 'این محصول در حال حاضر قابل سفارش نیست.']
      }[product.stockStatus] || ['وضعیت موجودی: نیازمند استعلام', 'برای اطلاع از موجودی و شرایط عرضه با پشتیبانی تماس بگیرید.'];
      const specifications = product.specs.map(([key, value]) => `<div><dt>${store.escapeHtml(key)}</dt><dd>${store.escapeHtml(value)}</dd></div>`).join('');

      document.title = `${product.name} | نُوا`;
      root.innerHTML = `<nav class="breadcrumbs" aria-label="مسیر صفحه"><a href="index.html">خانه</a><span>←</span><a href="shop.html">فروشگاه</a><span>←</span><span>${name}</span></nav>
        <div class="product-detail-grid">
          <section class="product-gallery" aria-label="گالری محصول">
            <div class="product-main-image view-cover" data-gallery-main>${store.picture(product, true)}</div>
            <div class="gallery-controls" role="group" aria-label="انتخاب نمای تصویر">
              <button class="gallery-thumb active" type="button" data-gallery-view="cover" aria-pressed="true" aria-label="نمای کامل محصول">${store.picture(product)}<span>نمای کامل</span></button>
              <button class="gallery-thumb view-detail" type="button" data-gallery-view="detail" aria-pressed="false" aria-label="نمای نزدیک جزئیات محصول">${store.picture(product)}<span>جزئیات</span></button>
              <button class="gallery-thumb view-fit" type="button" data-gallery-view="fit" aria-pressed="false" aria-label="نمای محصول با حاشیه کامل">${store.picture(product)}<span>نمای باز</span></button>
            </div>
          </section>
          <section class="product-summary">
            <span class="detail-category">${category}</span>
            <h1>${name}</h1>
            <p>${description}</p>
            <div class="demo-price"><small>قیمت</small><strong>${store.formatPrice(product.price)}</strong></div>
            <p class="availability-note"><b>${stock[0]}</b> ${stock[1]}</p>
            <form class="purchase-row" id="productAddForm">
              <label>تعداد<span class="quantity-stepper"><button type="button" data-qty-action="minus" aria-label="کاهش تعداد">−</button><input id="productQuantity" type="number" min="1" max="20" value="1" inputmode="numeric" aria-label="تعداد محصول"><button type="button" data-qty-action="plus" aria-label="افزایش تعداد">+</button></span></label>
              <button class="btn btn--primary" type="submit">افزودن به سبد خرید</button>
            </form>
            <a class="text-link" href="contact.html">پرسش درباره انتخاب این محصول ←</a>
          </section>
        </div>
        <section class="spec-panel"><div><span>اطلاعات محصول</span><h2>مشخصات کلیدی</h2><p>ویژگی‌های مهم این مدل را برای مقایسه و انتخاب دقیق‌تر مرور کنید.</p></div><dl>${specifications}</dl></section>`;
      setupProductInteractions(root, product);
      renderRelatedProducts(product);
    } catch (error) {
      if (error instanceof store.ApiError && error.status === 404) {
        root.innerHTML = stateMarkup('محصول پیدا نشد', 'این محصول در کاتالوگ موجود نیست.', '<a class="btn btn--primary" href="shop.html">بازگشت به فروشگاه</a>');
      } else {
        root.innerHTML = errorMarkup('ارتباط با سرویس محصولات را بررسی کنید.');
      }
      if (related) related.innerHTML = '';
    }
  }

  function renderCart() {
    const root = $('#cartPage');
    if (!root) return;
    const lines = store.cartLines();
    if (!lines.length) {
      root.innerHTML = '<div class="empty-state"><h1>سبد خرید شما خالی است</h1><p>محصولات دلخواه را از فروشگاه به سبد اضافه کنید.</p><a class="btn btn--primary" href="shop.html">رفتن به فروشگاه</a></div>';
      return;
    }
    root.innerHTML = `<div class="cart-layout"><section class="cart-list"><h1>سبد خرید</h1>${lines.map(({ product, quantity }) => {
      const name = store.escapeHtml(product.name);
      return `<article class="cart-row" data-cart-id="${product.id}">${store.picture(product)}<div><a href="product.html?id=${product.id}"><b>${name}</b></a><small>${store.escapeHtml(product.category)}</small><span>${store.formatPrice(product.price)}</span></div><label>تعداد<input class="js-cart-qty" type="number" min="0" max="20" value="${quantity}" inputmode="numeric" aria-label="تعداد ${name}"></label><button class="remove-btn js-cart-remove" type="button" aria-label="حذف ${name}">حذف</button></article>`;
    }).join('')}</section><aside class="order-summary"><h2>خلاصه سبد</h2><p><span>تعداد کالا</span><b>${store.fa.format(store.cartCount())}</b></p><p><span>جمع کل</span><b>${store.formatPrice(store.cartTotal())}</b></p><small>تعداد و اقلام سبد را پیش از ادامه بررسی کنید.</small><a class="btn btn--primary" href="checkout.html">ادامه به تسویه‌حساب</a><a class="text-link" href="shop.html">ادامه مرور محصولات</a></aside></div>`;
    root.querySelectorAll('.js-cart-qty').forEach(input => input.addEventListener('change', () => { store.updateCartItem(input.closest('[data-cart-id]').dataset.cartId, input.value); renderCart(); }));
    root.querySelectorAll('.js-cart-remove').forEach(button => button.addEventListener('click', () => { store.updateCartItem(button.closest('[data-cart-id]').dataset.cartId, 0); renderCart(); showMessage('محصول از سبد خرید حذف شد.'); }));
  }

  function renderCheckout() {
    const summary = $('#checkoutSummary');
    const form = $('#checkoutForm');
    if (!summary || !form) return;
    const lines = store.cartLines();
    if (!lines.length) {
      $('.checkout-layout').innerHTML = '<div class="empty-state"><h1>سبد خرید خالی است</h1><p>برای ادامه فرایند خرید ابتدا محصولی به سبد اضافه کنید.</p><a class="btn btn--primary" href="shop.html">انتخاب محصول</a></div>';
      return;
    }
    summary.innerHTML = `<h2>خلاصه سفارش</h2>${lines.map(({ product, quantity }) => `<p><span>${store.escapeHtml(product.name)} × ${store.fa.format(quantity)}</span><b>${store.formatPrice(product.price * quantity)}</b></p>`).join('')}<hr><p><span>جمع کل</span><strong>${store.formatPrice(store.cartTotal())}</strong></p><small>اقلام و تعداد محصولات را پیش از ادامه بررسی کنید.</small>`;
    const submitButton = form.querySelector('[type="submit"]');
    submitButton.textContent = 'ثبت سفارش';
    if (form.dataset.orderBound === 'true') return;
    form.dataset.orderBound = 'true';

    form.addEventListener('submit', async event => {
      event.preventDefault();
      if (checkoutSubmitting) return;
      if (!form.checkValidity()) { form.reportValidity(); return; }
      const phone = $('#checkoutPhone');
      const normalizedPhone = phone.value
        .replace(/[۰-۹]/g, digit => '۰۱۲۳۴۵۶۷۸۹'.indexOf(digit))
        .replace(/[٠-٩]/g, digit => '٠١٢٣٤٥٦٧٨٩'.indexOf(digit))
        .replace(/[\s\-()]+/g, '');
      if (!/^09\d{9}$/.test(normalizedPhone)) {
        phone.setCustomValidity('شماره موبایل را با ۰۹ و ۱۱ رقم وارد کنید.');
        phone.reportValidity();
        phone.addEventListener('input', () => phone.setCustomValidity(''), { once: true });
        return;
      }

      const currentLines = store.cartLines();
      if (!currentLines.length) {
        renderCheckout();
        return;
      }

      checkoutSubmitting = true;
      submitButton.disabled = true;
      submitButton.setAttribute('aria-busy', 'true');
      submitButton.textContent = 'در حال ثبت سفارش…';
      const formData = new FormData(form);
      const payload = {
        customer: {
          name: formData.get('name'),
          phone: formData.get('phone'),
          email: formData.get('email'),
          city: formData.get('city'),
          address: formData.get('address'),
          postalCode: formData.get('postal')
        },
        items: currentLines.map(({ product, quantity }) => ({ productId: product.id, quantity }))
      };

      try {
        const cartFingerprint = JSON.stringify(payload.items);
        const result = await store.createOrder(payload, getOrderAttemptKey(cartFingerprint));
        orderCompleted = true;
        const success = $('#checkoutSuccess');
        success.querySelector('h2').textContent = 'سفارش شما ثبت شد';
        success.querySelector('p').textContent = `شماره سفارش ${result.orderNumber} با موفقیت ثبت شد.`;
        form.hidden = true;
        success.hidden = false;
        store.clearCart();
        clearOrderAttemptKey();
        window.scrollTo({ top: 0, behavior: 'smooth' });
      } catch (error) {
        showMessage(error instanceof store.ApiError ? error.message : 'ثبت سفارش انجام نشد. لطفاً دوباره تلاش کنید.');
        checkoutSubmitting = false;
        submitButton.disabled = false;
        submitButton.removeAttribute('aria-busy');
        submitButton.textContent = 'ثبت سفارش';
      }
    });
  }

  function setupContactForm() {
    const form = $('#contactForm');
    if (!form) return;
    form.addEventListener('submit', event => {
      event.preventDefault();
      if (!form.checkValidity()) { form.reportValidity(); return; }
      form.reset();
      $('#contactResult').hidden = false;
    });
  }

  function showMessage(message) {
    const toast = $('.toast');
    if (!toast) return;
    toast.textContent = message;
    toast.classList.add('show');
    clearTimeout(showMessage.timer);
    showMessage.timer = setTimeout(() => toast.classList.remove('show'), 2500);
  }

  renderShop();
  renderProduct();
  renderCart();
  renderCheckout();
  setupContactForm();
  document.addEventListener('click', event => {
    if (event.target.closest('.js-retry')) location.reload();
  });
  document.addEventListener('nova:cart-change', () => { if (!orderCompleted && $('#checkoutSummary')) renderCheckout(); });
})();
