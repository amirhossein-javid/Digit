SET NAMES utf8mb4;
START TRANSACTION;

INSERT INTO categories (id, name, slug, position) VALUES
    (1, 'گوشی موبایل', 'mobile-phones', 1),
    (2, 'لپ‌تاپ', 'laptops', 2),
    (3, 'صوتی', 'audio', 3),
    (4, 'پوشیدنی هوشمند', 'smart-wearables', 4),
    (5, 'خانه هوشمند', 'smart-home', 5),
    (6, 'دوربین', 'cameras', 6),
    (7, 'تبلت', 'tablets', 7),
    (8, 'تصویربرداری هوایی', 'aerial-imaging', 8),
    (9, 'گیمینگ', 'gaming', 9)
ON DUPLICATE KEY UPDATE
    name = VALUES(name),
    slug = VALUES(slug),
    position = VALUES(position);

INSERT INTO products
    (id, category_id, name, slug, description, price, image_webp, image_avif, is_featured, is_new, stock_status)
VALUES
    (1, 1, 'گوشی Samsung Galaxy S24 Ultra', 'samsung-galaxy-s24-ultra', 'پرچم‌دار سامسونگ با نمایشگر بزرگ، قلم S Pen و مجموعه دوربین چندگانه؛ مناسب کاربرانی که عکاسی، یادداشت‌برداری و عملکرد سطح بالا را در یک دستگاه می‌خواهند.', 68900000, 'assets/images/products/phone-s24-ultra.webp', 'assets/images/products/phone-s24-ultra.avif', 1, 0, 'inquiry'),
    (2, 2, 'Microsoft Surface Pro 9', 'microsoft-surface-pro-9', 'رایانهٔ هیبریدی Surface Pro 9 با نمایشگر لمسی ۱۳ اینچی، پایهٔ قابل تنظیم و طراحی سبک؛ مناسب کار حرفه‌ای، یادداشت‌برداری و استفاده روزمره.', 84900000, 'assets/images/products/laptop-surface.webp', 'assets/images/products/laptop-surface.avif', 1, 0, 'inquiry'),
    (3, 3, 'هدفون Bose QuietComfort 25', 'bose-quietcomfort-25', 'هدفون دورگوشی سیمی Bose با قابلیت حذف نویز فعال؛ مناسب سفر و شنیدن متمرکز موسیقی. سازگاری کابل باید پیش از خرید با دستگاه مقصد بررسی شود.', 7490000, 'assets/images/products/headphones-qc25.webp', 'assets/images/products/headphones-qc25.avif', 1, 0, 'inquiry'),
    (4, 4, 'ساعت هوشمند Samsung Galaxy Watch5 44mm', 'samsung-galaxy-watch5-44mm', 'ساعت هوشمند Galaxy Watch5 با قاب ۴۴ میلی‌متری، نمایشگر Super AMOLED و سیستم‌عامل Wear OS؛ مناسب پایش فعالیت‌های روزانه و اعلان‌های گوشی.', 10900000, 'assets/images/products/smartwatch-wearos.webp', 'assets/images/products/smartwatch-wearos.avif', 1, 0, 'inquiry'),
    (5, 5, 'اسپیکر هوشمند Amazon Echo نسل سوم', 'amazon-echo-3rd-generation', 'اسپیکر هوشمند خانواده Amazon Echo با دستیار صوتی Alexa. پشتیبانی سرویس‌ها و زبان‌ها به منطقه و حساب کاربری وابسته است.', 9390000, 'assets/images/products/speaker-echo.webp', 'assets/images/products/speaker-echo.avif', 1, 0, 'inquiry'),
    (6, 6, 'دوربین بدون‌آینه Nikon Z30', 'nikon-z30', 'دوربین بدون‌آینه کامپکت نیکون با حسگر DX و مانت Z که برای تولید محتوا، ویدئو و عکاسی روزمره طراحی شده است.', 47500000, 'assets/images/products/camera-nikon-z30.webp', 'assets/images/products/camera-nikon-z30.avif', 1, 0, 'inquiry'),
    (7, 7, 'تبلت Apple iPad 10.2 نسل نهم', 'apple-ipad-10-2-9th-generation', 'آیپد نسل نهم با نمایشگر Retina ده‌ودو دهم اینچی، تراشه A13 Bionic و پشتیبانی از Apple Pencil نسل اول؛ مناسب مطالعه، یادداشت‌برداری و کارهای روزمره.', 28900000, 'assets/images/products/tablet-10.webp', 'assets/images/products/tablet-10.avif', 0, 1, 'inquiry'),
    (8, 3, 'ایرباد Hykker ActiveSound', 'hykker-activesound-earbuds', 'ایرباد بی‌سیم ActiveSound از برند Hykker همراه با کیس شارژ و سری‌های سیلیکونی؛ طراحی جمع‌وجور آن برای استفاده روزمره مناسب است.', 4900000, 'assets/images/products/earbuds-activesound.webp', 'assets/images/products/earbuds-activesound.avif', 0, 1, 'inquiry'),
    (9, 8, 'پهپاد DJI Phantom 3 Advanced', 'dji-phantom-3-advanced', 'کوادکوپتر نسل Phantom 3 Advanced با دوربین 2.7K و گیمبال سه‌محوره. استفاده از پهپاد تابع قوانین محلی و محدودیت‌های پروازی است.', 39900000, 'assets/images/products/drone-phantom3.webp', 'assets/images/products/drone-phantom3.avif', 0, 1, 'inquiry'),
    (10, 3, 'اسپیکر بلوتوث Ultimate Ears BOOM 2', 'ultimate-ears-boom-2', 'اسپیکر قابل‌حمل Ultimate Ears BOOM 2 با طراحی استوانه‌ای، صدای ۳۶۰ درجه و بدنه مقاوم در برابر آب؛ مناسب استفاده در خانه و فضای باز.', 5990000, 'assets/images/products/speaker-ue-boom.webp', 'assets/images/products/speaker-ue-boom.avif', 0, 1, 'inquiry'),
    (11, 9, 'کنترلر بازی Xbox 360', 'xbox-360-controller', 'کنترلر کلاسیک Xbox 360 با چینش آشنای کلیدها. نوع اتصال و سازگاری نسخهٔ موجود باید پیش از خرید بررسی شود.', 4290000, 'assets/images/products/controller-xbox360.webp', 'assets/images/products/controller-xbox360.avif', 0, 1, 'inquiry'),
    (12, 2, 'Microsoft Surface Pro 7', 'microsoft-surface-pro-7', 'رایانهٔ هیبریدی Surface Pro 7 با نمایشگر لمسی ۱۲٫۳ اینچی و پایهٔ داخلی؛ ترکیبی انعطاف‌پذیر برای کار، مطالعه و استفاده در حالت تبلت.', 79900000, 'assets/images/products/laptop-surface-pro.webp', 'assets/images/products/laptop-surface-pro.avif', 0, 1, 'inquiry')
ON DUPLICATE KEY UPDATE
    category_id = VALUES(category_id),
    name = VALUES(name),
    slug = VALUES(slug),
    description = VALUES(description),
    price = VALUES(price),
    image_webp = VALUES(image_webp),
    image_avif = VALUES(image_avif),
    is_featured = VALUES(is_featured),
    is_new = VALUES(is_new),
    stock_status = VALUES(stock_status);

DELETE FROM product_specs WHERE product_id BETWEEN 1 AND 12;

INSERT INTO product_specs (product_id, spec_key, spec_value, position) VALUES
    (1, 'نمایشگر', '۶٫۸ اینچ Dynamic AMOLED 2X', 1), (1, 'دوربین اصلی', '۲۰۰ مگاپیکسل', 2), (1, 'قلم', 'S Pen داخلی', 3), (1, 'مقاومت', 'IP68', 4),
    (2, 'نمایشگر', '۱۳ اینچ PixelSense Flow با نرخ نوسازی تا ۱۲۰ هرتز', 1), (2, 'پردازنده', 'Intel Core نسل دوازدهم', 2), (2, 'درگاه‌ها', 'دو USB-C با Thunderbolt 4', 3), (2, 'سیستم‌عامل', 'Windows 11', 4),
    (3, 'نوع اتصال', 'سیمی', 1), (3, 'فرم پوشش', 'دورگوشی', 2), (3, 'حذف نویز', 'فعال', 3), (3, 'مدل', 'QuietComfort 25', 4),
    (4, 'نمایشگر', '۱٫۴ اینچ Super AMOLED', 1), (4, 'سیستم‌عامل', 'Wear OS Powered by Samsung', 2), (4, 'مقاومت', '5ATM و IP68', 3), (4, 'مدل', 'Galaxy Watch5 44mm', 4),
    (5, 'دستیار صوتی', 'Amazon Alexa', 1), (5, 'اتصال', 'Wi‑Fi و Bluetooth', 2), (5, 'نسل', 'سوم', 3), (5, 'نیازمندی', 'حساب و سرویس سازگار', 4),
    (6, 'حسگر', 'DX CMOS با رزولوشن ۲۰٫۹ مگاپیکسل', 1), (6, 'ویدئو', '4K UHD تا ۳۰ فریم', 2), (6, 'مانت لنز', 'Nikon Z', 3), (6, 'نمایشگر', 'چرخان', 4),
    (7, 'نمایشگر', '۱۰٫۲ اینچ Retina با True Tone', 1), (7, 'تراشه', 'A13 Bionic', 2), (7, 'دوربین اصلی', '۸ مگاپیکسل', 3), (7, 'درگاه', 'Lightning', 4),
    (8, 'نوع', 'ایرباد بی‌سیم', 1), (8, 'لوازم همراه', 'کیس شارژ', 2), (8, 'برند', 'Hykker', 3), (8, 'مدل', 'ActiveSound', 4),
    (9, 'ویدئو', '2.7K', 1), (9, 'لرزش‌گیر', 'گیمبال سه‌محوره', 2), (9, 'ناوبری', 'GPS/GLONASS', 3), (9, 'مدل', 'Phantom 3 Advanced', 4),
    (10, 'پخش صدا', '۳۶۰ درجه', 1), (10, 'اتصال', 'Bluetooth', 2), (10, 'مقاومت', 'IPX7', 3), (10, 'شارژدهی', 'تا ۱۵ ساعت', 4),
    (11, 'پلتفرم اصلی', 'Xbox 360', 1), (11, 'نوع محصول', 'کنترلر بازی', 2), (11, 'رنگ تصویر', 'مشکی', 3), (11, 'سازگاری', 'وابسته به نسخه و گیرنده', 4),
    (12, 'نمایشگر', '۱۲٫۳ اینچ PixelSense', 1), (12, 'پردازنده', 'Intel Core نسل دهم', 2), (12, 'درگاه‌ها', 'USB-C و USB-A', 3), (12, 'سیستم‌عامل', 'Windows 10', 4);

COMMIT;
