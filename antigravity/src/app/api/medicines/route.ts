import { NextRequest, NextResponse } from 'next/server';
import pool from '@/lib/db';
import { getSession, unauthorized } from '@/lib/auth';
import type { Medicine } from '@/types';

/**
 * src/app/api/medicines/route.ts
 * ─────────────────────────────────────────────────────────────
 * CRUD for Medicines Catalog.
 * ─────────────────────────────────────────────────────────────
 */

export async function GET(request: NextRequest) {
  const session = getSession(request);
  if (!session) return unauthorized();

  try {
    const [rows] = await pool.execute('SELECT * FROM medicines ORDER BY name ASC');
    return NextResponse.json({ success: true, data: rows });
  } catch (error: any) {
    return NextResponse.json({ success: false, error: 'Failed to fetch medicines' }, { status: 500 });
  }
}

export async function POST(request: NextRequest) {
  const session = getSession(request);
  if (!session) return unauthorized();

  try {
    const body = await request.json();
    const { name, category, unit, buy_price, sell_price, stock_current, safety_stock, expired_at, seasonal_tag, notes } = body;

    const [result] = await pool.execute(
      `INSERT INTO medicines (name, category, unit, buy_price, sell_price, stock_current, safety_stock, expired_at, seasonal_tag, notes)
       VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)`,
      [name, category, unit, buy_price, sell_price, stock_current, safety_stock, expired_at, seasonal_tag, notes]
    );

    return NextResponse.json({ success: true, data: { id: (result as any).insertId } });
  } catch (error: any) {
    return NextResponse.json({ success: false, error: 'Failed to create medicine' }, { status: 500 });
  }
}
