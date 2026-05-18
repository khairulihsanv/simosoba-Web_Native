const mysql = require('mysql2/promise');
const path = require('path');
require('dotenv').config({ path: path.join(__dirname, '../.env.local') });

async function check() {
  const pool = mysql.createPool({
    host: process.env.TIDB_HOST,
    port: parseInt(process.env.TIDB_PORT || '4000', 10),
    user: process.env.TIDB_USER,
    password: process.env.TIDB_PASSWORD,
    database: process.env.TIDB_DATABASE || 'simosoba2',
    ssl: {
      rejectUnauthorized: false,
    }
  });

  try {
    const [users] = await pool.query('SELECT * FROM users');
    console.log('Users in database:', users);

    const [obat] = await pool.query('SELECT * FROM obat LIMIT 5');
    console.log('Sample obat:', obat);
  } catch (err) {
    console.error('Error:', err);
  } finally {
    await pool.end();
  }
}

check();
