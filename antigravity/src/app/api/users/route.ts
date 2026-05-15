import { NextRequest, NextResponse } from 'next/server';
import pool from '@/lib/db';
import { getSession, unauthorized, forbidden } from '@/lib/auth';
import type { User } from '@/types';

/**
 * src/app/api/users/route.ts
 * ─────────────────────────────────────────────────────────────
 * User Management for Superadmin only.
 * ─────────────────────────────────────────────────────────────
 */

export async function GET(request: NextRequest) {
  const session = getSession(request);
  if (!session) return unauthorized();
  if (session.role !== 'superadmin') return forbidden();

  try {
    const [rows] = await pool.execute('SELECT id, name, email, role, created_at FROM users ORDER BY created_at DESC');
    return NextResponse.json({ success: true, data: rows });
  } catch (error: any) {
    return NextResponse.json({ success: false, error: 'Failed to fetch users' }, { status: 500 });
  }
}

export async function DELETE(request: NextRequest) {
  const session = getSession(request);
  if (!session) return unauthorized();
  if (session.role !== 'superadmin') return forbidden();

  try {
    const { searchParams } = new URL(request.url);
    const id = searchParams.get('id');

    if (!id) return NextResponse.json({ success: false, error: 'User ID is required' }, { status: 400 });

    // Prevent self-deletion
    if (Number(id) === session.id) {
      return NextResponse.json({ success: false, error: 'You cannot delete your own account' }, { status: 400 });
    }

    await pool.execute('DELETE FROM users WHERE id = ?', [id]);
    return NextResponse.json({ success: true, message: 'User deleted successfully' });
  } catch (error: any) {
    return NextResponse.json({ success: false, error: 'Failed to delete user' }, { status: 500 });
  }
}
