-- Additive migration for the existing Warranty System. It does not delete legacy data.
CREATE TABLE IF NOT EXISTS warranty_types (
  id INT NOT NULL AUTO_INCREMENT,
  code VARCHAR(30) NOT NULL,
  name VARCHAR(100) NOT NULL,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  created_by INT NULL,
  updated_by INT NULL,
  created_at TIMESTAMP NULL,
  updated_at TIMESTAMP NULL,
  PRIMARY KEY (id), UNIQUE KEY warranty_types_code_unique (code)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
INSERT INTO warranty_types (code, name, is_active, created_at, updated_at) VALUES
  ('CAR', 'MOBIL / AUTOMOTIVE', 1, NOW(), NOW()),
  ('BUILDING', 'BUILDING / BANGUNAN', 1, NOW(), NOW()),
  ('PPF', 'PAINT PROTECTION FILM', 1, NOW(), NOW())
ON DUPLICATE KEY UPDATE name=VALUES(name), is_active=VALUES(is_active), updated_at=VALUES(updated_at);

ALTER TABLE warranties
  ADD COLUMN IF NOT EXISTS id_warranty_type INT NULL AFTER id_customer,
  ADD INDEX IF NOT EXISTS warranties_type_index (id_warranty_type),
  MODIFY status ENUM('Active','Claim','Expired','Void') NOT NULL DEFAULT 'Active';
-- Add after the column exists; nullable keeps all existing warranty rows valid.
ALTER TABLE warranties ADD CONSTRAINT warranties_type_foreign FOREIGN KEY (id_warranty_type) REFERENCES warranty_types(id) ON DELETE SET NULL;

CREATE TABLE IF NOT EXISTS warranty_vehicles (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT, id_warranty INT NOT NULL,
  no_polisi VARCHAR(20) NULL, merk_mobil VARCHAR(100) NULL, tipe_mobil VARCHAR(100) NULL,
  warna_mobil VARCHAR(50) NULL, tahun_mobil VARCHAR(4) NULL,
  created_at TIMESTAMP NULL, updated_at TIMESTAMP NULL,
  PRIMARY KEY (id), UNIQUE KEY warranty_vehicles_warranty_unique (id_warranty),
  CONSTRAINT warranty_vehicles_warranty_fk FOREIGN KEY (id_warranty) REFERENCES warranties(id_warranty) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS warranty_vehicle_items (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT, id_warranty INT NOT NULL, id_product INT NOT NULL,
  posisi_kaca VARCHAR(100) NOT NULL, tanggal_pasang DATE NOT NULL, tanggal_expired DATE NOT NULL,
  status ENUM('Active','Claim','Expired','Void') NOT NULL DEFAULT 'Active', catatan TEXT NULL,
  created_at TIMESTAMP NULL, updated_at TIMESTAMP NULL,
  PRIMARY KEY (id), KEY warranty_vehicle_items_warranty_index (id_warranty), KEY warranty_vehicle_items_product_index (id_product), KEY warranty_vehicle_items_expired_index (tanggal_expired),
  CONSTRAINT warranty_vehicle_items_warranty_fk FOREIGN KEY (id_warranty) REFERENCES warranties(id_warranty) ON DELETE CASCADE,
  CONSTRAINT warranty_vehicle_items_product_fk FOREIGN KEY (id_product) REFERENCES products(id_product)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS warranty_buildings (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT, id_warranty INT NOT NULL,
  nama_bangunan VARCHAR(150) NOT NULL, alamat TEXT NOT NULL, created_at TIMESTAMP NULL, updated_at TIMESTAMP NULL,
  PRIMARY KEY (id), UNIQUE KEY warranty_buildings_warranty_unique (id_warranty),
  CONSTRAINT warranty_buildings_warranty_fk FOREIGN KEY (id_warranty) REFERENCES warranties(id_warranty) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS warranty_building_items (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT, id_warranty INT NOT NULL, id_product INT NOT NULL,
  area_pekerjaan VARCHAR(150) NOT NULL, panjang DECIMAL(10,2) NOT NULL, lebar DECIMAL(10,2) NOT NULL,
  jumlah INT UNSIGNED NOT NULL, luas_per_item DECIMAL(12,2) NOT NULL, total_luas DECIMAL(12,2) NOT NULL,
  tanggal_pasang DATE NOT NULL, tanggal_expired DATE NOT NULL, status ENUM('Active','Claim','Expired','Void') NOT NULL DEFAULT 'Active', catatan TEXT NULL,
  created_at TIMESTAMP NULL, updated_at TIMESTAMP NULL,
  PRIMARY KEY (id), KEY warranty_building_items_warranty_index (id_warranty), KEY warranty_building_items_product_index (id_product), KEY warranty_building_items_expired_index (tanggal_expired),
  CONSTRAINT warranty_building_items_warranty_fk FOREIGN KEY (id_warranty) REFERENCES warranties(id_warranty) ON DELETE CASCADE,
  CONSTRAINT warranty_building_items_product_fk FOREIGN KEY (id_product) REFERENCES products(id_product)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS warranty_ppfs (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT, id_warranty INT NOT NULL,
  no_polisi VARCHAR(20) NULL, merk_mobil VARCHAR(100) NULL, tipe_mobil VARCHAR(100) NULL,
  warna_mobil VARCHAR(50) NULL, tahun_mobil VARCHAR(4) NULL, created_at TIMESTAMP NULL, updated_at TIMESTAMP NULL,
  PRIMARY KEY (id), UNIQUE KEY warranty_ppfs_warranty_unique (id_warranty),
  CONSTRAINT warranty_ppfs_warranty_fk FOREIGN KEY (id_warranty) REFERENCES warranties(id_warranty) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS warranty_ppf_items (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT, id_warranty INT NOT NULL, id_product INT NOT NULL,
  area_pekerjaan VARCHAR(150) NOT NULL, tanggal_pasang DATE NOT NULL, tanggal_expired DATE NOT NULL,
  status ENUM('Active','Claim','Expired','Void') NOT NULL DEFAULT 'Active', catatan TEXT NULL,
  created_at TIMESTAMP NULL, updated_at TIMESTAMP NULL,
  PRIMARY KEY (id), KEY warranty_ppf_items_warranty_index (id_warranty), KEY warranty_ppf_items_product_index (id_product), KEY warranty_ppf_items_expired_index (tanggal_expired),
  CONSTRAINT warranty_ppf_items_warranty_fk FOREIGN KEY (id_warranty) REFERENCES warranties(id_warranty) ON DELETE CASCADE,
  CONSTRAINT warranty_ppf_items_product_fk FOREIGN KEY (id_product) REFERENCES products(id_product)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
