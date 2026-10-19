# فروشگاه الکترونیک دیجیت

یک فروشگاه چندصفحه‌ای محصولات دیجیتال فارسی، کاملاً راست‌چین و واکنش‌گرا، همراه با API و پنل مدیریت مبتنی بر PHP خام و MySQL.

## اجرا

نمایش محصولات فرانت‌اند از API خوانده می‌شود و برای اجرای کامل باید سایت از طریق PHP اجرا شود. PHP 8.1 یا جدیدتر با افزونه‌های `pdo_mysql`، `mbstring` و `fileinfo` و یک دیتابیس MySQL/MariaDB نیاز است؛ Composer، Node.js و فرایند build لازم نیست.

## صفحات

- `index.html` — صفحه اصلی
- `shop.html` — فروشگاه، جستجو، فیلتر و مرتب‌سازی
- `product.html?id=1` — جزئیات محصول و مشخصات
- `cart.html` — سبد خرید محلی با مدیریت تعداد و حذف کالا
- `checkout.html` — ثبت سفارش در MySQL با اعتبارسنجی مرورگر و سرور
- `about.html` — معرفی پروژه و راهنمای انتخاب
- `contact.html` — فرم تماس نمایشی با اعتبارسنجی مرورگر

## امکانات

- طراحی واکنش‌گرا برای دسکتاپ، تبلت و موبایل
- اسلایدر هیرو و شمارش معکوس پیشنهاد ویژه
- سبد خرید مبتنی بر `localStorage` با تغییر تعداد و حذف کالا
- مسیر کامل خانه ← فروشگاه ← محصول ← سبد ← ثبت سفارش
- فیلتر، جستجو و مرتب‌سازی کاتالوگ
- گالری سه‌حالته محصول، انتخاب تعداد و نمایش مشخصات فنی
- نمایش وضعیت موجودی و محصولات مرتبط
- فرم‌های دسترس‌پذیر با اعتبارسنجی فرانت‌اند
- منوی موبایل و ناوبری واقعی میان صفحات
- استفادهٔ کامل از تصاویر محلی و مسیرهای نسبی

## API محصولات

- `GET /api/products.php` — فهرست محصولات
- `GET /api/products.php?q=surface&category=laptops&sort=price-asc&page=1&limit=12`
- `GET /api/product.php?id=1` — جزئیات و مشخصات محصول
- `GET /api/categories.php` — دسته‌بندی‌ها و تعداد محصولات
- `POST /api/orders.php` — ثبت سفارش با بدنه JSON و سربرگ یکتای `Idempotency-Key`

پارامترهای مرتب‌سازی مجاز: `default`، `price-asc`، `price-desc`، `name` و `newest`. فهرست‌ها، جستجو، فیلتر و جزئیات محصول از API دریافت می‌شوند. هنگام ثبت سفارش، قیمت‌ها فقط از دیتابیس خوانده می‌شوند و سفارش و اقلام آن در یک تراکنش ذخیره می‌شوند. کلید یکتای درخواست نیز از ثبت دوباره سفارش در اثر ارسال مجدد جلوگیری می‌کند.

## پنل مدیریت

پنل از مسیر `admin/` در دسترس است و شامل داشبورد، مدیریت محصولات و دسته‌بندی‌ها، غیرفعال‌سازی منطقی محصولات، مدیریت سفارش‌ها و تاریخچه تغییر وضعیت است. ورود با Session امن، CSRF و محدودسازی تلاش ناموفق محافظت می‌شود.

برای دیتابیس موجود، فایل `database/migrations/002_admin_panel.sql` را فقط یک‌بار اجرا کنید. سپس در فایل محیطی خصوصی دو مقدار تصادفی و مستقل با حداقل ۳۲ کاراکتر برای `APP_KEY` و `ADMIN_SETUP_TOKEN` قرار دهید و `PUBLIC_ROOT` را برابر مسیر مطلق ریشه عمومی سایت تنظیم کنید. صفحه `admin/setup.php` فقط تا پیش از ساخت اولین مدیر فعال است؛ پس از ساخت مدیر، ورود از `admin/login.php` انجام می‌شود. هیچ نام کاربری یا رمز پیش‌فرضی در پروژه وجود ندارد.

## راه‌اندازی محلی API

1. یک دیتابیس MySQL با `utf8mb4` بسازید.
2. برای نصب تازه ابتدا `database/schema.sql` و سپس `database/seed.sql` را Import کنید. روی دیتابیس موجود فقط migrationهای اجرا‌نشده را به‌ترتیب اعمال کنید.
3. فایل `.env.example` را خارج از ریشه عمومی، برای نمونه با نام `../nova-store.env` کپی و اطلاعات اتصال، کلید برنامه، توکن راه‌اندازی و مسیر `PUBLIC_ROOT` را وارد کنید.
4. در PowerShell مسیر تنظیمات را برای همان نشست تعیین کنید: `$env:NOVA_ENV_FILE=(Resolve-Path ../nova-store.env)`.
5. از ریشه پروژه اجرا کنید: `php -S 127.0.0.1:8000`.
6. سایت را در `http://127.0.0.1:8000/` و API را در `http://127.0.0.1:8000/api/products.php` بررسی کنید.

مسیر API به‌صورت پیش‌فرض نسبی و برابر `api` است. در استقرار متفاوت می‌توان پیش از `catalog.js` مقدار `window.NOVA_CONFIG = { apiBase: '/path/to/api' }` را تعیین کرد؛ هیچ آدرس localhost یا دامنه‌ای در کد کاتالوگ ثابت نشده است.

## استقرار روی cPanel

1. فایل‌های عمومی، از جمله صفحات، `assets`، `api` و `admin` را در `public_html` قرار دهید.
2. محتوای پوشه `backend` را خارج از مسیر عمومی در `~/nova-private` قرار دهید؛ فایل `bootstrap.php` باید در `~/nova-private/bootstrap.php` باشد.
3. یک فایل `.env` واقعی داخل `~/nova-private` بسازید و اطلاعات دیتابیس cPanel را در آن قرار دهید. این فایل را در Git یا `public_html` قرار ندهید.
4. در نصب تازه، در phpMyAdmin ابتدا `database/schema.sql` و سپس `database/seed.sql` را Import کنید؛ روی دیتابیس موجود migration اجرا‌نشده را اعمال کنید. پوشه `database` نباید در `public_html` باقی بماند.
5. PHP 8.1 یا جدیدتر و افزونه‌های `pdo_mysql`، `mbstring` و `fileinfo` را در cPanel فعال کنید.
6. برای دیتابیس موجود migration پنل را اجرا و سپس مدیر اولیه را از `admin/setup.php` ایجاد کنید.

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
