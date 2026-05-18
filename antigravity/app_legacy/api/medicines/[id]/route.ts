/**
 * app/api/medicines/[id]/route.ts
 * ─────────────────────────────────────────────────────────────
 * GET    /api/medicines/:id  — Fetch single medicine
 * PATCH  /api/medicines/:id  — Update medicine fields
 * DELETE /api/medicines/:id  — Delete medicine (cascades logs)
 * ─────────────────────────────────────────────────────────────
 */

import { NextRequest, NextResponse } from 'next/server';
import { getSession, unauthorized } from '@/lib/auth';
import { query } from '@/lib/db';
import type { MedicineInput } from '@/types';

type Params = { params: { id: string } };

// ── GET ────────────────────────────────────────────────────────
export async function GET(request: NextRequest, { params }: Params) {
  const session = getSession(request);
  if (!session) return unauthorized();

  try {
    const rows = await query('SELECT * FROM medicines WHERE id = ? LIMIT 1', [params.id]);
    const med  = (rows as unknown[])[0];
    if (!med) return NextResponse.json({ error: 'Medicine not found.' }, { status: 404 });
    return NextResponse.json({ success: true, data: med });
  } catch (err) {
    console.error('[MEDICINE GET ERROR]', err);
    return NextResponse.json({ error: 'Failed to fetch medicine.' }, { status: 500 });
  }
}

// ── PATCH ──────────────────────────────────────────────────────
export async function PATCH(request: NextRequest, { params }: Params) {
  const session = getSession(request);
  if (!session) return unauthorized();

  try {
    const body = await request.json().catch(() => ({})) as Partial<MedicineInput>;

    await query(
      `UPDATE medicines SET
         name          = COALESCE(?, name),
         category      = COALESCE(?, category),
         unit          = COALESCE(?, unit),
         buy_price     = COALESCE(?, buy_price),
         sell_price    = COALESCE(?, sell_price),
         stock_current = COALESCE(?, stock_current),
         safety_stock  = COALESCE(?, safety_stock),
         expired_at    = COALESCE(?, expired_at),
         seasonal_tag  = COALESCE(?, seasonal_tag),
         notes         = COALESCE(?, notes)
       WHERE id = ?`,
      [
        body.name       ?? null,
        body.category   ?? null,
        body.unit        ?? null,
        body.buy_price  != null ? Number(body.buy_price)     : null,
        body.sell_price != null ? Number(body.sell_price)    : null,
        body.stock_current != null ? Number(body.stock_current) : null,
        body.safety_stock  != null ? Number(body.safety_stock)  : null,
        body.expired_at  ?? null,
        body.seasonal_tag?? null,
        body.notes       ?? null,
        params.id,
      ]
    );

    return NextResponse.json({ success: true, message: 'Medicine updated.' });

  } catch (err) {
    console.error('[MEDICINE PATCH ERROR]', err);
    return NextResponse.json({ error: 'Failed to update medicine.' }, { status: 500 });
  }
}

// ── DELETE ─────────────────────────────────────────────────────
export async function DELETE(request: NextRequest, { params }: Params) {
  const session = getSession(request);
  if (!session) return unauthorized();

  // Only superadmin can delete medicines
  if (session.role !== 'superadmin') {
    return NextResponse.json({ error: 'Only superadmin can delete medicines.' }, { status: 403 });
  }

  try {
    await query('DELETE FROM medicines WHERE id = ?', [params.id]);
    return NextResponse.json({ success: true, message: 'Medicine deleted.' });
  } catch (err) {
    console.error('[MEDICINE DELETE ERROR]', err);
    return NextResponse.json({ error: 'Failed to delete medicine.' }, { status: 500 });
  }
}
