-- ============================================================
--  Online Flower Shop – Database Schema
--  Database: online_shop
--  Host: 127.0.0.1:3306
-- ============================================================

CREATE DATABASE IF NOT EXISTS online_shop
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE online_shop;

-- ─── TABLES ─────────────────────────────────────────────────

CREATE TABLE IF NOT EXISTS categories (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    name        VARCHAR(100) NOT NULL,
    slug        VARCHAR(100) NOT NULL UNIQUE,
    image       VARCHAR(255),
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS products (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    category_id     INT NOT NULL,
    name            VARCHAR(200) NOT NULL,
    slug            VARCHAR(200) NOT NULL UNIQUE,
    description     TEXT,
    original_price  DECIMAL(10,2) NOT NULL,
    discount_pct    TINYINT UNSIGNED DEFAULT 0,   -- e.g. 30 = 30%
    stock           INT DEFAULT 0,
    image           VARCHAR(255),
    is_featured     TINYINT(1) DEFAULT 0,
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS users (
    id            INT AUTO_INCREMENT PRIMARY KEY,
    name          VARCHAR(150) NOT NULL,
    email         VARCHAR(200) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    phone         VARCHAR(30),
    address       TEXT,
    role          ENUM('customer','admin') DEFAULT 'customer',
    created_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS cart (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    user_id    INT NOT NULL,
    product_id INT NOT NULL,
    qty        INT NOT NULL DEFAULT 1,
    added_at   TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_user_product (user_id, product_id),
    FOREIGN KEY (user_id)    REFERENCES users(id)    ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS orders (
    id           INT AUTO_INCREMENT PRIMARY KEY,
    user_id      INT NOT NULL,
    total_amount DECIMAL(10,2) NOT NULL,
    status       ENUM('pending','processing','shipped','delivered','cancelled') DEFAULT 'pending',
    address      TEXT,
    notes        TEXT,
    created_at   TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
);

CREATE TABLE IF NOT EXISTS order_items (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    order_id   INT NOT NULL,
    product_id INT NOT NULL,
    qty        INT NOT NULL,
    unit_price DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (order_id)   REFERENCES orders(id)   ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id)
);

CREATE TABLE IF NOT EXISTS wishlist (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    user_id    INT NOT NULL,
    product_id INT NOT NULL,
    added_at   TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_wish (user_id, product_id),
    FOREIGN KEY (user_id)    REFERENCES users(id)    ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
);

-- ─── VIEWS ──────────────────────────────────────────────────

-- Products with computed sale price and category name (aliases + join)
CREATE OR REPLACE VIEW vw_products AS
    SELECT
        p.id,
        p.name                                                  AS product_name,
        c.name                                                  AS category_name,
        p.slug,
        p.description,
        p.original_price,
        p.discount_pct,
        ROUND(p.original_price * (1 - p.discount_pct / 100), 2) AS sale_price,
        p.stock,
        p.image,
        p.is_featured,
        p.created_at
    FROM products p
    JOIN categories c ON c.id = p.category_id;

-- Category product count + avg price (aggregation + GROUP BY)
CREATE OR REPLACE VIEW vw_category_stats AS
    SELECT
        c.id                        AS category_id,
        c.name                      AS category_name,
        COUNT(p.id)                 AS total_products,
        AVG(p.original_price)       AS avg_price,
        MIN(p.original_price)       AS min_price,
        MAX(p.original_price)       AS max_price,
        SUM(p.stock)                AS total_stock
    FROM categories c
    LEFT JOIN products p ON p.category_id = c.id
    GROUP BY c.id, c.name;

-- ─── STORED PROCEDURES ──────────────────────────────────────

DELIMITER $$

-- Get paginated products, optionally filtered by category
CREATE PROCEDURE IF NOT EXISTS sp_get_products(
    IN  p_category_slug VARCHAR(100),
    IN  p_limit         INT,
    IN  p_offset        INT
)
BEGIN
    IF p_category_slug IS NULL OR p_category_slug = '' THEN
        SELECT * FROM vw_products
        ORDER BY is_featured DESC, created_at DESC
        LIMIT p_limit OFFSET p_offset;
    ELSE
        SELECT v.* FROM vw_products v
        JOIN categories c ON c.name = v.category_name
        WHERE c.slug = p_category_slug
        ORDER BY v.is_featured DESC, v.created_at DESC
        LIMIT p_limit OFFSET p_offset;
    END IF;
END$$

-- Add item to cart (upsert)
CREATE PROCEDURE IF NOT EXISTS sp_add_to_cart(
    IN p_user_id    INT,
    IN p_product_id INT,
    IN p_qty        INT
)
BEGIN
    INSERT INTO cart (user_id, product_id, qty)
    VALUES (p_user_id, p_product_id, p_qty)
    ON DUPLICATE KEY UPDATE qty = qty + p_qty;
END$$

-- Place order from cart
CREATE PROCEDURE IF NOT EXISTS sp_place_order(
    IN  p_user_id INT,
    IN  p_address TEXT,
    IN  p_notes   TEXT,
    OUT p_order_id INT
)
BEGIN
    DECLARE v_total DECIMAL(10,2);

    -- Calculate total using vw_products (sale price)
    SELECT SUM(c.qty * v.sale_price)
    INTO   v_total
    FROM   cart c
    JOIN   vw_products v ON v.id = c.product_id
    WHERE  c.user_id = p_user_id;

    -- Insert order
    INSERT INTO orders (user_id, total_amount, address, notes)
    VALUES (p_user_id, IFNULL(v_total, 0), p_address, p_notes);

    SET p_order_id = LAST_INSERT_ID();

    -- Copy cart → order_items
    INSERT INTO order_items (order_id, product_id, qty, unit_price)
    SELECT p_order_id, c.product_id, c.qty, v.sale_price
    FROM   cart c
    JOIN   vw_products v ON v.id = c.product_id
    WHERE  c.user_id = p_user_id;

    -- Clear cart
    DELETE FROM cart WHERE user_id = p_user_id;
END$$

-- Related products via self-join on same category
CREATE PROCEDURE IF NOT EXISTS sp_related_products(
    IN p_product_id INT,
    IN p_limit      INT
)
BEGIN
    SELECT p2.*
    FROM   vw_products p1
    JOIN   vw_products p2 ON p2.category_name = p1.category_name
                          AND p2.id <> p1.id
    WHERE  p1.id = p_product_id
    ORDER  BY p2.is_featured DESC
    LIMIT  p_limit;
END$$

DELIMITER ;

-- ─── SEED DATA ───────────────────────────────────────────────

INSERT IGNORE INTO categories (name, slug) VALUES
('Сарнай',         'roses'),
('Холимог баглаа', 'mixed-bouquets'),
('Тропик',         'tropical'),
('Наран цэцэг',    'sunflowers'),
('Орхид',          'orchids');

INSERT IGNORE INTO products (category_id, name, slug, description, original_price, discount_pct, stock, image, is_featured) VALUES
(1, 'Улаан Сарнайн Баглаа',      'red-rose-bouquet',      'Ямар ч тохиолдолд тохирох сонгодог улаан сарнай.',         56, 30, 20, 'https://images.unsplash.com/photo-1548094990-c16ca90f1f0d?w=600&q=80', 1),
(2, 'Хаврын Холимог Баглаа',     'spring-mix-bouquet',    'Улирлын гоё цэцгүүдийн тод хольц.',                        50, 30, 15, 'https://images.unsplash.com/photo-1487530811176-3780de880c2d?w=600&q=80', 1),
(3, 'Диваажингийн Шувуу',        'bird-of-paradise',      'Ховор тропик цэцэг, гоёмсог байрлуулалт.',                 29, 20, 10, 'https://images.unsplash.com/photo-1596438459194-f275f413d6ff?w=600&q=80', 1),
(1, 'Ягаан & Цагаан Сарнай',     'pink-white-roses',      'Нарийн ягаан болон цагаан сарнайн хольц.',                 25, 20, 18, 'https://images.unsplash.com/photo-1559563458-527698bf5295?w=600&q=80', 0),
(2, 'Шар Наран Цэцэг Баглаа',    'yellow-daisy-bouquet',  'Баяр хөөрийг илэрхийлэх шар цэцгийн баглаа.',             40, 15, 12, 'https://images.unsplash.com/photo-1490750967868-88df5691cc02?w=600&q=80', 1),
(4, 'Наран Цэцгийн Сагс',        'sunflower-basket',      'Гэгээн наран цэцгийн байрлуулалт.',                        35, 10,  8, 'https://images.unsplash.com/photo-1597848212624-a19eb35e2651?w=600&q=80', 0),
(5, 'Ягаан Орхидын Вааз',        'purple-orchid-pot',     'Керамик вааз дахь нарийн ягаан орхид.',                    60, 25,  5, 'https://images.unsplash.com/photo-1610397648930-477b8c7f0943?w=600&q=80', 1),
(3, 'Тропик Жинжүүрийн Баглаа',  'tropical-ginger-bunch', 'Ховор улаан жинжүүр ба пальмын навч.',                     45, 30,  7, 'https://images.unsplash.com/photo-1548094990-c16ca90f1f0d?w=600&q=80', 0),
(1, 'Цагаан Сарнайн Баглаа',     'white-rose-bundle',     'Энх тайвны бэлгэдэл цагаан сарнай.',                       38, 20, 14, 'https://images.unsplash.com/photo-1522748906645-95d8adfd52c7?w=600&q=80', 0),
(2, 'Солонгон Зэрлэг Цэцэг',     'rainbow-wildflowers',   'Олон өнгийн зэрлэг цэцгийн хольц.',                        32, 10, 20, 'https://images.unsplash.com/photo-1467810563316-b5476525c0f9?w=600&q=80', 0),
(4, 'Том Наран Цэцгийн Вааз',    'giant-sunflower-vase',  'Шилэн вааз дахь том наран цэцэг.',                         55, 30,  6, 'https://images.unsplash.com/photo-1543158181-e6f9f6712055?w=600&q=80', 1),
(5, 'Цагаан Орхидын Эгнээ',      'white-orchid-cascade',  'Урсах хэлбэртэй цагаан орхид.',                            70, 20,  4, 'https://images.unsplash.com/photo-1610397648930-477b8c7f0943?w=600&q=80', 0);

-- Default admin user (password: Admin@1234)
INSERT IGNORE INTO users (name, email, password_hash, role) VALUES
('Admin', 'admin@flowershop.mn', '$2y$12$Mn7VCNEVih/CqbjM20uaq.vfC6FanUvefod8lcZHI3vkzXPXl7J6i', 'admin');
