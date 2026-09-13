-- 1. suppliers 테이블 생성
CREATE TABLE IF NOT EXISTS suppliers (
  id INT AUTO_INCREMENT PRIMARY KEY,
  vendor_user_id INT NOT NULL COMMENT '소유자(벤더) ID',
  name VARCHAR(50) NOT NULL,
  factory_name VARCHAR(100),
  status VARCHAR(20) DEFAULT 'none' COMMENT 'excel, manual, none',
  color VARCHAR(20) DEFAULT '#94a3b8',
  excel_file VARCHAR(255) DEFAULT NULL,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 2. vendor_prices_manual 테이블 생성
CREATE TABLE IF NOT EXISTS vendor_prices_manual (
  id INT AUTO_INCREMENT PRIMARY KEY,
  supplier_id INT NOT NULL,
  item_code VARCHAR(50) NOT NULL,
  unit_price DECIMAL(12,2) DEFAULT 0,
  updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY unique_supplier_item (supplier_id, item_code)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 3. vendor_pricing_rules 테이블에 supplier_id 컬럼 추가
-- 주의: 이미 supplier_id가 있다면 무시하셔도 됩니다.
ALTER TABLE vendor_pricing_rules ADD COLUMN supplier_id INT DEFAULT 1 AFTER vendor_id;

-- 4. 기존 세화산업 엑셀 데이터를 공급사로 자동 이관 (선택)
-- INSERT INTO suppliers (vendor_user_id, name, factory_name, status, color) SELECT DISTINCT vendor_id, '세화산업', '세화산업(주) 화성공장', 'excel', '#fde047' FROM vendor_pricing_rules;
-- UPDATE vendor_pricing_rules r JOIN suppliers s ON r.vendor_id = s.vendor_user_id SET r.supplier_id = s.id;
