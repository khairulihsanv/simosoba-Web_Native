-- ==============================================================
-- ANTIGRAVITY — Database Schema
-- Compatible with TiDB Cloud (MySQL 8.0 protocol)
-- Run this once on your TiDB cluster to initialize all tables.
-- ==============================================================

CREATE DATABASE IF NOT EXISTS antigravity CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE antigravity;

-- ---------------------------------------------------------------
-- TABLE: users
-- Stores all authenticated users. Role determines access level.
-- ---------------------------------------------------------------
CREATE TABLE IF NOT EXISTS users (
  id            BIGINT       NOT NULL AUTO_INCREMENT,
  name          VARCHAR(120) NOT NULL,
  email         VARCHAR(180) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  -- 'superadmin' = full access; 'admin' = staff/operator access
  role          ENUM('superadmin', 'admin') NOT NULL DEFAULT 'admin',
  created_at    DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at    DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  INDEX idx_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ---------------------------------------------------------------
-- TABLE: medicines
-- Core medicine catalogue with pricing, stock, and intelligence fields.
-- buy_price / sell_price → financial margin calculation
-- safety_stock           → low-stock alert threshold
-- expired_at             → expiry warning (30/60/90-day color coding)
-- seasonal_tag           → drives the predictive recommendation engine
-- ---------------------------------------------------------------
CREATE TABLE IF NOT EXISTS medicines (
  id            BIGINT          NOT NULL AUTO_INCREMENT,
  name          VARCHAR(200)    NOT NULL,
  category      VARCHAR(100)    NOT NULL DEFAULT 'General',
  unit          VARCHAR(30)     NOT NULL DEFAULT 'pcs',
  buy_price     DECIMAL(15, 2)  NOT NULL DEFAULT 0.00,
  sell_price    DECIMAL(15, 2)  NOT NULL DEFAULT 0.00,
  stock_current INT             NOT NULL DEFAULT 0,
  safety_stock  INT             NOT NULL DEFAULT 10,   -- trigger LOW STOCK alert below this
  expired_at    DATE            NULL,                  -- NULL = no expiry (e.g. medical devices)
  -- seasonal_tag drives the predictive engine:
  --   'Rainy'  → recommend stock increase Oct–Mar
  --   'Dry'    → recommend stock increase Apr–Sep
  --   'None'   → no seasonal pattern
  seasonal_tag  ENUM('Rainy', 'Dry', 'None') NOT NULL DEFAULT 'None',
  notes         TEXT            NULL,
  created_at    DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at    DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  INDEX idx_name        (name),
  INDEX idx_seasonal    (seasonal_tag),
  INDEX idx_expired_at  (expired_at),
  INDEX idx_stock       (stock_current)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ---------------------------------------------------------------
-- TABLE: inventory_logs
-- Immutable audit trail of every stock movement.
-- type='in'  → goods received / restock
-- type='out' → dispensed / sold
-- total_price = unit_price × quantity at the time of transaction
-- ---------------------------------------------------------------
CREATE TABLE IF NOT EXISTS inventory_logs (
  id          BIGINT          NOT NULL AUTO_INCREMENT,
  medicine_id BIGINT          NOT NULL,
  user_id     BIGINT          NOT NULL,   -- who performed the transaction
  -- 'in' = stock received; 'out' = stock dispensed/sold
  type        ENUM('in', 'out') NOT NULL,
  quantity    INT             NOT NULL,
  -- Snapshot the price at transaction time (prices may change later)
  unit_price  DECIMAL(15, 2)  NOT NULL DEFAULT 0.00,
  total_price DECIMAL(15, 2)  NOT NULL DEFAULT 0.00,
  notes       VARCHAR(255)    NULL,
  created_at  DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  INDEX idx_medicine_id (medicine_id),
  INDEX idx_user_id     (user_id),
  INDEX idx_created_at  (created_at),
  INDEX idx_type        (type),
  CONSTRAINT fk_log_medicine FOREIGN KEY (medicine_id) REFERENCES medicines (id) ON DELETE CASCADE,
  CONSTRAINT fk_log_user     FOREIGN KEY (user_id)     REFERENCES users     (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ---------------------------------------------------------------
-- SEED DATA: Default superadmin account
-- Password: Admin@123  (bcrypt hash — change immediately in production)
-- ---------------------------------------------------------------
INSERT IGNORE INTO users (name, email, password_hash, role) VALUES
(
  'Super Admin',
  'admin@antigravity.app',
  -- bcrypt hash of 'Admin@123' with 12 salt rounds
  '$2a$12$LQ2ygjB0M6JZ2jtBSFKR9.D7vNsL5tQfKl.9rMKsG5FXQLe3jBh3K',
  'superadmin'
);

-- ---------------------------------------------------------------
-- SEED DATA: Sample medicines for demonstration
-- ---------------------------------------------------------------
INSERT IGNORE INTO medicines (name, category, unit, buy_price, sell_price, stock_current, safety_stock, expired_at, seasonal_tag) VALUES
('Paracetamol 500mg',   'Analgesic',     'tablet', 500,    1200,  250, 50,  '2026-12-31', 'None'),
('Amoxicillin 500mg',   'Antibiotic',    'capsule',3500,   8000,  120, 30,  '2026-08-15', 'Rainy'),
('Loratadine 10mg',     'Antihistamine', 'tablet', 1800,   4500,  80,  20,  '2027-03-01', 'Rainy'),
('ORS Sachet',          'Electrolyte',   'sachet', 2000,   5000,  200, 40,  '2027-06-30', 'Dry'),
('Vitamin C 1000mg',    'Supplement',    'tablet', 800,    2000,  300, 60,  '2027-09-15', 'None'),
('Ibuprofen 400mg',     'NSAID',         'tablet', 1200,   3000,  90,  25,  '2026-05-20', 'None'),
('Cetirizine 10mg',     'Antihistamine', 'tablet', 1500,   3800,  60,  15,  '2027-01-10', 'Rainy'),
('Antacid Syrup 200ml', 'Antacid',       'bottle', 8000,   18000, 45,  10,  '2026-11-30', 'Dry');
