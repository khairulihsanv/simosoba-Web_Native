import mysql from 'mysql2/promise';

// Create a connection pool to TiDB Cloud (MySQL compatible)
const pool = mysql.createPool({
  host: process.env.TIDB_HOST || 'localhost',
  port: Number(process.env.TIDB_PORT) || 4000,
  user: process.env.TIDB_USER || 'root',
  password: process.env.TIDB_PASSWORD || '',
  database: process.env.TIDB_DATABASE || 'test',
  ssl: {
    // TiDB Cloud requires SSL
    rejectUnauthorized: process.env.NODE_ENV === 'production' ? true : false,
  },
  waitForConnections: true,
  connectionLimit: 10,
  queueLimit: 0,
});

export default pool;