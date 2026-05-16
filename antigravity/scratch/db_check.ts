import mysql from 'mysql2/promise';
import dotenv from 'dotenv';
import path from 'path';

dotenv.config({ path: path.resolve(process.cwd(), '.env.local') });

async function checkDb() {
  const connection = await mysql.createConnection({
    host: process.env.TIDB_HOST,
    port: parseInt(process.env.TIDB_PORT || '4000'),
    user: process.env.TIDB_USER,
    password: process.env.TIDB_PASSWORD,
    database: process.env.TIDB_DATABASE,
    ssl: { rejectUnauthorized: true }
  });

  try {
    const [tables] = await connection.execute('SHOW TABLES');
    console.log('Tables in database:', tables);

    for (const tableRow of (tables as any[])) {
      const tableName = Object.values(tableRow)[0] as string;
      const [columns] = await connection.execute(`DESCRIBE ${tableName}`);
      console.log(`Columns in ${tableName}:`, columns);
    }
  } catch (error) {
    console.error('Error checking DB:', error);
  } finally {
    await connection.end();
  }
}

checkDb();
