-- =====================================================
-- TINK E-Commerce Jewelry Store - Database Creation
-- =====================================================
-- Project: TINK Web Application Development (TMF3973)
-- Database: MySQL 8.0+
-- Version: 1.0 Final
-- =====================================================

-- Create Database
CREATE DATABASE IF NOT EXISTS tink_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE tink_db;

-- =====================================================
-- TABLE: DESIGNER
-- =====================================================
CREATE TABLE DESIGNER (
    DESIGNER_ID INT AUTO_INCREMENT PRIMARY KEY,
    DESIGNER_NAME VARCHAR(100) NOT NULL UNIQUE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- TABLE: CUSTOMER
-- =====================================================
CREATE TABLE CUSTOMER (
    CUSTOMER_ID INT AUTO_INCREMENT PRIMARY KEY,
    CUSTOMER_NAME VARCHAR(100) NOT NULL,
    CUSTOMER_EMAIL VARCHAR(100) NOT NULL UNIQUE,
    CUSTOMER_PW VARCHAR(255) NOT NULL,
    CUSTOMER_TEL VARCHAR(15) NOT NULL,
    CUSTOMER_ADDRESS VARCHAR(255) NOT NULL,
    CUSTOMER_DATE DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX IDX_CUST_EMAIL (CUSTOMER_EMAIL)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- TABLE: ITEM
-- =====================================================
CREATE TABLE ITEM (
    ITEM_ID INT AUTO_INCREMENT PRIMARY KEY,
    DESIGNER_ID INT NOT NULL,
    ITEM_CATEGORY VARCHAR(50) NOT NULL,
    ITEM_NAME VARCHAR(150) NOT NULL,
    ITEM_DESCRIPTION VARCHAR(500) NOT NULL,
    ITEM_MATERIAL VARCHAR(100) NOT NULL,
    ITEM_PRICE DECIMAL(10,2) NOT NULL,
    ITEM_STOCK INT NOT NULL DEFAULT 0,
    ITEM_IMAGE VARCHAR(255),
    ITEM_DATE DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (DESIGNER_ID) REFERENCES DESIGNER(DESIGNER_ID) ON DELETE RESTRICT,
    INDEX IDX_ITEM_NAME (ITEM_NAME),
    INDEX IDX_ITEM_CATEGORY (ITEM_CATEGORY),
    INDEX IDX_ITEM_PRICE (ITEM_PRICE),
    CHECK (ITEM_PRICE > 0),
    CHECK (ITEM_STOCK >= 0),
    CHECK (ITEM_CATEGORY IN ('Necklaces', 'Earrings', 'Bracelets', 'Rings'))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- TABLE: CHARM
-- =====================================================
CREATE TABLE CHARM (
    CHARM_ID INT AUTO_INCREMENT PRIMARY KEY,
    CHARM_NAME VARCHAR(100) NOT NULL UNIQUE,
    CHARM_TYPE VARCHAR(50) NOT NULL,
    CHARM_MATERIAL VARCHAR(100) NOT NULL,
    CHARM_PRICE DECIMAL(10,2) NOT NULL,
    CHARM_COMPATIBLE_CAT VARCHAR(100) NOT NULL,
    CHARM_IMAGE VARCHAR(255),
    CHARM_ACTIVE BOOLEAN NOT NULL DEFAULT TRUE,
    INDEX IDX_CHARM_NAME (CHARM_NAME),
    INDEX IDX_CHARM_ACTIVE (CHARM_ACTIVE),
    CHECK (CHARM_PRICE >= 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- TABLE: ITEMCHARM (M:M Junction)
-- =====================================================
CREATE TABLE ITEMCHARM (
    ITEM_ID INT NOT NULL,
    CHARM_ID INT NOT NULL,
    PRIMARY KEY (ITEM_ID, CHARM_ID),
    FOREIGN KEY (ITEM_ID) REFERENCES ITEM(ITEM_ID) ON DELETE CASCADE,
    FOREIGN KEY (CHARM_ID) REFERENCES CHARM(CHARM_ID) ON DELETE RESTRICT,
    INDEX IDX_ITEMCHARM_ITEM (ITEM_ID),
    INDEX IDX_ITEMCHARM_CHARM (CHARM_ID)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- TABLE: REVIEW (M:M Junction with Attributes)
-- =====================================================
CREATE TABLE REVIEW (
    CUSTOMER_ID INT NOT NULL,
    ITEM_ID INT NOT NULL,
    REVIEW_RATING DECIMAL(2,1) NOT NULL,
    REVIEW_COMMENT TEXT NOT NULL,
    REVIEW_DATE DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (CUSTOMER_ID, ITEM_ID),
    FOREIGN KEY (CUSTOMER_ID) REFERENCES CUSTOMER(CUSTOMER_ID) ON DELETE CASCADE,
    FOREIGN KEY (ITEM_ID) REFERENCES ITEM(ITEM_ID) ON DELETE CASCADE,
    INDEX IDX_REVIEW_ITEM (ITEM_ID),
    INDEX IDX_REVIEW_CUSTOMER (CUSTOMER_ID),
    CHECK (REVIEW_RATING BETWEEN 1 AND 5)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- TABLE: CART
-- =====================================================
CREATE TABLE CART (
    CART_ID INT AUTO_INCREMENT PRIMARY KEY,
    CUSTOMER_ID INT NOT NULL UNIQUE,
    CART_STATUS VARCHAR(20) NOT NULL DEFAULT 'active',
    FOREIGN KEY (CUSTOMER_ID) REFERENCES CUSTOMER(CUSTOMER_ID) ON DELETE CASCADE,
    INDEX IDX_CART_CUSTOMER (CUSTOMER_ID),
    INDEX IDX_CART_STATUS (CART_STATUS),
    CHECK (CART_STATUS IN ('active', 'abandoned', 'converted'))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- TABLE: CARTITEM (M:M Junction with Attributes)
-- =====================================================
CREATE TABLE CARTITEM (
    CART_ID INT NOT NULL,
    ITEM_ID INT NOT NULL,
    CARTITEM_QUANTITY INT NOT NULL,
    CARTITEM_PRICE DECIMAL(10,2) NOT NULL,
    PRIMARY KEY (CART_ID, ITEM_ID),
    FOREIGN KEY (CART_ID) REFERENCES CART(CART_ID) ON DELETE CASCADE,
    FOREIGN KEY (ITEM_ID) REFERENCES ITEM(ITEM_ID) ON DELETE RESTRICT,
    INDEX IDX_CARTITEM_CART (CART_ID),
    INDEX IDX_CARTITEM_ITEM (ITEM_ID),
    CHECK (CARTITEM_QUANTITY > 0),
    CHECK (CARTITEM_PRICE > 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- TABLE: ORDER
-- =====================================================
CREATE TABLE `ORDER` (
    ORDER_ID INT AUTO_INCREMENT PRIMARY KEY,
    CART_ID INT,
    ORDER_DATE DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    ORDER_STATUS VARCHAR(20) NOT NULL DEFAULT 'pending',
    ORDER_TOTALAMOUNT DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (CART_ID) REFERENCES CART(CART_ID) ON DELETE SET NULL,
    INDEX IDX_ORDER_DATE (ORDER_DATE),
    INDEX IDX_ORDER_STATUS (ORDER_STATUS),
    CHECK (ORDER_STATUS IN ('pending', 'confirmed', 'shipped', 'delivered', 'cancelled')),
    CHECK (ORDER_TOTALAMOUNT >= 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- TABLE: PAYMENT
-- =====================================================
CREATE TABLE PAYMENT (
    PAYMENT_ID INT AUTO_INCREMENT PRIMARY KEY,
    ORDER_ID INT NOT NULL,
    PAYMENT_DATE DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PAYMENT_AMOUNT DECIMAL(10,2) NOT NULL,
    PAYMENT_METHOD VARCHAR(50) NOT NULL,
    PAYMENT_STATUS VARCHAR(20) NOT NULL DEFAULT 'pending',
    FOREIGN KEY (ORDER_ID) REFERENCES `ORDER`(ORDER_ID) ON DELETE RESTRICT,
    INDEX IDX_PAYMENT_ORDER (ORDER_ID),
    INDEX IDX_PAYMENT_DATE (PAYMENT_DATE),
    CHECK (PAYMENT_AMOUNT > 0),
    CHECK (PAYMENT_METHOD IN ('CreditCard', 'DebitCard', 'OnlineBanking', 'EWallet')),
    CHECK (PAYMENT_STATUS IN ('pending', 'successful', 'failed', 'refunded'))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- DUMMY DATA INSERTION
-- =====================================================

-- Insert 2 Designers
INSERT INTO DESIGNER (DESIGNER_ID, DESIGNER_NAME) VALUES
(1, 'Sterling Creations Ltd'),
(2, 'Elegance Jewelry Studio');

-- Insert 10 Customers
INSERT INTO CUSTOMER (CUSTOMER_ID, CUSTOMER_NAME, CUSTOMER_EMAIL, CUSTOMER_PW, CUSTOMER_TEL, CUSTOMER_ADDRESS, CUSTOMER_DATE) VALUES
(1001, 'Nur Aini', 'nur.aini@email.com', '$2y$10$N9qo8uLOickgx2ZMRZoMye0nnHVmyqKn0F5x5z5z5z5', '012-345-6789', '123 Jalan Merdeka, 50000 Kuala Lumpur', '2025-01-10 08:30:00'),
(1002, 'Siti Farah', 'siti.farah@email.com', '$2y$10$N9qo8uLOickgx2ZMRZoMye0nnHVmyqKn0F5x5z5z5z6', '011-222-3333', '456 Persiaran Sultan Ismail, 50250 KL', '2025-01-11 09:15:00'),
(1003, 'Fatimah Zahra', 'fatimah.z@email.com', '$2y$10$N9qo8uLOickgx2ZMRZoMye0nnHVmyqKn0F5x5z5z5z7', '016-444-5555', '789 Jalan Bukit Bintang, 55100 KL', '2025-01-12 10:45:00'),
(1004, 'Yasmin Sofiya', 'yasmin.s@email.com', '$2y$10$N9qo8uLOickgx2ZMRZoMye0nnHVmyqKn0F5x5z5z5z8', '013-666-7777', '321 Jalan Ampang, 68100 Ampang', '2025-01-13 11:20:00'),
(1005, 'Leila Nazira', 'leila.n@email.com', '$2y$10$N9qo8uLOickgx2ZMRZoMye0nnHVmyqKn0F5x5z5z5z9', '017-888-9999', '654 Jalan Kebun, 50000 KL', '2025-01-14 12:30:00'),
(1006, 'Amira Putri', 'amira.p@email.com', '$2y$10$N9qo8uLOickgx2ZMRZoMye0nnHVmyqKn0F5x5z5z5za', '010-111-2222', '987 Jalan Damansara, 50490 KL', '2025-01-15 13:45:00'),
(1007, 'Nadia Husna', 'nadia.h@email.com', '$2y$10$N9qo8uLOickgx2ZMRZoMye0nnHVmyqKn0F5x5z5z5zb', '014-333-4444', '111 Jalan Kota, 68100 KL', '2025-01-16 14:15:00'),
(1008, 'Hana Salma', 'hana.s@email.com', '$2y$10$N9qo8uLOickgx2ZMRZoMye0nnHVmyqKn0F5x5z5z5zc', '015-555-6666', '222 Jalan Sultan Sulaiman, 50000 KL', '2025-01-17 15:30:00'),
(1009, 'Zara Eka', 'zara.e@email.com', '$2y$10$N9qo8uLOickgx2ZMRZoMye0nnHVmyqKn0F5x5z5z5zd', '012-777-8888', '333 Jalan Taming Sari, 68100 KL', '2025-01-18 16:45:00'),
(1010, 'Maya Ilya', 'maya.i@email.com', '$2y$10$N9qo8uLOickgx2ZMRZoMye0nnHVmyqKn0F5x5z5z5ze', '011-999-0000', '444 Jalan Raja Chulan, 50200 KL', '2025-01-19 17:20:00');

-- Insert 10 Items (Products)
INSERT INTO ITEM (ITEM_ID, DESIGNER_ID, ITEM_CATEGORY, ITEM_NAME, ITEM_DESCRIPTION, ITEM_MATERIAL, ITEM_PRICE, ITEM_STOCK, ITEM_IMAGE, ITEM_DATE) VALUES
(2001, 1, 'Necklaces', 'Sterling Silver Heart Necklace', 'Elegant 925 sterling silver heart pendant with 45cm chain', '925 Sterling Silver', 89.99, 45, '/images/products/item_2001.jpg', '2025-01-01 08:00:00'),
(2002, 1, 'Bracelets', 'Build-Your-Own Charm Bracelet', 'Customizable bracelet with interchangeable charms', '925 Sterling Silver', 59.99, 30, '/images/products/item_2002.jpg', '2025-01-02 09:30:00'),
(2003, 2, 'Earrings', 'Pearl Drop Earrings', 'Classic pearl drop earrings with gold plating', '18K Gold Plating', 65.50, 25, '/images/products/item_2003.jpg', '2025-01-03 10:15:00'),
(2004, 2, 'Rings', 'Diamond Solitaire Ring', 'Elegant diamond solitaire engagement ring', '925 Sterling Silver', 299.99, 12, '/images/products/item_2004.jpg', '2025-01-04 11:00:00'),
(2005, 1, 'Necklaces', 'Gold Infinity Pendant', 'Modern gold infinity symbol pendant', '18K Gold Plating', 74.99, 20, '/images/products/item_2005.jpg', '2025-01-05 12:30:00'),
(2006, 2, 'Bracelets', 'Rose Gold Bangle Bracelet', 'Sleek rose gold bangle bracelet', 'Rose Gold Plating', 54.99, 35, '/images/products/item_2006.jpg', '2025-01-06 13:45:00'),
(2007, 1, 'Earrings', 'Cubic Zirconia Studs', 'Sparkling cubic zirconia stud earrings', 'Cubic Zirconia', 39.99, 50, '/images/products/item_2007.jpg', '2025-01-07 14:20:00'),
(2008, 2, 'Rings', 'Moonstone Ring', 'Mystical moonstone statement ring', 'Sterling Silver & Moonstone', 89.99, 15, '/images/products/item_2008.jpg', '2025-01-08 15:10:00'),
(2009, 1, 'Necklaces', 'Birthstone Pendant Necklace', 'Personalized birthstone pendant with name engraving', '925 Sterling Silver', 79.99, 28, '/images/products/item_2009.jpg', '2025-01-09 16:00:00'),
(2010, 2, 'Bracelets', 'Beaded Stretch Bracelet', 'Colorful beaded stretch bracelet', 'Semi-Precious Beads', 44.99, 40, '/images/products/item_2010.jpg', '2025-01-10 17:30:00');

-- Insert 10 Charms
INSERT INTO CHARM (CHARM_ID, CHARM_NAME, CHARM_TYPE, CHARM_MATERIAL, CHARM_PRICE, CHARM_COMPATIBLE_CAT, CHARM_IMAGE, CHARM_ACTIVE) VALUES
(11001, 'Heart Charm', 'Heart', '925 Sterling Silver', 15.00, 'Bracelets', '/images/charms/charm_11001.jpg', TRUE),
(11002, 'Star Charm', 'Star', '925 Sterling Silver', 12.00, 'Bracelets', '/images/charms/charm_11002.jpg', TRUE),
(11003, 'Moon Charm', 'Moon', '925 Sterling Silver', 14.00, 'Bracelets', '/images/charms/charm_11003.jpg', TRUE),
(11004, 'Flower Charm', 'Flower', '925 Sterling Silver', 13.00, 'Bracelets', '/images/charms/charm_11004.jpg', TRUE),
(11005, 'Letter A Charm', 'Letter', '925 Sterling Silver', 10.00, 'Bracelets', '/images/charms/charm_11005.jpg', TRUE),
(11006, 'Letter B Charm', 'Letter', '925 Sterling Silver', 10.00, 'Bracelets', '/images/charms/charm_11006.jpg', TRUE),
(11007, 'Love Charm', 'Word', '925 Sterling Silver', 16.00, 'Bracelets', '/images/charms/charm_11007.jpg', TRUE),
(11008, 'Crown Charm', 'Crown', '925 Sterling Silver', 18.00, 'Bracelets', '/images/charms/charm_11008.jpg', TRUE),
(11009, 'Butterfly Charm', 'Butterfly', '925 Sterling Silver', 14.50, 'Bracelets', '/images/charms/charm_11009.jpg', TRUE),
(11010, 'Cross Charm', 'Cross', '925 Sterling Silver', 12.00, 'Bracelets', '/images/charms/charm_11010.jpg', TRUE);

-- Link Charms to Bracelets (ITEMCHARM)
INSERT INTO ITEMCHARM (ITEM_ID, CHARM_ID) VALUES
(2002, 11001), -- Bracelet can have Heart Charm
(2002, 11002), -- Bracelet can have Star Charm
(2002, 11003), -- Bracelet can have Moon Charm
(2002, 11004), -- Bracelet can have Flower Charm
(2002, 11005), -- Bracelet can have Letter A Charm
(2002, 11006), -- Bracelet can have Letter B Charm
(2002, 11007), -- Bracelet can have Love Charm
(2002, 11008), -- Bracelet can have Crown Charm
(2006, 11001), -- Rose Gold Bracelet can have Heart Charm
(2006, 11002); -- Rose Gold Bracelet can have Star Charm

-- Insert Reviews (10 dummy reviews)
INSERT INTO REVIEW (CUSTOMER_ID, ITEM_ID, REVIEW_RATING, REVIEW_COMMENT, REVIEW_DATE) VALUES
(1001, 2001, 5.0, 'Absolutely beautiful! The quality is excellent and shipping was fast.', '2025-01-20 10:30:00'),
(1002, 2001, 4.5, 'Very nice necklace, perfect for daily wear.', '2025-01-21 11:15:00'),
(1003, 2003, 5.0, 'Stunning pearl earrings! Exceeded my expectations.', '2025-01-22 12:45:00'),
(1004, 2002, 4.0, 'Good quality bracelet, charm selection is great.', '2025-01-23 13:20:00'),
(1005, 2005, 5.0, 'The gold infinity pendant is gorgeous and elegant.', '2025-01-24 14:30:00'),
(1006, 2004, 4.5, 'Diamond ring looks stunning, very satisfied with purchase.', '2025-01-25 15:45:00'),
(1007, 2006, 5.0, 'Perfect rose gold bracelet, exactly as described.', '2025-01-26 16:20:00'),
(1008, 2007, 4.0, 'Cubic zirconia studs are sparkly and look expensive.', '2025-01-27 17:10:00'),
(1009, 2009, 5.0, 'Birthstone pendant is perfect with my name engraved.', '2025-01-28 18:30:00'),
(1010, 2010, 4.5, 'Beaded bracelet is colorful and comfortable to wear.', '2025-01-29 19:00:00');

-- Insert Shopping Carts
INSERT INTO CART (CART_ID, CUSTOMER_ID, CART_STATUS) VALUES
(4001, 1001, 'active'),
(4002, 1002, 'active'),
(4003, 1003, 'converted'),
(4004, 1004, 'active'),
(4005, 1005, 'active'),
(4006, 1006, 'abandoned'),
(4007, 1007, 'active'),
(4008, 1008, 'converted'),
(4009, 1009, 'active'),
(4010, 1010, 'active');

-- Insert Cart Items
INSERT INTO CARTITEM (CART_ID, ITEM_ID, CARTITEM_QUANTITY, CARTITEM_PRICE) VALUES
(4001, 2001, 2, 89.99),
(4001, 2003, 1, 65.50),
(4002, 2005, 1, 74.99),
(4003, 2002, 1, 59.99),
(4003, 2001, 1, 89.99),
(4004, 2004, 1, 299.99),
(4005, 2006, 2, 54.99),
(4006, 2007, 3, 39.99),
(4007, 2009, 1, 79.99),
(4008, 2002, 1, 59.99),
(4008, 2010, 2, 44.99),
(4009, 2008, 1, 89.99),
(4010, 2005, 1, 74.99),
(4010, 2007, 1, 39.99);

-- Insert Orders
INSERT INTO `ORDER` (ORDER_ID, CART_ID, ORDER_DATE, ORDER_STATUS, ORDER_TOTALAMOUNT) VALUES
(6001, 4003, '2025-01-20 14:30:00', 'confirmed', 149.98),
(6002, 4008, '2025-01-21 15:45:00', 'confirmed', 189.97),
(6003, 4001, '2025-01-22 16:20:00', 'pending', 245.48),
(6004, NULL, '2025-01-23 17:10:00', 'pending', 299.99),
(6005, NULL, '2025-01-24 18:30:00', 'shipped', 109.98),
(6006, NULL, '2025-01-25 19:00:00', 'delivered', 154.98),
(6007, NULL, '2025-01-26 10:15:00', 'confirmed', 79.99),
(6008, NULL, '2025-01-27 11:30:00', 'cancelled', 89.99),
(6009, NULL, '2025-01-28 12:45:00', 'pending', 124.98),
(6010, NULL, '2025-01-29 13:20:00', 'shipped', 114.98);

-- Insert Payments
INSERT INTO PAYMENT (PAYMENT_ID, ORDER_ID, PAYMENT_DATE, PAYMENT_AMOUNT, PAYMENT_METHOD, PAYMENT_STATUS) VALUES
(8001, 6001, '2025-01-20 14:35:00', 149.98, 'CreditCard', 'successful'),
(8002, 6002, '2025-01-21 15:50:00', 189.97, 'OnlineBanking', 'successful'),
(8003, 6003, '2025-01-22 16:25:00', 245.48, 'EWallet', 'successful'),
(8004, 6004, '2025-01-23 17:15:00', 299.99, 'DebitCard', 'successful'),
(8005, 6005, '2025-01-24 18:35:00', 109.98, 'CreditCard', 'successful'),
(8006, 6006, '2025-01-25 19:05:00', 154.98, 'OnlineBanking', 'successful'),
(8007, 6007, '2025-01-26 10:20:00', 79.99, 'EWallet', 'successful'),
(8008, 6008, '2025-01-27 11:35:00', 89.99, 'CreditCard', 'failed'),
(8009, 6009, '2025-01-28 12:50:00', 124.98, 'DebitCard', 'pending'),
(8010, 6010, '2025-01-29 13:25:00', 114.98, 'OnlineBanking', 'successful');

-- =====================================================
-- DATABASE SUMMARY
-- =====================================================
-- DESIGNERS: 2
-- CUSTOMERS: 10
-- ITEMS: 10
-- CHARMS: 10
-- ITEMCHARM LINKS: 10
-- REVIEWS: 10
-- CARTS: 10
-- CART ITEMS: 14 (multiple items per cart)
-- ORDERS: 10
-- PAYMENTS: 10
-- =====================================================

-- Verify Data
-- SELECT * FROM DESIGNER;
-- SELECT * FROM CUSTOMER;
-- SELECT * FROM ITEM;
-- SELECT * FROM CHARM;
-- SELECT * FROM ITEMCHARM;
-- SELECT * FROM REVIEW;
-- SELECT * FROM CART;
-- SELECT * FROM CARTITEM;
-- SELECT * FROM `ORDER`;
-- SELECT * FROM PAYMENT;

