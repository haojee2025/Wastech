-- Wastech schema (MySQL 8+)

CREATE TABLE users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(120) NOT NULL,
  email VARCHAR(160) NOT NULL UNIQUE,
  phone VARCHAR(30),
  role ENUM('recycler','collector') NOT NULL DEFAULT 'recycler',
  password_hash VARCHAR(255) NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE machines (
  id INT AUTO_INCREMENT PRIMARY KEY,
  code VARCHAR(40) NOT NULL UNIQUE,
  name VARCHAR(120) NOT NULL,
  location VARCHAR(255) NOT NULL,
  max_volume_l DECIMAL(10,3) NOT NULL,
  max_weight_kg DECIMAL(10,3) NOT NULL,
  current_volume_l DECIMAL(10,3) NOT NULL DEFAULT 0,
  current_weight_kg DECIMAL(10,3) NOT NULL DEFAULT 0,
  last_pickup_at DATETIME NULL,
  active TINYINT(1) NOT NULL DEFAULT 1,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE item_types (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(80) NOT NULL UNIQUE,
  hazardous TINYINT(1) NOT NULL DEFAULT 0
);

CREATE TABLE drop_events (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  machine_id INT NOT NULL,
  item_type_id INT NOT NULL,
  est_weight_kg DECIMAL(10,3) NOT NULL DEFAULT 0,
  est_volume_l DECIMAL(10,3) NOT NULL DEFAULT 0,
  note VARCHAR(255),
  status ENUM('accepted','rejected') NOT NULL,
  reason VARCHAR(80),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_de_user FOREIGN KEY (user_id) REFERENCES users(id),
  CONSTRAINT fk_de_machine FOREIGN KEY (machine_id) REFERENCES machines(id),
  CONSTRAINT fk_de_item FOREIGN KEY (item_type_id) REFERENCES item_types(id),
  INDEX idx_de_user_created (user_id, created_at),
  INDEX idx_de_machine_status_created (machine_id, status, created_at)
);

CREATE TABLE pickups (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,
  machine_id INT NOT NULL,
  collector_id INT NOT NULL,
  total_weight_kg DECIMAL(10,3) NOT NULL DEFAULT 0,
  total_volume_l DECIMAL(10,3) NOT NULL DEFAULT 0,
  photo_path VARCHAR(255),
  notes VARCHAR(255),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_pu_machine FOREIGN KEY (machine_id) REFERENCES machines(id),
  CONSTRAINT fk_pu_collector FOREIGN KEY (collector_id) REFERENCES users(id),
  INDEX idx_pickups_machine_created (machine_id, created_at)
);

-- Optional: lightweight audit logs
CREATE TABLE audit_logs (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,
  actor_id INT,
  action VARCHAR(80),
  entity VARCHAR(80),
  entity_id BIGINT,
  meta_json JSON,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
