import { NextRequest, NextResponse } from 'next/server';
import pool from '@/lib/db';
import { getSession, unauthorized } from '@/lib/auth';

/**
 * src/app/api/transactions/route.ts
 * ─────────────────────────────────────────────────────────────
 * Handles inventory logs (IN/OUT).
 * Automatically updates medicine stock levels in a transaction.
 * ─────────────────────────────────────────────────────────────
 */

export async function GET(request: NextRequest) {
  const session = getSession(request);
  if (!session) return unauthorized();

  try {
    const [rows] = await pool.execute(`
      SELECT il.*, m.name as medicine_name, u.name as user_name
      FROM inventory_logs il
      JOIN medicines m ON il.medicine_id = m.id
      JOIN users u ON il.user_id = u.id
      ORDER BY il.created_at DESC
    `);
    return NextResponse.json({ success: true, data: rows });
  } catch (error: any) {
    return NextResponse.json({ success: false, error: 'Failed to fetch transactions' }, { status: 500 });
  }
}

export async function POST(request: NextRequest) {
  const session = getSession(request);
  if (!session) return unauthorized();

  const conn = await pool.getConnection();
  try {
    const body = await request.json();
    const { medicine_id, type, quantity, notes } = body;

    await conn.beginTransaction();

    // 1. Get current medicine data
    const [medRows] = await conn.execute('SELECT stock_current, sell_price, buy_price FROM medicines WHERE id = ?', [medicine_id]);
    const medicine = (medRows as any[])[0];

    if (!medicine) throw new Error('Medicine not found');
    
    // Prevent negative stock for 'out' transactions
    if (type === 'out' && medicine.stock_current < quantity) {
      throw new Error(`Insufficient stock. Current: ${medicine.stock_current}, Requested: ${quantity}`);
    }

    const unitPrice = type === 'in' ? medicine.buy_price : medicine.sell_price;
    const totalPrice = unitPrice * quantity;

    // 2. Insert Log
    await conn.execute(
      `INSERT INTO inventory_logs (medicine_id, user_id, type, quantity, unit_price, total_price, notes)
       VALUES (?, ?, ?, ?, ?, ?, ?)`,
      [medicine_id, session.id, type, quantity, unitPrice, totalPrice, notes]
    );

    // 3. Update Stock
    const stockChange = type === 'in' ? quantity : -quantity;
    await conn.execute(
      'UPDATE medicines SET stock_current = stock_current + ? WHERE id = ?',
      [stockChange, medicine_id]
    );

    await conn.commit();
    return NextResponse.json({ success: true, message: 'Transaction recorded successfully' });
  } catch (error: any) {
    await conn.rollback();
    return NextResponse.json({ success: false, error: error.message || 'Transaction failed' }, { status: 500 });
  } finally {
    conn.release();
  }
}
