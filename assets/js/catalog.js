(() => {
  'use strict';

  const products = [
    {
      id: 1,
      name: 'گوشی Samsung Galaxy S24 Ultra',
      category: 'گوشی موبایل',
      price: 68900000,
      image: 'assets/images/products/phone-s24-ultra.webp',
      avif: 'assets/images/products/phone-s24-ultra.avif',
      description: 'پرچم‌دار سامسونگ با نمایشگر بزرگ، قلم S Pen و مجموعه دوربین چندگانه؛ مناسب کاربرانی که عکاسی، یادداشت‌برداری و عملکرد سطح بالا را در یک دستگاه می‌خواهند.',
      specs: [['نمایشگر', '۶٫۸ اینچ Dynamic AMOLED 2X'], ['دوربین اصلی', '۲۰۰ مگاپیکسل'], ['قلم', 'S Pen داخلی'], ['مقاومت', 'IP68']],
      featured: true
    },
    {
      id: 2,
      name: 'Microsoft Surface Pro 9',
      category: 'لپ‌تاپ',
      price: 84900000,
      image: 'assets/images/products/laptop-surface.webp',
      avif: 'assets/images/products/laptop-surface.avif',
      description: 'رایانهٔ هیبریدی Surface Pro 9 با نمایشگر لمسی ۱۳ اینچی، پایهٔ قابل تنظیم و طراحی سبک؛ مناسب کار حرفه‌ای، یادداشت‌برداری و استفاده روزمره.',
      specs: [['نمایشگر', '۱۳ اینچ PixelSense Flow با نرخ نوسازی تا ۱۲۰ هرتز'], ['پردازنده', 'Intel Core نسل دوازدهم'], ['درگاه‌ها', 'دو USB-C با Thunderbolt 4'], ['سیستم‌عامل', 'Windows 11']],
      featured: true
    },
    {
      id: 3,
      name: 'هدفون Bose QuietComfort 25',
      category: 'صوتی',
      price: 7490000,
      image: 'assets/images/products/headphones-qc25.webp',
      avif: 'assets/images/products/headphones-qc25.avif',
      description: 'هدفون دورگوشی سیمی Bose با قابلیت حذف نویز فعال؛ مناسب سفر و شنیدن متمرکز موسیقی. سازگاری کابل باید پیش از خرید با دستگاه مقصد بررسی شود.',
      specs: [['نوع اتصال', 'سیمی'], ['فرم پوشش', 'دورگوشی'], ['حذف نویز', 'فعال'], ['مدل', 'QuietComfort 25']],
      featured: true
    },
    {
      id: 4,
      name: 'ساعت هوشمند Samsung Galaxy Watch5 44mm',
      category: 'پوشیدنی هوشمند',
      price: 10900000,
      image: 'assets/images/products/smartwatch-wearos.webp',
      avif: 'assets/images/products/smartwatch-wearos.avif',
      description: 'ساعت هوشمند Galaxy Watch5 با قاب ۴۴ میلی‌متری، نمایشگر Super AMOLED و سیستم‌عامل Wear OS؛ مناسب پایش فعالیت‌های روزانه و اعلان‌های گوشی.',
      specs: [['نمایشگر', '۱٫۴ اینچ Super AMOLED'], ['سیستم‌عامل', 'Wear OS Powered by Samsung'], ['مقاومت', '5ATM و IP68'], ['مدل', 'Galaxy Watch5 44mm']],
      featured: true
    },
    {
      id: 5,
      name: 'اسپیکر هوشمند Amazon Echo نسل سوم',
      category: 'خانه هوشمند',
      price: 9390000,
      image: 'assets/images/products/speaker-echo.webp',
      avif: 'assets/images/products/speaker-echo.avif',
      description: 'اسپیکر هوشمند خانواده Amazon Echo با دستیار صوتی Alexa. پشتیبانی سرویس‌ها و زبان‌ها به منطقه و حساب کاربری وابسته است.',
      specs: [['دستیار صوتی', 'Amazon Alexa'], ['اتصال', 'Wi‑Fi و Bluetooth'], ['نسل', 'سوم'], ['نیازمندی', 'حساب و سرویس سازگار']],
      featured: true
    },
    {
      id: 6,
      name: 'دوربین بدون‌آینه Nikon Z30',
      category: 'دوربین',
      price: 47500000,
      image: 'assets/images/products/camera-nikon-z30.webp',
      avif: 'assets/images/products/camera-nikon-z30.avif',
      description: 'دوربین بدون‌آینه کامپکت نیکون با حسگر DX و مانت Z که برای تولید محتوا، ویدئو و عکاسی روزمره طراحی شده است.',
      specs: [['حسگر', 'DX CMOS با رزولوشن ۲۰٫۹ مگاپیکسل'], ['ویدئو', '4K UHD تا ۳۰ فریم'], ['مانت لنز', 'Nikon Z'], ['نمایشگر', 'چرخان']],
      featured: true
    },
    {
      id: 7,
      name: 'تبلت Apple iPad 10.2 نسل نهم',
      category: 'تبلت',
      price: 28900000,
      image: 'assets/images/products/tablet-10.webp',
      avif: 'assets/images/products/tablet-10.avif',
      description: 'آیپد نسل نهم با نمایشگر Retina ده‌ودو دهم اینچی، تراشه A13 Bionic و پشتیبانی از Apple Pencil نسل اول؛ مناسب مطالعه، یادداشت‌برداری و کارهای روزمره.',
      specs: [['نمایشگر', '۱۰٫۲ اینچ Retina با True Tone'], ['تراشه', 'A13 Bionic'], ['دوربین اصلی', '۸ مگاپیکسل'], ['درگاه', 'Lightning']],
      isNew: true
    },
    {
      id: 8,
      name: 'ایرباد Hykker ActiveSound',
      category: 'صوتی',
      price: 4900000,
      image: 'assets/images/products/earbuds-activesound.webp',
      avif: 'assets/images/products/earbuds-activesound.avif',
      description: 'ایرباد بی‌سیم ActiveSound از برند Hykker همراه با کیس شارژ و سری‌های سیلیکونی؛ طراحی جمع‌وجور آن برای استفاده روزمره مناسب است.',
      specs: [['نوع', 'ایرباد بی‌سیم'], ['لوازم همراه', 'کیس شارژ'], ['برند', 'Hykker'], ['مدل', 'ActiveSound']],
      isNew: true
    },
    {
      id: 9,
      name: 'پهپاد DJI Phantom 3 Advanced',
      category: 'تصویربرداری هوایی',
      price: 39900000,
      image: 'assets/images/products/drone-phantom3.webp',
      avif: 'assets/images/products/drone-phantom3.avif',
      description: 'کوادکوپتر نسل Phantom 3 Advanced با دوربین 2.7K و گیمبال سه‌محوره. استفاده از پهپاد تابع قوانین محلی و محدودیت‌های پروازی است.',
      specs: [['ویدئو', '2.7K'], ['لرزش‌گیر', 'گیمبال سه‌محوره'], ['ناوبری', 'GPS/GLONASS'], ['مدل', 'Phantom 3 Advanced']],
      isNew: true
    },
    {
      id: 10,
      name: 'اسپیکر بلوتوث Ultimate Ears BOOM 2',
      category: 'صوتی',
      price: 5990000,
      image: 'assets/images/products/speaker-ue-boom.webp',
      avif: 'assets/images/products/speaker-ue-boom.avif',
      description: 'اسپیکر قابل‌حمل Ultimate Ears BOOM 2 با طراحی استوانه‌ای، صدای ۳۶۰ درجه و بدنه مقاوم در برابر آب؛ مناسب استفاده در خانه و فضای باز.',
      specs: [['پخش صدا', '۳۶۰ درجه'], ['اتصال', 'Bluetooth'], ['مقاومت', 'IPX7'], ['شارژدهی', 'تا ۱۵ ساعت']],
      isNew: true
    },
    {
      id: 11,
      name: 'کنترلر بازی Xbox 360',
      category: 'گیمینگ',
      price: 4290000,
      image: 'assets/images/products/controller-xbox360.webp',
      avif: 'assets/images/products/controller-xbox360.avif',
      description: 'کنترلر کلاسیک Xbox 360 با چینش آشنای کلیدها. نوع اتصال و سازگاری نسخهٔ موجود باید پیش از خرید بررسی شود.',
      specs: [['پلتفرم اصلی', 'Xbox 360'], ['نوع محصول', 'کنترلر بازی'], ['رنگ تصویر', 'مشکی'], ['سازگاری', 'وابسته به نسخه و گیرنده']],
      isNew: true
    },
    {
      id: 12,
      name: 'Microsoft Surface Pro 7',
      category: 'لپ‌تاپ',
      price: 79900000,
      image: 'assets/images/products/laptop-surface-pro.webp',
      avif: 'assets/images/products/laptop-surface-pro.avif',
      description: 'رایانهٔ هیبریدی Surface Pro 7 با نمایشگر لمسی ۱۲٫۳ اینچی و پایهٔ داخلی؛ ترکیبی انعطاف‌پذیر برای کار، مطالعه و استفاده در حالت تبلت.',
      specs: [['نمایشگر', '۱۲٫۳ اینچ PixelSense'], ['پردازنده', 'Intel Core نسل دهم'], ['درگاه‌ها', 'USB-C و USB-A'], ['سیستم‌عامل', 'Windows 10']],
      isNew: true
    }
  ];

  const fa = new Intl.NumberFormat('fa-IR');
  const CART_KEY = 'nova_demo_cart_v2';
  const formatPrice = value => `${fa.format(value)} تومان`;
  const getProduct = id => products.find(product => product.id === Number(id));

  function getCart() {
    try {
      const parsed = JSON.parse(localStorage.getItem(CART_KEY) || '{}');
      return Object.fromEntries(Object.entries(parsed).filter(([id, qty]) => getProduct(id) && Number.isInteger(qty) && qty > 0 && qty <= 20));
    } catch (_) {
      return {};
    }
  }

  function saveCart(cart) {
    localStorage.setItem(CART_KEY, JSON.stringify(cart));
    updateCartBadges();
    document.dispatchEvent(new CustomEvent('nova:cart-change'));
  }

  function addToCart(id, quantity = 1) {
    if (!getProduct(id)) return;
    const cart = getCart();
    cart[id] = Math.min(20, (cart[id] || 0) + Math.max(1, Number(quantity) || 1));
    saveCart(cart);
  }

  function updateCartItem(id, quantity) {
    const cart = getCart();
    const safeQuantity = Math.max(0, Math.min(20, Number(quantity) || 0));
    if (safeQuantity) cart[id] = safeQuantity;
    else delete cart[id];
    saveCart(cart);
  }

  function cartLines() {
    const cart = getCart();
    return Object.entries(cart).map(([id, quantity]) => ({ product: getProduct(id), quantity }));
  }

  function cartTotal() {
    return cartLines().reduce((sum, line) => sum + line.product.price * line.quantity, 0);
  }

  function cartCount() {
    return cartLines().reduce((sum, line) => sum + line.quantity, 0);
  }

  function updateCartBadges() {
    document.querySelectorAll('[data-cart-count]').forEach(node => { node.textContent = fa.format(cartCount()); });
  }

  function picture(product, eager = false) {
    return `<picture><source srcset="${product.avif}" type="image/avif"><img src="${product.image}" alt="${product.name}" width="720" height="720" ${eager ? 'fetchpriority="high"' : 'loading="lazy"'} decoding="async"></picture>`;
  }

  function productCard(product) {
    return `<article class="product-card" data-id="${product.id}">
      <a class="product-image" href="product.html?id=${product.id}" aria-label="مشاهده ${product.name}">${picture(product)}</a>
      <small class="product-category">${product.category}</small>
      <h3><a href="product.html?id=${product.id}">${product.name}</a></h3>
      <div class="price-row"><strong>${formatPrice(product.price)}</strong></div>
      <div class="product-meta"><a class="details-link" href="product.html?id=${product.id}">جزئیات</a><button class="add-btn js-add-cart" type="button" data-product-id="${product.id}" aria-label="افزودن ${product.name} به سبد خرید">+</button></div>
    </article>`;
  }

  window.NovaStore = { products, fa, formatPrice, getProduct, getCart, saveCart, addToCart, updateCartItem, cartLines, cartTotal, cartCount, updateCartBadges, picture, productCard };
})();
