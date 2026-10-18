# فروشگاه الکترونیک نُوا

یک فروشگاه چندصفحه‌ای محصولات دیجیتال فارسی، کاملاً راست‌چین و واکنش‌گرا، همراه با API خواندنی سبک مبتنی بر PHP خام و MySQL.

## اجرا

فرانت‌اند فعلی همچنان با بازکردن مستقیم `index.html` اجرا می‌شود. برای API، PHP 8.1 یا جدیدتر با افزونه‌های `pdo_mysql` و `mbstring` و یک دیتابیس MySQL/MariaDB نیاز است؛ Composer، Node.js و فرایند build لازم نیست.

## صفحات

- `index.html` — صفحه اصلی
- `shop.html` — فروشگاه، جستجو، فیلتر و مرتب‌سازی
- `product.html?id=1` — جزئیات محصول و مشخصات
- `cart.html` — سبد خرید نمایشی
- `checkout.html` — فرم تسویه‌حساب نمایشی با اعتبارسنجی مرورگر
- `about.html` — معرفی پروژه و راهنمای انتخاب
- `contact.html` — فرم تماس نمایشی با اعتبارسنجی مرورگر

## امکانات

- طراحی واکنش‌گرا برای دسکتاپ، تبلت و موبایل
- اسلایدر هیرو و شمارش معکوس پیشنهاد ویژه
- سبد خرید نمایشی مبتنی بر `localStorage` با تغییر تعداد و حذف کالا
- مسیر کامل خانه ← فروشگاه ← محصول ← سبد ← تسویه‌حساب دمو
- فیلتر، جستجو و مرتب‌سازی کاتالوگ
- گالری سه‌حالته محصول، انتخاب تعداد و نمایش مشخصات فنی
- وضعیت موجودی صریح نسخه دمو و محصولات مرتبط
- فرم‌های دسترس‌پذیر با اعتبارسنجی فرانت‌اند
- منوی موبایل و ناوبری واقعی میان صفحات
- استفادهٔ کامل از تصاویر محلی و مسیرهای نسبی

## API محصولات

- `GET /api/products.php` — فهرست محصولات
- `GET /api/products.php?q=surface&category=laptops&sort=price-asc&page=1&limit=12`
- `GET /api/product.php?id=1` — جزئیات و مشخصات محصول
- `GET /api/categories.php` — دسته‌بندی‌ها و تعداد محصولات

پارامترهای مرتب‌سازی مجاز: `default`، `price-asc`، `price-desc`، `name` و `newest`. فرانت‌اند در این فاز هنوز به API متصل نشده و سبد خرید و فرم‌ها همان رفتار محلی قبلی را دارند.

## راه‌اندازی محلی API

1. یک دیتابیس MySQL با `utf8mb4` بسازید.
2. ابتدا `database/schema.sql` و سپس `database/seed.sql` را در همان دیتابیس Import کنید.
3. فایل `.env.example` را با نام `backend/.env` کپی و اطلاعات اتصال محلی را وارد کنید. فایل `.env` در Git نادیده گرفته می‌شود.
4. از ریشه پروژه اجرا کنید: `php -S 127.0.0.1:8000 -t api`
5. برای بررسی API به `http://127.0.0.1:8000/products.php` بروید. با این روش کد خصوصی و تنظیمات محیطی در Document Root سرور محلی قرار نمی‌گیرند.

## استقرار روی cPanel

1. فایل‌های عمومی، از جمله صفحات، `assets` و `api` را در `public_html` قرار دهید.
2. محتوای پوشه `backend` را خارج از مسیر عمومی در `~/nova-private` قرار دهید؛ فایل `bootstrap.php` باید در `~/nova-private/bootstrap.php` باشد.
3. یک فایل `.env` واقعی داخل `~/nova-private` بسازید و اطلاعات دیتابیس cPanel را در آن قرار دهید. این فایل را در Git یا `public_html` قرار ندهید.
4. در phpMyAdmin ابتدا `database/schema.sql` و سپس `database/seed.sql` را Import کنید. پوشه `database` برای اجرای سایت لازم نیست و نباید در `public_html` باقی بماند.
5. PHP 8.1 یا جدیدتر و افزونه‌های `pdo_mysql` و `mbstring` را در cPanel فعال کنید.

## منابع تصاویر محصولات

تصاویر زیر از Wikimedia Commons دریافت، برای نمایش وب برش/تغییر اندازه داده و به WebP و AVIF تبدیل شده‌اند:

- Samsung Galaxy S24 Ultra — Dinkun Chen، CC BY-SA 4.0 ([منبع](https://commons.wikimedia.org/wiki/File:SAMSUNG_Galaxy_S24_Ultra_(6).jpg))
- Microsoft Surface — KK IN HK، CC BY-SA 4.0 ([منبع](https://commons.wikimedia.org/wiki/File:Laptop_PC.jpg))
- Bose QuietComfort 25 — Florian Fuchs، CC BY-SA 3.0 ([منبع](https://commons.wikimedia.org/wiki/File:Bose_QuietComfort_25_Acoustic_Noise_Cancelling_Headphones_with_Carry_Case.jpg))
- ساعت Wear OS — Unsplash، CC BY-SA 4.0 ([منبع](https://commons.wikimedia.org/wiki/File:Android_white_watch.jpg))
- Amazon Echo — Smart Home Perfected، CC BY 2.0 ([منبع](https://commons.wikimedia.org/wiki/File:Alexa_Amazon_Echo_Smart_Speaker_-_Flashing_Blue_Ring_(Creative_Commons)_(51048329823).jpg))
- Nikon Z30 — 昼落ち، CC BY-SA 4.0 ([منبع](https://commons.wikimedia.org/wiki/File:Nikon_Z_30_5_aug_2022a.jpg))
- تبلت روی میز — Matthew Guay، CC0 ([منبع](https://commons.wikimedia.org/wiki/File:Tablet_on_a_newspaper_(Unsplash).jpg))
- Hykker ActiveSound — Public domain ([منبع](https://commons.wikimedia.org/wiki/File:ActiveSound_wireless_earbuds_by_Hykker_(POJM200483).jpg))
- DJI Phantom 3 Advanced — Jacek Halicki، CC BY-SA 4.0 ([منبع](https://commons.wikimedia.org/wiki/File:2015_Dron_DJI_Phantom_3_Advanced.JPG))
- UE Boom — Sinchen.Lin، CC BY 2.0 ([منبع](https://commons.wikimedia.org/wiki/File:UE_Boom_speakers.jpg))
- کنترلر Xbox 360 — Evan-Amos، Public domain ([منبع](https://commons.wikimedia.org/wiki/File:Xbox-360-Controller-Black.jpg))
- Echo Dot — Smart Home Perfected، CC BY 2.0 ([منبع](https://commons.wikimedia.org/wiki/File:Alexa_Amazon_Echo_Dot_3rd_Gen_Smart_Speaker_-_Sandstone_Fabric_(Creative_Commons)_(51049273602).jpg))
- Surface Pro — KK IN HK، CC BY-SA 4.0 ([منبع](https://commons.wikimedia.org/wiki/File:Laptop_computer.jpeg))
