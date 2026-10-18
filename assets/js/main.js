(() => {
  'use strict';

  function renderSharedLayout() {
    const file = location.pathname.split('/').pop() || 'index.html';
    const page = document.body.dataset.page || (file === 'index.html' ? 'home' : file.replace('.html', ''));
    const activePage = page === 'product' ? 'shop' : (page === 'checkout' ? 'cart' : page);
    const navLink = (href, label, key) => `<a${activePage === key ? ' class="active" aria-current="page"' : ''} href="${href}">${label}</a>`;
    const headerRoot = document.querySelector('[data-site-header]');
    const footerRoot = document.querySelector('[data-site-footer]');

    if (headerRoot) {
      headerRoot.outerHTML = `<div class="announcement"><div class="container announcement__inner"><span>🚚 ارسال سریع و رایگان برای سفارش‌های واجد شرایط</span><div><a href="about.html">درباره نُوا</a><span class="sep"></span><a href="contact.html">پشتیبانی و تماس</a></div></div></div>
      <header class="site-header">
        <div class="container header-main">
          <a class="brand" href="index.html" aria-label="صفحه اصلی نُوا"><i>×</i><strong>نُوا</strong></a>
          <nav class="desktop-nav" aria-label="منوی اصلی">${navLink('index.html', 'خانه', 'home')}${navLink('shop.html', 'فروشگاه', 'shop')}${navLink('about.html', 'درباره ما', 'about')}${navLink('contact.html', 'تماس با ما', 'contact')}</nav>
          <div class="header-actions">
            <form class="search search-form" action="shop.html"><input name="q" type="search" placeholder="جستجو میان محصولات..." aria-label="جستجوی محصولات"><button type="submit" aria-label="جستجو">⌕</button></form>
            <a class="icon-btn cart-toggle${activePage === 'cart' ? ' active' : ''}" href="cart.html" aria-label="سبد خرید"${activePage === 'cart' ? ' aria-current="page"' : ''}>🛒<b data-cart-count>۰</b></a>
            <button class="menu-toggle" type="button" aria-label="نمایش منو" aria-expanded="false"><span></span><span></span><span></span></button>
          </div>
        </div>
        <form class="mobile-search container search-form" action="shop.html"><input name="q" type="search" placeholder="جستجو میان محصولات..." aria-label="جستجوی محصولات در موبایل"><button type="submit" aria-label="جستجو">⌕</button></form>
        <nav class="mobile-nav" aria-label="منوی موبایل">${navLink('index.html', 'خانه', 'home')}${navLink('shop.html', 'فروشگاه', 'shop')}${navLink('about.html', 'درباره ما', 'about')}${navLink('contact.html', 'تماس با ما', 'contact')}${navLink('cart.html', 'سبد خرید', 'cart')}</nav>
      </header>`;
    }

    if (footerRoot) {
      footerRoot.outerHTML = `<footer id="footer"><div class="container footer-grid"><div class="footer-brand"><a class="brand brand--light" href="index.html"><i>×</i><strong>نُوا</strong></a><p>مرجعی برای بررسی و انتخاب محصولات دیجیتال متناسب با نیاز شما.</p></div><div><h3>فروشگاه</h3><a href="shop.html">همه محصولات</a><a href="shop.html?category=لپ‌تاپ">لپ‌تاپ</a><a href="shop.html?category=صوتی">محصولات صوتی</a><a href="cart.html">سبد خرید</a></div><div><h3>خدمات مشتریان</h3><a href="about.html#buying-guide">راهنمای انتخاب</a><a href="cart.html">مدیریت سبد خرید</a><a href="checkout.html">تسویه‌حساب</a><a href="contact.html">پشتیبانی</a></div><div><h3>درباره نُوا</h3><a href="about.html">درباره ما</a><a href="contact.html">تماس با ما</a><a href="index.html#categories">دسته‌بندی‌ها</a><a href="index.html#new">جدیدترین‌ها</a></div><div><h3>دسترسی سریع</h3><a href="index.html#deals">پیشنهادها</a><a href="index.html#brands">کالکشن‌ها</a><a href="shop.html?category=گیمینگ">گیمینگ</a><a href="shop.html?category=دوربین">دوربین</a></div><div><h3>فرایند خرید</h3><div class="payments"><span>سبد خرید</span><span>خلاصه سفارش</span><span>بررسی نهایی</span></div></div></div><div class="container copyright"><span>© ۱۴۰۵ نُوا. تمامی حقوق محفوظ است.</span><span>فروشگاه محصولات دیجیتال</span></div></footer>`;
    }
  }

  renderSharedLayout();

  const $ = selector => document.querySelector(selector);
  const $$ = selector => document.querySelectorAll(selector);
  const store = window.NovaStore;
  const isHome = document.body.classList.contains('home-page');

  const homeProductName = product => product.name;
  const homeProductCard = product => {
    const name = homeProductName(product);
    return `<article class="product-card" data-id="${product.id}">
      <a class="product-image" href="product.html?id=${product.id}" aria-label="مشاهده ${name}">${store.picture(product)}</a>
      <small class="product-category">${product.category}</small>
      <h3><a href="product.html?id=${product.id}">${name}</a></h3>
      <div class="price-row"><strong>${store.formatPrice(product.price)}</strong></div>
      <div class="product-meta"><a class="details-link" href="product.html?id=${product.id}">جزئیات</a><button class="add-btn js-add-cart" type="button" data-product-id="${product.id}" aria-label="افزودن ${name} به سبد خرید">+</button></div>
    </article>`;
  };

  if (store) {
    store.updateCartBadges();
    const featured = $('#dealProducts');
    const newest = $('#newProducts');
    const best = $('#bestProducts');
    if (featured) featured.innerHTML = store.products.slice(0, 6).map(isHome ? homeProductCard : store.productCard).join('');
    if (newest) newest.innerHTML = store.products.slice(6, 12).map(isHome ? homeProductCard : store.productCard).join('');
    if (best) best.innerHTML = store.products.slice(0, 5).map((product, index) => {
      const name = isHome ? homeProductName(product) : product.name;
      return `<article class="best-card" data-id="${product.id}"><span class="best-rank">۰${index + 1}</span><a href="product.html?id=${product.id}">${store.picture(product)}</a><div><h3><a href="product.html?id=${product.id}">${name}</a></h3><small>${product.category}</small><strong>${store.formatPrice(product.price)}</strong></div><button class="add-best js-add-cart" type="button" data-product-id="${product.id}" aria-label="افزودن ${name} به سبد خرید">+</button></article>`;
    }).join('');
  }

  const toast = $('.toast');
  function showToast(message) {
    if (!toast) return;
    toast.textContent = message;
    toast.classList.add('show');
    clearTimeout(showToast.timer);
    showToast.timer = setTimeout(() => toast.classList.remove('show'), 2300);
  }

  document.addEventListener('click', event => {
    const addButton = event.target.closest('.js-add-cart');
    if (addButton && store) {
      store.addToCart(addButton.dataset.productId, 1);
      showToast('محصول به سبد خرید افزوده شد.');
    }
  });

  const menuButton = $('.menu-toggle');
  const mobileNav = $('.mobile-nav');
  const overlay = $('.overlay');
  function setMenu(open) {
    if (!menuButton || !mobileNav) return;
    mobileNav.classList.toggle('open', open);
    menuButton.setAttribute('aria-expanded', String(open));
    if (overlay) overlay.classList.toggle('show', open);
  }
  menuButton?.addEventListener('click', () => setMenu(!mobileNav.classList.contains('open')));
  mobileNav?.addEventListener('click', () => setMenu(false));
  overlay?.addEventListener('click', () => setMenu(false));
  window.addEventListener('scroll', () => $('.site-header')?.classList.toggle('scrolled', scrollY > 20), { passive: true });

  const slides = [
    { kicker: 'نسل جدید', title: 'تکنولوژی آینده،<br>همین امروز.', copy: 'محصولات دیجیتال را با اطلاعات روشن بررسی و برای انتخاب دقیق‌تر مقایسه کنید.' },
    { kicker: 'انتخاب آگاهانه', title: 'جزئیات مهم را<br>پیش از خرید ببین.', copy: 'مدل، سازگاری و نیاز واقعی خود را کنار هم قرار دهید و بعد تصمیم بگیرید.' },
    { kicker: 'انتخاب تازه', title: 'محصول مناسب را<br>ساده‌تر پیدا کن.', copy: 'دسته‌بندی‌های متنوع را مرور کنید و مشخصات محصولات را کنار هم ببینید.' }
  ];
  const hero = $('.hero');
  const heroContent = $('.hero-content');
  let slideIndex = 0;
  function showSlide(nextIndex) {
    if (!heroContent) return;
    slideIndex = (nextIndex + slides.length) % slides.length;
    const slide = slides[slideIndex];
    heroContent.classList.add('changing');
    setTimeout(() => {
      heroContent.querySelector('.eyebrow').textContent = slide.kicker;
      heroContent.querySelector('h1').innerHTML = slide.title;
      heroContent.querySelector('p').textContent = slide.copy;
      const current = $('#slideNow');
      if (current) current.textContent = `۰${slideIndex + 1}`;
      heroContent.classList.remove('changing');
    }, 180);
  }
  $('.slider-arrow.next')?.addEventListener('click', () => showSlide(slideIndex + 1));
  $('.slider-arrow.prev')?.addEventListener('click', () => showSlide(slideIndex - 1));
  if (hero && !matchMedia('(prefers-reduced-motion: reduce)').matches) {
    let timer = setInterval(() => showSlide(slideIndex + 1), 6500);
    hero.addEventListener('mouseenter', () => clearInterval(timer));
    hero.addEventListener('focusin', () => clearInterval(timer));
  }

  let remaining = 7 * 3600 + 45 * 60 + 32;
  const countdownNodes = { hours: $('#hours'), minutes: $('#minutes'), seconds: $('#seconds') };
  if (countdownNodes.hours && countdownNodes.minutes && countdownNodes.seconds && store) {
    setInterval(() => {
      remaining = remaining > 0 ? remaining - 1 : 8 * 3600;
      const hours = Math.floor(remaining / 3600);
      const minutes = Math.floor((remaining % 3600) / 60);
      const seconds = remaining % 60;
      countdownNodes.hours.textContent = store.fa.format(hours).padStart(2, '۰');
      countdownNodes.minutes.textContent = store.fa.format(minutes).padStart(2, '۰');
      countdownNodes.seconds.textContent = store.fa.format(seconds).padStart(2, '۰');
    }, 1000);
  }

  $('#newsletterForm')?.addEventListener('submit', event => {
    event.preventDefault();
    if (!event.currentTarget.reportValidity()) return;
    showToast('عضویت خبرنامه به‌زودی فعال می‌شود.');
  });

  const testimonialViewport = $('.testimonial-viewport');
  const testimonialTrack = $('.testimonial-track');
  if (testimonialViewport && testimonialTrack && testimonialTrack.children.length > 1) {
    const originals = [...testimonialTrack.children];
    originals.forEach(card => {
      const clone = card.cloneNode(true);
      clone.setAttribute('aria-hidden', 'true');
      clone.setAttribute('inert', '');
      testimonialTrack.appendChild(clone);
    });
    const reducedMotion = matchMedia('(prefers-reduced-motion: reduce)');
    let paused = false;
    let loopWidth = 0;
    const measure = () => { loopWidth = Math.abs(testimonialTrack.children[originals.length].offsetLeft - testimonialTrack.children[0].offsetLeft); };
    const pause = () => { paused = true; };
    const resume = () => { paused = false; };
    testimonialViewport.addEventListener('mouseenter', pause);
    testimonialViewport.addEventListener('mouseleave', resume);
    testimonialViewport.addEventListener('focusin', pause);
    testimonialViewport.addEventListener('focusout', resume);
    testimonialViewport.addEventListener('pointerdown', pause);
    testimonialViewport.addEventListener('pointerup', resume);
    testimonialViewport.addEventListener('pointercancel', resume);
    addEventListener('resize', measure, { passive: true });
    requestAnimationFrame(measure);
    setInterval(() => {
      if (!paused && !reducedMotion.matches && loopWidth) {
        testimonialViewport.scrollLeft += .5;
        if (testimonialViewport.scrollLeft >= loopWidth) testimonialViewport.scrollLeft -= loopWidth;
      }
    }, 20);
  }

})();
