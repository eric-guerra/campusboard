-- CampusBoard schema (MySQL 5.x / 8.x)
CREATE TABLE IF NOT EXISTS users (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(190) NOT NULL UNIQUE,
    display_name VARCHAR(80) NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS categories (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    slug VARCHAR(40) NOT NULL UNIQUE,
    name VARCHAR(80) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS listings (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    category_id INT UNSIGNED NOT NULL,
    title VARCHAR(120) NOT NULL,
    body TEXT NOT NULL,
    price_cents INT UNSIGNED NOT NULL DEFAULT 0,
    status ENUM('active','sold','removed') NOT NULL DEFAULT 'active',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_listing_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_listing_category FOREIGN KEY (category_id) REFERENCES categories(id),
    INDEX idx_listing_status_created (status, created_at),
    FULLTEXT INDEX ft_listing_text (title, body)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT IGNORE INTO categories (slug, name) VALUES
    ('textbooks', 'Textbooks'),
    ('furniture', 'Furniture'),
    ('electronics', 'Electronics'),
    ('housing', 'Housing & Sublets'),
    ('rides', 'Rides'),
    ('misc', 'Miscellaneous');
