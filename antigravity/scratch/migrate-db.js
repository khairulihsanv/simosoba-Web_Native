const mysql = require('mysql2/promise');
const path = require('path');
require('dotenv').config({ path: path.join(__dirname, '../.env.local') });

async function connectWithRetry(retries = 5, delay = 2000) {
  for (let i = 0; i < retries; i++) {
    try {
      console.log(`Connecting to TiDB (Attempt ${i + 1}/${retries})...`);
      const conn = await mysql.createConnection({
        host: process.env.TIDB_HOST,
        port: parseInt(process.env.TIDB_PORT || '4000', 10),
        user: process.env.TIDB_USER,
        password: process.env.TIDB_PASSWORD,
        database: process.env.TIDB_DATABASE || 'simosoba2',
        ssl: {
          rejectUnauthorized: false,
        }
      });
      return conn;
    } catch (err) {
      console.error(`Connection attempt ${i + 1} failed:`, err.message);
      if (i < retries - 1) {
        await new Promise(res => setTimeout(res, delay));
      } else {
        throw err;
      }
    }
  }
}

async function migrate() {
  let conn;
  try {
    conn = await connectWithRetry();
    console.log('Starting migration...');

    // 1. Alter users table to support Next.js fields
    console.log('Checking / altering users table...');
    const [cols] = await conn.query('DESCRIBE users');
    const hasEmail = cols.some(c => c.Field === 'email');
    const hasPasswordHash = cols.some(c => c.Field === 'password_hash');
    const hasName = cols.some(c => c.Field === 'name');

    if (!hasEmail) {
      console.log('Adding email column...');
      await conn.query('ALTER TABLE users ADD COLUMN email VARCHAR(180) NULL');
    }
    if (!hasPasswordHash) {
      console.log('Adding password_hash column...');
      await conn.query('ALTER TABLE users ADD COLUMN password_hash VARCHAR(255) NULL');
    }
    if (!hasName) {
      console.log('Adding name column...');
      await conn.query('ALTER TABLE users ADD COLUMN name VARCHAR(120) NULL');
    }

    // 2. Populate columns for existing users
    console.log('Syncing existing users data...');
    await conn.query('UPDATE users SET name = nama WHERE name IS NULL AND nama IS NOT NULL');
    await conn.query('UPDATE users SET email = CONCAT(username, "@simosoba.com") WHERE email IS NULL AND username IS NOT NULL');
    await conn.query('UPDATE users SET password_hash = password WHERE password_hash IS NULL AND password IS NOT NULL');

    // 3. Create medicines table if not exists
    console.log('Creating medicines table...');
    await conn.query(`
      CREATE TABLE IF NOT EXISTS medicines (
        id            BIGINT          NOT NULL AUTO_INCREMENT,
        name          VARCHAR(200)    NOT NULL,
        category      VARCHAR(100)    NOT NULL DEFAULT 'General',
        unit          VARCHAR(30)     NOT NULL DEFAULT 'pcs',
        buy_price     DECIMAL(15, 2)  NOT NULL DEFAULT 0.00,
        sell_price    DECIMAL(15, 2)  NOT NULL DEFAULT 0.00,
        stock_current INT             NOT NULL DEFAULT 0,
        safety_stock  INT             NOT NULL DEFAULT 10,
        expired_at    DATE            NULL,
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
    `);

    // 4. Create inventory_logs table if not exists
    console.log('Creating inventory_logs table...');
    await conn.query(`
      CREATE TABLE IF NOT EXISTS inventory_logs (
        id          BIGINT          NOT NULL AUTO_INCREMENT,
        medicine_id BIGINT          NOT NULL,
        user_id     BIGINT          NOT NULL,
        type        ENUM('in', 'out') NOT NULL,
        quantity    INT             NOT NULL,
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
    `);

    // 5. Seed default superadmin if not exists
    console.log('Seeding default superadmin...');
    await conn.query(`
      INSERT IGNORE INTO users (name, email, password_hash, role, username, password) VALUES
      (
        'Super Admin',
        'admin@antigravity.app',
        '$2a$12$LQ2ygjB0M6JZ2jtBSFKR9.D7vNsL5tQfKl.9rMKsG5FXQLe3jBh3K',
        'superadmin',
        'admin',
        '$2y$10$NW46msmWRPNgdUKl/mHWl.4XPwWdLa/W.JlOzNaShebM2QwGhBy8O'
      )
    `);

    // 6. Migrate medicines data from old 'obat' table if 'medicines' is empty
    const [medsCount] = await conn.query('SELECT COUNT(*) as count FROM medicines');
    if (medsCount[0].count === 0) {
      console.log('Migrating data from obat to medicines...');
      const [oldObat] = await conn.query('SELECT * FROM obat');
      for (const o of oldObat) {
        const buyPrice = Math.round(Number(o.harga || 0) * 0.75); // Estimate 25% profit margin
        const sellPrice = Number(o.harga || 0);
        const expiredAt = o.exp_date ? new Date(o.exp_date).toISOString().split('T')[0] : null;

        await conn.query(
          `INSERT INTO medicines (id, name, category, unit, buy_price, sell_price, stock_current, safety_stock, expired_at)
           VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)`,
          [o.id, o.nama, o.kategori || 'General', o.satuan || 'pcs', buyPrice, sellPrice, o.stok || 0, o.stok_min || 10, expiredAt]
        );
      }
      console.log(`Successfully migrated ${oldObat.length} medicines!`);
    }

    console.log('Migration completed successfully!');
  } catch (err) {
    console.error('Migration failed:', err);
  } finally {
    if (conn) await conn.end();
  }
}

migrate();
