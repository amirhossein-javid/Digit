(() => {
  'use strict';

  const store = window.NovaStore;
  if (!store) return;

  const $ = selector => document.querySelector(selector);
  const params = new URLSearchParams(location.search);

  function renderShop() {
    const grid = $('#shopProducts');
    if (!grid) return;
    const search = params.get('q')?.trim() || '';
    const category = params.get('category') || '';
    const sort = params.get('sort') || 'default';
    const qInput = $('#shopSearch');
    const categorySelect = $('#categoryFilter');
    const sortSelect = $('#sortFilter');
    if (qInput) qInput.value = search;
    if (categorySelect) categorySelect.value = category;
    if (sortSelect) sortSelect.value = sort;

    let list = store.products.filter(product => {
      const matchesSearch = !search || `${product.name} ${product.category}`.toLocaleLowerCase('fa').includes(search.toLocaleLowerCase('fa'));
      return matchesSearch && (!category || product.category === category);
    });
    if (sort === 'price-asc') list.sort((a, b) => a.price - b.price);
    if (sort === 'price-desc') list.sort((a, b) => b.price - a.price);
    if (sort === 'name') list.sort((a, b) => a.name.localeCompare(b.name, 'fa'));

    grid.innerHTML = list.length ? list.map(store.productCard).join('') : '<div class="empty-state"><h2>محصولی پیدا نشد</h2><p>عبارت جستجو یا فیلتر دسته‌بندی را تغییر دهید.</p><a class="btn btn--primary" href="shop.html">نمایش همه محصولات</a></div>';
    const count = $('#resultCount');
    if (count) count.textContent = `${store.fa.format(list.length)} محصول`;
  }

  function renderProduct() {
    const root = $('#productDetail');
    if (!root) return;
    const product = store.getProduct(params.get('id')) || store.products[0];
    document.title = `${product.name} | نُوا`;
    root.innerHTML = `<nav class="breadcrumbs" aria-label="مسیر صفحه"><a href="index.html">خانه</a><span>←</span><a href="shop.html">فروشگاه</a><span>←</span><span>${product.name}</span></nav>
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
          <span class="detail-category">${product.category}</span>
          <h1>${product.name}</h1>
          <p>${product.description}</p>
          <div class="demo-price"><small>قیمت</small><strong>${store.formatPrice(product.price)}</strong></div>
          <p class="availability-note"><b>وضعیت موجودی: نیازمند استعلام</b> برای اطلاع از موجودی و شرایط عرضه با پشتیبانی تماس بگیرید.</p>
          <form class="purchase-row" id="productAddForm">
            <label>تعداد<span class="quantity-stepper"><button type="button" data-qty-action="minus" aria-label="کاهش تعداد">−</button><input id="productQuantity" type="number" min="1" max="20" value="1" inputmode="numeric" aria-label="تعداد محصول"><button type="button" data-qty-action="plus" aria-label="افزایش تعداد">+</button></span></label>
            <button class="btn btn--primary" type="submit">افزودن به سبد خرید</button>
          </form>
          <a class="text-link" href="contact.html">پرسش درباره انتخاب این محصول ←</a>
        </section>
      </div>
      <section class="spec-panel"><div><span>اطلاعات محصول</span><h2>مشخصات کلیدی</h2><p>ویژگی‌های مهم این مدل را برای مقایسه و انتخاب دقیق‌تر مرور کنید.</p></div><dl>${product.specs.map(([key, value]) => `<div><dt>${key}</dt><dd>${value}</dd></div>`).join('')}</dl></section>`;
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
    const related = $('#relatedProducts');
    if (related) related.innerHTML = store.products.filter(item => item.id !== product.id && item.category === product.category).concat(store.products.filter(item => item.id !== product.id && item.category !== product.category)).slice(0, 4).map(store.productCard).join('');
  }

  function renderCart() {
    const root = $('#cartPage');
    if (!root) return;
    const lines = store.cartLines();
    if (!lines.length) {
      root.innerHTML = '<div class="empty-state"><h1>سبد خرید شما خالی است</h1><p>محصولات دلخواه را از فروشگاه به سبد اضافه کنید.</p><a class="btn btn--primary" href="shop.html">رفتن به فروشگاه</a></div>';
      return;
    }
    root.innerHTML = `<div class="cart-layout"><section class="cart-list"><h1>سبد خرید</h1>${lines.map(({ product, quantity }) => `<article class="cart-row" data-cart-id="${product.id}">${store.picture(product)}<div><a href="product.html?id=${product.id}"><b>${product.name}</b></a><small>${product.category}</small><span>${store.formatPrice(product.price)}</span></div><label>تعداد<input class="js-cart-qty" type="number" min="0" max="20" value="${quantity}" inputmode="numeric" aria-label="تعداد ${product.name}"></label><button class="remove-btn js-cart-remove" type="button" aria-label="حذف ${product.name}">حذف</button></article>`).join('')}</section><aside class="order-summary"><h2>خلاصه سبد</h2><p><span>تعداد کالا</span><b>${store.fa.format(store.cartCount())}</b></p><p><span>جمع کل</span><b>${store.formatPrice(store.cartTotal())}</b></p><small>تعداد و اقلام سبد را پیش از ادامه بررسی کنید.</small><a class="btn btn--primary" href="checkout.html">ادامه به تسویه‌حساب</a><a class="text-link" href="shop.html">ادامه مرور محصولات</a></aside></div>`;
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
    summary.innerHTML = `<h2>خلاصه سفارش</h2>${lines.map(({ product, quantity }) => `<p><span>${product.name} × ${store.fa.format(quantity)}</span><b>${store.formatPrice(product.price * quantity)}</b></p>`).join('')}<hr><p><span>جمع کل</span><strong>${store.formatPrice(store.cartTotal())}</strong></p><small>اقلام و تعداد محصولات را پیش از ادامه بررسی کنید.</small>`;
    form.addEventListener('submit', event => {
      event.preventDefault();
      if (!form.checkValidity()) { form.reportValidity(); return; }
      const phone = $('#checkoutPhone');
      if (!/^09\d{9}$/.test(phone.value.replace(/[۰-۹]/g, digit => '۰۱۲۳۴۵۶۷۸۹'.indexOf(digit)))) {
        phone.setCustomValidity('شماره موبایل را با ۰۹ و ۱۱ رقم وارد کنید.');
        phone.reportValidity();
        phone.addEventListener('input', () => phone.setCustomValidity(''), { once: true });
        return;
      }
      form.hidden = true;
      $('#checkoutSuccess').hidden = false;
      window.scrollTo({ top: 0, behavior: 'smooth' });
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
  document.addEventListener('nova:cart-change', () => { if ($('#checkoutSummary')) renderCheckout(); });
})();
