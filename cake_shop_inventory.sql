-- SweetBites database for the supplied SQA_1AG1 project.
-- Modules: accounts, ingredient stock, suppliers, purchase orders and cake catalog.
CREATE DATABASE IF NOT EXISTS cake_shop_inventory
  CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE cake_shop_inventory;

CREATE TABLE IF NOT EXISTS users (
  user_id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  username VARCHAR(50) NOT NULL,
  password_hash VARCHAR(255) NOT NULL,
  role ENUM('Admin', 'User') NOT NULL,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (user_id),
  UNIQUE KEY uq_users_username (username)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS ingredients (
  ingredient_id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  ingredient_name VARCHAR(100) NOT NULL,
  category ENUM('Baking', 'Dairy', 'Fruits', 'Sweeteners', 'Others') NOT NULL,
  quantity DECIMAL(10,2) NOT NULL DEFAULT 0,
  unit ENUM('KG', 'G', 'PCS', 'L', 'ML') NOT NULL,
  expiry_date DATE DEFAULT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (ingredient_id),
  UNIQUE KEY uq_ingredients_name (ingredient_name)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS supplier (
  supplier_id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  supplier_name VARCHAR(100) NOT NULL,
  contact_person VARCHAR(100) DEFAULT NULL,
  phone VARCHAR(20) DEFAULT NULL,
  email VARCHAR(100) DEFAULT NULL,
  address TEXT DEFAULT NULL,
  status ENUM('Active', 'Inactive') NOT NULL DEFAULT 'Active',
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (supplier_id)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS purchase_orders (
  purchase_order_id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  po_number VARCHAR(20) NOT NULL,
  supplier_id INT UNSIGNED NOT NULL,
  order_date DATE NOT NULL,
  expected_date DATE NOT NULL,
  status ENUM('Pending', 'Processing', 'Ordered', 'Received', 'Cancelled') NOT NULL DEFAULT 'Pending',
  remarks TEXT DEFAULT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (purchase_order_id),
  UNIQUE KEY uq_purchase_orders_number (po_number),
  CONSTRAINT fk_purchase_orders_supplier FOREIGN KEY (supplier_id)
    REFERENCES supplier (supplier_id) ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS purchase_order_items (
  purchase_order_item_id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  purchase_order_id INT UNSIGNED NOT NULL,
  ingredient_name VARCHAR(100) NOT NULL,
  quantity DECIMAL(10,2) NOT NULL,
  unit VARCHAR(10) NOT NULL,
  unit_price DECIMAL(10,2) NOT NULL DEFAULT 0,
  PRIMARY KEY (purchase_order_item_id),
  UNIQUE KEY uq_order_ingredient (purchase_order_id, ingredient_name),
  CONSTRAINT fk_purchase_order_items_order FOREIGN KEY (purchase_order_id)
    REFERENCES purchase_orders (purchase_order_id) ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS products (
  product_id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  product_name VARCHAR(150) NOT NULL,
  category VARCHAR(60) NOT NULL,
  description TEXT DEFAULT NULL,
  flavor VARCHAR(80) DEFAULT NULL,
  size VARCHAR(30) DEFAULT NULL,
  price DECIMAL(10,2) NOT NULL DEFAULT 0,
  availability ENUM('Available', 'Out of Stock', 'Pre-Order') NOT NULL DEFAULT 'Available',
  stock_quantity INT UNSIGNED DEFAULT NULL,
  image_path VARCHAR(255) DEFAULT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (product_id),
  UNIQUE KEY uq_products_name (product_name)
) ENGINE=InnoDB;

-- Demo login credentials from the original login page; passwords are bcrypt hashes.
INSERT INTO users (username, password_hash, role) VALUES
('admin', '$2y$12$J8Wzv75sOFtJFXROp9Ejse/OEbOP6iNm7GkSjEaosVyzI3zVzpFUa', 'Admin'),
('user', '$2y$12$oHTxZQb.lUR5hcSx1Q6d8.JHqVMiqZ.osIbljkGaRtHsDAbIjpPaW', 'User')
ON DUPLICATE KEY UPDATE password_hash = VALUES(password_hash), role = VALUES(role), is_active = 1;

-- Ingredient stock records shown by the original Ingredient Stock module.
INSERT INTO ingredients (ingredient_name, category, quantity, unit, expiry_date) VALUES
('Flour', 'Baking', 25, 'KG', '2026-12-15'),
('Sugar', 'Sweeteners', 8, 'KG', '2027-01-20'),
('Butter', 'Dairy', 5, 'KG', '2026-10-20')
ON DUPLICATE KEY UPDATE category = VALUES(category), quantity = VALUES(quantity), unit = VALUES(unit), expiry_date = VALUES(expiry_date);

-- Supplier names are the values shown in the purchase-order form and sample records.
INSERT INTO supplier (supplier_name, status)
SELECT seed.supplier_name, 'Active'
FROM (
  SELECT 'Baker Supply Co.' AS supplier_name UNION ALL SELECT 'Fresh Dairy Traders' UNION ALL
  SELECT 'Cocoa & More' UNION ALL SELECT 'Market Fresh Produce' UNION ALL
  SELECT 'Kirthana Supplies' UNION ALL SELECT 'Baker''s Mart' UNION ALL
  SELECT 'Fresh Dairy Sdn Bhd' UNION ALL SELECT 'Sugar & Flour Trading' UNION ALL
  SELECT 'Daily Ingredients Co.' UNION ALL SELECT 'Cake Supplies MY' UNION ALL
  SELECT 'Happy Baking Sdn Bhd' UNION ALL SELECT 'TasteWell Supplier'
) AS seed
WHERE NOT EXISTS (SELECT 1 FROM supplier s WHERE s.supplier_name = seed.supplier_name);

-- Sample purchase orders copied from the original JavaScript demo data.
INSERT INTO purchase_orders (po_number, supplier_id, order_date, expected_date, status, remarks)
SELECT seed.po_number, s.supplier_id, seed.order_date, seed.expected_date, seed.status, ''
FROM (
  SELECT 'PO-2026015' AS po_number, 'Kirthana Supplies' AS supplier_name, '2026-10-10' AS order_date, '2026-10-15' AS expected_date, 'Pending' AS status UNION ALL
  SELECT 'PO-2026014', 'Baker''s Mart', '2026-10-05', '2026-10-08', 'Received' UNION ALL
  SELECT 'PO-2026013', 'Fresh Dairy Sdn Bhd', '2026-10-01', '2026-10-04', 'Processing' UNION ALL
  SELECT 'PO-2026012', 'Sugar & Flour Trading', '2026-09-28', '2026-10-03', 'Cancelled' UNION ALL
  SELECT 'PO-2026011', 'Daily Ingredients Co.', '2026-09-20', '2026-09-25', 'Received' UNION ALL
  SELECT 'PO-2026010', 'Cake Supplies MY', '2026-09-15', '2026-09-18', 'Pending' UNION ALL
  SELECT 'PO-2026009', 'Happy Baking Sdn Bhd', '2026-09-10', '2026-09-12', 'Received' UNION ALL
  SELECT 'PO-2026008', 'TasteWell Supplier', '2026-09-01', '2026-09-05', 'Processing'
) AS seed
JOIN supplier s ON s.supplier_name = seed.supplier_name
ON DUPLICATE KEY UPDATE supplier_id = VALUES(supplier_id), order_date = VALUES(order_date), expected_date = VALUES(expected_date), status = VALUES(status);

-- Purchase-order line items copied from the same sample records.
INSERT INTO purchase_order_items (purchase_order_id, ingredient_name, quantity, unit, unit_price)
SELECT po.purchase_order_id, seed.ingredient_name, seed.quantity, seed.unit, seed.unit_price
FROM (
  SELECT 'PO-2026015' AS po_number, 'All-purpose flour' AS ingredient_name, 10 AS quantity, 'kg' AS unit, 4.50 AS unit_price UNION ALL
  SELECT 'PO-2026015', 'Unsalted butter', 5, 'kg', 15.00 UNION ALL SELECT 'PO-2026015', 'Eggs', 20, 'pcs', 1.25 UNION ALL
  SELECT 'PO-2026014', 'All-purpose flour', 20, 'kg', 5.00 UNION ALL SELECT 'PO-2026014', 'Caster sugar', 10, 'kg', 5.05 UNION ALL
  SELECT 'PO-2026013', 'Unsalted butter', 10, 'kg', 15.00 UNION ALL SELECT 'PO-2026013', 'Eggs', 100, 'pcs', 1.30 UNION ALL
  SELECT 'PO-2026012', 'Caster sugar', 20, 'kg', 6.00 UNION ALL SELECT 'PO-2026012', 'All-purpose flour', 10, 'kg', 7.00 UNION ALL
  SELECT 'PO-2026011', 'Heavy cream', 10, 'L', 16.00 UNION ALL SELECT 'PO-2026011', 'Unsalted butter', 20, 'kg', 15.00 UNION ALL SELECT 'PO-2026011', 'Eggs', 100, 'pcs', 1.05 UNION ALL
  SELECT 'PO-2026010', 'Vanilla extract', 2, 'L', 42.50 UNION ALL
  SELECT 'PO-2026009', 'All-purpose flour', 100, 'kg', 4.50 UNION ALL SELECT 'PO-2026009', 'Caster sugar', 40, 'kg', 6.00 UNION ALL SELECT 'PO-2026009', 'Eggs', 100, 'pcs', 1.10 UNION ALL
  SELECT 'PO-2026008', 'Cocoa powder', 10, 'kg', 18.00 UNION ALL SELECT 'PO-2026008', 'Cream cheese', 10, 'kg', 14.00
) AS seed
JOIN purchase_orders po ON po.po_number = seed.po_number
ON DUPLICATE KEY UPDATE quantity = VALUES(quantity), unit = VALUES(unit), unit_price = VALUES(unit_price);

-- Product catalog cards from the original Product Catalog page.
INSERT INTO products (product_name, category, description, flavor, size, price, availability, stock_quantity, image_path) VALUES
('Chocolate Fudge Cake', 'Birthday Cake', 'Rich, moist chocolate sponge layered with velvety fudge frosting.', 'Chocolate', 'Medium', 89.00, 'Available', 18, 'Images/choco_fudge.jpg'),
('Vanilla Rose Wedding Cake', 'Wedding Cake', 'A vanilla sponge with rose buttercream and edible gold accents.', 'Vanilla Rose', 'Large', 90.00, 'Available', NULL, 'Images/vanilla_rose.jpg'),
('Strawberry Cheesecake', 'Cheesecake', 'Creamy baked cheesecake topped with fresh strawberry compote.', 'Strawberry', 'Medium', 120.00, 'Pre-Order', NULL, 'Images/strawberry_cheese.jpg')
ON DUPLICATE KEY UPDATE category = VALUES(category), description = VALUES(description), flavor = VALUES(flavor), size = VALUES(size), price = VALUES(price), availability = VALUES(availability), stock_quantity = VALUES(stock_quantity), image_path = VALUES(image_path);
