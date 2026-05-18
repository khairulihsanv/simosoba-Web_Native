import mysql from 'mysql2/promise';

/**
 * src/lib/db.ts
 * ─────────────────────────────────────────────────────────────
 * Singleton MySQL connection pool for TiDB Cloud.
 * Uses SSL validation as required by TiDB Serverless.
 * Implements a generic query wrapper for strict TypeScript typing.
 * ─────────────────────────────────────────────────────────────
 */

// Declare a global cache to prevent database connection exhaustion
// during Next.js Hot Module Replacement (HMR) in development.
declare global {
  // eslint-disable-next-line no-var
  var _mysqlPool: mysql.Pool | undefined;
}

/**
 * Initializes a new connection pool to TiDB Cloud using credentials
 * strictly typed via process.env.
 */
function createPool(): mysql.Pool {
  if (!process.env.TIDB_HOST) throw new Error('FATAL: TIDB_HOST is missing.');
  if (!process.env.TIDB_USER) throw new Error('FATAL: TIDB_USER is missing.');
  if (!process.env.TIDB_PASSWORD) throw new Error('FATAL: TIDB_PASSWORD is missing.');

  return mysql.createPool({
    host: process.env.TIDB_HOST,
    port: parseInt(process.env.TIDB_PORT || '4000', 10),
    user: process.env.TIDB_USER,
    password: process.env.TIDB_PASSWORD,
    database: process.env.TIDB_DATABASE || 'antigravity',

    // SSL Configuration is absolutely critical for TiDB Cloud Serverless.
    ssl: {
      rejectUnauthorized: true,
    },

    // Pool limits tailored for serverless functions (short lifecycle)
    connectionLimit: 5,
    waitForConnections: true,
    queueLimit: 0,
    enableKeepAlive: true,
    keepAliveInitialDelay: 10000,
    timezone: '+00:00', // Ensures consistent Date fetching without local offset bias
  });
}

// Retrieve the active pool from global scope, or create a new one.
const pool: mysql.Pool = global._mysqlPool ?? createPool();

if (process.env.NODE_ENV !== 'production') {
  global._mysqlPool = pool;
}

export default pool;

/**
 * Generic query wrapper to cast rows to the expected TypeScript interface.
 * Ensures the codebase isn't polluted with `any` types.
 */
export async function query<T>(sql: string, params?: any[]): Promise<T> {
  const [rows] = await pool.execute(sql, params);
  return rows as T;
}
