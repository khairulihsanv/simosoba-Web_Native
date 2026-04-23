-- Jalankan query ini di phpMyAdmin untuk tambah kolom last_login
-- Jika sudah ada, skip saja
ALTER TABLE users ADD COLUMN IF NOT EXISTS last_login DATETIME DEFAULT NULL;
