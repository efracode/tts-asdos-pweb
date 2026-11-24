-- Migrasi tambahan untuk database kasirrestoran yang sudah ada:
-- Menambahkan kolom gambar pada tabel menu secara aman (idempoten di MySQL 8).

USE kasirrestoran;

SET @col_exists := (
    SELECT COUNT(*)
    FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'menu'
      AND COLUMN_NAME = 'gambar'
);

SET @sql_stmt := IF(
    @col_exists = 0,
    'ALTER TABLE menu ADD COLUMN gambar VARCHAR(255) DEFAULT NULL AFTER stok',
    'SELECT ''Kolom gambar sudah tersedia pada tabel menu.'' AS status_migrasi'
);

PREPARE stmt FROM @sql_stmt;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;
