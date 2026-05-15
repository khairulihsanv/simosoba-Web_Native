/**
 * app/api/users/[id]/route.ts
 * ─────────────────────────────────────────────────────────────
 * PATCH  /api/users/:id  — Update role or name (superadmin)
 * DELETE /api/users/:id  — Delete user (superadmin, can't self-delete)
 * ─────────────────────────────────────────────────────────────
 */

import { NextRequest, NextResponse } from 'next/server';
import bcrypt from 'bcryptjs';
import { getSession, unauthorized, forbidden } from '@/lib/auth';
import { query } from '@/lib/db';

type Params = { params: { id: string } };

export async function PATCH(request: NextRequest, { params }: Params) {
  const session = getSession(request);
  if (!session) return unauthorized();
  if (session.role !== 'superadmin') return forbidden();

  try {
    const body = await request.json().catch(() => ({})) as {
      name?: string; role?: string; password?: string;
    };

    // Build dynamic update
    const fields: string[]  = [];
    const values: unknown[] = [];

    if (body.name?.trim())  { fields.push('name = ?');          values.push(body.name.trim()); }
    if (body.role)          { fields.push('role = ?');          values.push(body.role === 'superadmin' ? 'superadmin' : 'admin'); }
    if (body.password)      {
      const hash = await bcrypt.hash(body.password, 12);
      fields.push('password_hash = ?');
      values.push(hash);
    }

    if (fields.length === 0) {
      return NextResponse.json({ error: 'No fields to update.' }, { status: 400 });
    }

    values.push(params.id);
    await query(`UPDATE users SET ${fields.join(', ')} WHERE id = ?`, values);

    return NextResponse.json({ success: true, message: 'User updated.' });
  } catch (err) {
    console.error('[USER PATCH ERROR]', err);
    return NextResponse.json({ error: 'Failed to update user.' }, { status: 500 });
  }
}

export async function DELETE(request: NextRequest, { params }: Params) {
  const session = getSession(request);
  if (!session) return unauthorized();
  if (session.role !== 'superadmin') return forbidden();

  // Prevent self-deletion
  if (String(session.id) === params.id) {
    return NextResponse.json({ error: 'You cannot delete your own account.' }, { status: 400 });
  }

  try {
    await query('DELETE FROM users WHERE id = ?', [params.id]);
    return NextResponse.json({ success: true, message: 'User deleted.' });
  } catch (err) {
    console.error('[USER DELETE ERROR]', err);
    return NextResponse.json({ error: 'Failed to delete user.' }, { status: 500 });
  }
}
