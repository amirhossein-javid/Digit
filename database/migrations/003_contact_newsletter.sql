SET NAMES utf8mb4;

CREATE TABLE IF NOT EXISTS contact_messages (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    sender_name VARCHAR(100) NOT NULL,
    sender_email VARCHAR(190) CHARACTER SET ascii COLLATE ascii_general_ci NOT NULL,
    subject ENUM('product_guidance', 'product_specs', 'cart_support') NOT NULL,
    message TEXT NOT NULL,
    status ENUM('new', 'read') NOT NULL DEFAULT 'new',
    client_hash CHAR(64) CHARACTER SET ascii COLLATE ascii_general_ci NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    read_at DATETIME NULL,
    PRIMARY KEY (id),
    KEY idx_contact_messages_status_created (status, created_at),
    KEY idx_contact_messages_email_created (sender_email, created_at),
    KEY idx_contact_messages_client_created (client_hash, created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS newsletter_subscribers (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    email VARCHAR(190) CHARACTER SET ascii COLLATE ascii_general_ci NOT NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    client_hash CHAR(64) CHARACTER SET ascii COLLATE ascii_general_ci NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_newsletter_subscribers_email (email),
    KEY idx_newsletter_subscribers_active_created (is_active, created_at),
    KEY idx_newsletter_subscribers_client_created (client_hash, created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
