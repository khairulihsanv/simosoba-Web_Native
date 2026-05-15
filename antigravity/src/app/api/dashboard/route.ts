import { NextRequest, NextResponse } from 'next/server';
import pool from '@/lib/db';
import { getSession, unauthorized } from '@/lib/auth';
import { getSeasonalInsight } from '@/lib/predictions';
import type { DashboardStats, Medicine } from '@/types';

/**
 * src/app/api/dashboard/route.ts
 * ─────────────────────────────────────────────────────────────
 * Aggregates all KPI metrics, charts, and predictions.
 * ─────────────────────────────────────────────────────────────
 */

export async function GET(request: NextRequest) {
  const session = getSession(request);
  if (!session) return unauthorized();

  try {
    // 1. Basic Stats
    const [medCountRows] = await pool.execute('SELECT COUNT(*) as count FROM medicines');
    const [lowStockRows] = await pool.execute('SELECT COUNT(*) as count FROM medicines WHERE stock_current <= safety_stock');
    
    // 2. Expiry Stats (within 90 days)
    const [expiringRows] = await pool.execute(
      'SELECT COUNT(*) as count FROM medicines WHERE expired_at IS NOT NULL AND expired_at <= DATE_ADD(NOW(), INTERVAL 90 DAY) AND expired_at > NOW()'
    );
    const [expiredRows] = await pool.execute('SELECT COUNT(*) as count FROM medicines WHERE expired_at <= NOW()');

    // 3. Financials (Current Month)
    const [finRows] = await pool.execute(`
      SELECT 
        SUM(CASE WHEN type = 'out' THEN total_price ELSE 0 END) as revenue,
        SUM(CASE WHEN type = 'out' THEN (quantity * buy_price) ELSE 0 END) as cost
      FROM inventory_logs il
      JOIN medicines m ON il.medicine_id = m.id
      WHERE MONTH(il.created_at) = MONTH(CURRENT_DATE()) AND YEAR(il.created_at) = YEAR(CURRENT_DATE())
    `);

    const financials = (finRows as { revenue: number | string, cost: number | string }[])[0];
    const revenue = Number(financials?.revenue || 0);
    const cost = Number(financials?.cost || 0);

    const stats: DashboardStats = {
      total_medicines: (medCountRows as any[])[0].count,
      low_stock_count: (lowStockRows as any[])[0].count,
      expiring_soon: (expiringRows as any[])[0].count,
      expired_count: (expiredRows as any[])[0].count,
      monthly_revenue: revenue,
      monthly_cost: cost,
      monthly_profit: revenue - cost
    };

    // 4. Monthly Chart Data (Last 6 Months)
    const [chartRows] = await pool.execute(`
      SELECT 
        DATE_FORMAT(il.created_at, '%b') as month,
        SUM(CASE WHEN type = 'out' THEN total_price ELSE 0 END) as revenue,
        SUM(CASE WHEN type = 'out' THEN (quantity * buy_price) ELSE 0 END) as cost
      FROM inventory_logs il
      JOIN medicines m ON il.medicine_id = m.id
      WHERE il.created_at >= DATE_SUB(CURRENT_DATE(), INTERVAL 6 MONTH)
      GROUP BY YEAR(il.created_at), MONTH(il.created_at), month
      ORDER BY YEAR(il.created_at) ASC, MONTH(il.created_at) ASC
    `);

    const rawChartData = chartRows as { month: string, revenue: number | string, cost: number | string }[];
    
    // Fill missing months to ensure a continuous 6-month view
    const monthlyChart = [];
    for (let i = 5; i >= 0; i--) {
      const d = new Date();
      d.setMonth(d.getMonth() - i);
      const monthLabel = d.toLocaleString('default', { month: 'short' });
      
      const existing = rawChartData.find(r => r.month === monthLabel);
      monthlyChart.push({
        month: monthLabel,
        revenue: Number(existing?.revenue || 0),
        cost: Number(existing?.cost || 0),
        profit: Number(existing?.revenue || 0) - Number(existing?.cost || 0)
      });
    }

    // 5. Recent Activities
    const [activityRows] = await pool.execute(`
      SELECT il.*, m.name as medicine_name, u.name as user_name
      FROM inventory_logs il
      JOIN medicines m ON il.medicine_id = m.id
      JOIN users u ON il.user_id = u.id
      ORDER BY il.created_at DESC
      LIMIT 10
    `);

    // 6. Seasonal Predictions
    const [allMeds] = await pool.execute('SELECT * FROM medicines');
    const predictions = getSeasonalInsight(allMeds as Medicine[]);

    return NextResponse.json({
      success: true,
      data: {
        stats,
        monthlyChart,
        activities: activityRows,
        predictions: predictions.slice(0, 5) // Top 5
      }
    });
  } catch (error: any) {
    console.error('Dashboard API Error:', error);
    return NextResponse.json({ success: false, error: 'Failed to aggregate dashboard data' }, { status: 500 });
  }
}
