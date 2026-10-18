SET NAMES utf8mb4;
SET time_zone = '+00:00';

CREATE TABLE IF NOT EXISTS categories (
    id SMALLINT UNSIGNED NOT NULL AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    slug VARCHAR(100) CHARACTER SET ascii COLLATE ascii_general_ci NOT NULL,
    position SMALLINT UNSIGNED NOT NULL DEFAULT 0,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_categories_name (name),
    UNIQUE KEY uq_categories_slug (slug),
    KEY idx_categories_position (position)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS products (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    category_id SMALLINT UNSIGNED NOT NULL,
    name VARCHAR(190) NOT NULL,
    slug VARCHAR(190) CHARACTER SET ascii COLLATE ascii_general_ci NOT NULL,
    description TEXT NOT NULL,
    price BIGINT UNSIGNED NOT NULL,
    image_webp VARCHAR(255) NOT NULL,
    image_avif VARCHAR(255) NOT NULL,
    is_featured TINYINT(1) NOT NULL DEFAULT 0,
    is_new TINYINT(1) NOT NULL DEFAULT 0,
    stock_status ENUM('inquiry', 'in_stock', 'out_of_stock') NOT NULL DEFAULT 'inquiry',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_products_slug (slug),
    KEY idx_products_category (category_id),
    KEY idx_products_price (price),
    KEY idx_products_featured (is_featured),
    KEY idx_products_new (is_new),
    CONSTRAINT fk_products_category
        FOREIGN KEY (category_id) REFERENCES categories (id)
        ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS product_specs (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    product_id INT UNSIGNED NOT NULL,
    spec_key VARCHAR(100) NOT NULL,
    spec_value VARCHAR(255) NOT NULL,
    position SMALLINT UNSIGNED NOT NULL DEFAULT 0,
    PRIMARY KEY (id),
    KEY idx_product_specs_product_position (product_id, position),
    CONSTRAINT fk_product_specs_product
        FOREIGN KEY (product_id) REFERENCES products (id)
        ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS orders (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    order_number VARCHAR(32) CHARACTER SET ascii COLLATE ascii_general_ci NOT NULL,
    idempotency_key CHAR(36) CHARACTER SET ascii COLLATE ascii_general_ci NOT NULL,
    customer_name VARCHAR(100) NOT NULL,
    customer_phone VARCHAR(20) CHARACTER SET ascii COLLATE ascii_general_ci NOT NULL,
    customer_email VARCHAR(190) NULL,
    city VARCHAR(100) NOT NULL,
    address VARCHAR(500) NOT NULL,
    postal_code CHAR(10) CHARACTER SET ascii COLLATE ascii_general_ci NULL,
    status ENUM('pending', 'confirmed', 'cancelled') NOT NULL DEFAULT 'pending',
    total_amount BIGINT UNSIGNED NOT NULL,
    total_quantity SMALLINT UNSIGNED NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_orders_order_number (order_number),
    UNIQUE KEY uq_orders_idempotency_key (idempotency_key),
    KEY idx_orders_phone_created (customer_phone, created_at),
    KEY idx_orders_status_created (status, created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS order_items (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    order_id BIGINT UNSIGNED NOT NULL,
    product_id INT UNSIGNED NOT NULL,
    product_name VARCHAR(190) NOT NULL,
    unit_price BIGINT UNSIGNED NOT NULL,
    quantity SMALLINT UNSIGNED NOT NULL,
    line_total BIGINT UNSIGNED NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_order_items_order (order_id),
    KEY idx_order_items_product (product_id),
    CONSTRAINT fk_order_items_order
        FOREIGN KEY (order_id) REFERENCES orders (id)
        ON UPDATE CASCADE ON DELETE CASCADE,
    CONSTRAINT fk_order_items_product
        FOREIGN KEY (product_id) REFERENCES products (id)
        ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
