'use client';
/**
 * app/(app)/dashboard/page.tsx
 * ─────────────────────────────────────────────────────────────
 * Main Dashboard View.
 * Displays KPI cards, charts (using Chart.js), recent activity,
 * and seasonal predictions.
 * ─────────────────────────────────────────────────────────────
 */

import { useState, useEffect } from 'react';
import {
  Chart as ChartJS,
  CategoryScale,
  LinearScale,
  PointElement,
  LineElement,
  BarElement,
  Title,
  Tooltip,
  Legend,
  Filler
} from 'chart.js';
import { Line, Bar } from 'react-chartjs-2';
import { Loader2, TrendingUp, AlertTriangle, Clock, ArrowDownRight, ArrowUpRight, PackageOpen } from 'lucide-react';
import type { DashboardStats, MonthlyChartData, InventoryLog, PredictionResult } from '@/types';
import { format } from 'date-fns';

// Register Chart.js components
ChartJS.register(
  CategoryScale, LinearScale, PointElement, LineElement, BarElement,
  Title, Tooltip, Legend, Filler
);

export default function DashboardPage() {
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);
  
  const [stats, setStats] = useState<DashboardStats | null>(null);
  const [monthlyChart, setMonthlyChart] = useState<MonthlyChartData[]>([]);
  const [stockChart, setStockChart] = useState<any[]>([]);
  const [activities, setActivities] = useState<InventoryLog[]>([]);
  const [predictions, setPredictions] = useState<PredictionResult[]>([]);

  useEffect(() => {
    fetch('/api/dashboard')
      .then(res => res.json())
      .then(json => {
        if (!json.success) throw new Error(json.error || 'Failed to load dashboard.');
        setStats(json.data.stats);
        setMonthlyChart(json.data.monthlyChart);
        setStockChart(json.data.stockChart);
        setActivities(json.data.activities);
        setPredictions(json.data.predictions);
      })
      .catch(err => setError(err.message))
      .finally(() => setLoading(false));
  }, []);

  if (loading) {
    return (
      <div className="h-[60vh] flex flex-col items-center justify-center gap-4 text-slate-400">
        <Loader2 className="w-8 h-8 animate-spin text-brand-500" />
        <p className="tracking-widest text-sm uppercase">Loading Dashboard</p>
      </div>
    );
  }

  if (error || !stats) {
    return (
      <div className="glass-card p-6 text-center text-red-400 border-red-500/20">
        <AlertTriangle className="w-8 h-8 mx-auto mb-2" />
        <p>{error || 'Failed to load data.'}</p>
      </div>
    );
  }

  // ── Chart Data ─────────────────────────────────────────────
  const lineChartData = {
    labels: monthlyChart.map(d => d.month),
    datasets: [
      {
        label: 'Net Profit',
        data: monthlyChart.map(d => d.profit),
        borderColor: '#10b981', // accent.green
        backgroundColor: 'rgba(16, 185, 129, 0.1)',
        borderWidth: 2,
        tension: 0.4,
        fill: true,
      },
      {
        label: 'Revenue',
        data: monthlyChart.map(d => d.revenue),
        borderColor: '#6366f1', // brand.500
        backgroundColor: 'transparent',
        borderWidth: 2,
        borderDash: [5, 5],
        tension: 0.4,
      }
    ],
  };

  const lineChartOptions = {
    responsive: true,
    maintainAspectRatio: false,
    scales: {
      y: { grid: { color: 'rgba(255,255,255,0.05)' }, ticks: { color: '#94a3b8' } },
      x: { grid: { display: false }, ticks: { color: '#94a3b8' } },
    },
    plugins: {
      legend: { labels: { color: '#f1f5f9' } }
    }
  };

  const barChartData = {
    labels: stockChart.map(d => d.name),
    datasets: [
      {
        label: 'Current Stock',
        data: stockChart.map(d => d.stock_current),
        backgroundColor: 'rgba(99, 102, 241, 0.8)',
        borderRadius: 4,
      },
      {
        label: 'Safety Stock',
        data: stockChart.map(d => d.safety_stock),
        backgroundColor: 'rgba(239, 68, 68, 0.5)',
        borderRadius: 4,
      }
    ]
  };

  const barChartOptions = {
    responsive: true,
    maintainAspectRatio: false,
    scales: {
      y: { grid: { color: 'rgba(255,255,255,0.05)' }, ticks: { color: '#94a3b8' } },
      x: { grid: { display: false }, ticks: { color: '#94a3b8', maxRotation: 45, minRotation: 45 } },
    },
    plugins: { legend: { labels: { color: '#f1f5f9' } } }
  };

  // ── Formatters ─────────────────────────────────────────────
  const formatIDR = (num: number) => new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(num);

  return (
    <div className="space-y-6">
      <div className="flex justify-between items-end">
        <div>
          <h1 className="text-2xl font-display font-bold gradient-text">Dashboard Overview</h1>
          <p className="text-slate-400 text-sm mt-1">Real-time pharmacy intelligence & insights.</p>
        </div>
      </div>

      {/* ── KPI Cards ────────────────────────────────────────── */}
      <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
        <div className="metric-card">
          <div className="flex justify-between items-start">
            <p className="text-sm font-medium text-slate-400">Monthly Profit</p>
            <div className="p-2 rounded-lg bg-emerald-500/10 text-emerald-400">
              <TrendingUp className="w-5 h-5" />
            </div>
          </div>
          <h3 className="text-2xl font-bold text-white mt-2">{formatIDR(stats.monthly_profit)}</h3>
          <p className="text-xs text-slate-500 mt-1">Net profit this month</p>
        </div>

        <div className="metric-card">
          <div className="flex justify-between items-start">
            <p className="text-sm font-medium text-slate-400">Low Stock Alerts</p>
            <div className="p-2 rounded-lg bg-red-500/10 text-red-400">
              <AlertTriangle className="w-5 h-5" />
            </div>
          </div>
          <h3 className="text-2xl font-bold text-white mt-2">{stats.low_stock_count}</h3>
          <p className="text-xs text-slate-500 mt-1">Items below safety threshold</p>
        </div>

        <div className="metric-card">
          <div className="flex justify-between items-start">
            <p className="text-sm font-medium text-slate-400">Expiring Soon</p>
            <div className="p-2 rounded-lg bg-amber-500/10 text-amber-400">
              <Clock className="w-5 h-5" />
            </div>
          </div>
          <h3 className="text-2xl font-bold text-white mt-2">{stats.expiring_soon}</h3>
          <p className="text-xs text-slate-500 mt-1">Items expiring within 90 days</p>
        </div>

        <div className="metric-card">
          <div className="flex justify-between items-start">
            <p className="text-sm font-medium text-slate-400">Total Medicines</p>
            <div className="p-2 rounded-lg bg-brand-500/10 text-brand-400">
              <PackageOpen className="w-5 h-5" />
            </div>
          </div>
          <h3 className="text-2xl font-bold text-white mt-2">{stats.total_medicines}</h3>
          <p className="text-xs text-slate-500 mt-1">Active items in inventory</p>
        </div>
      </div>

      {/* ── Charts ───────────────────────────────────────────── */}
      <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div className="glass-card p-5 h-[400px] flex flex-col">
          <h3 className="section-title mb-4">Financial Performance</h3>
          <div className="flex-1 w-full min-h-0">
            <Line data={lineChartData} options={lineChartOptions} />
          </div>
        </div>

        <div className="glass-card p-5 h-[400px] flex flex-col">
          <h3 className="section-title mb-4">Top Stock Turnover</h3>
          <div className="flex-1 w-full min-h-0">
            <Bar data={barChartData} options={barChartOptions} />
          </div>
        </div>
      </div>

      {/* ── Bottom Section: Predictions & Activity ───────────── */}
      <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        {/* Seasonal Predictions */}
        <div className="glass-card p-5 lg:col-span-1 flex flex-col">
          <h3 className="section-title mb-1 flex items-center gap-2">
            <span>🌧️</span> Seasonal Engine
          </h3>
          <p className="text-xs text-slate-400 mb-4 pb-4 border-b border-white/5">
            AI-driven stock recommendations
          </p>

          <div className="flex-1 overflow-y-auto pr-2 space-y-3 custom-scrollbar">
            {predictions.length === 0 ? (
              <p className="text-sm text-slate-500 text-center py-8">
                No immediate seasonal restocking needed.
              </p>
            ) : (
              predictions.map(pred => (
                <div key={pred.medicine_id} className="p-3 rounded-xl bg-white/5 border border-white/5">
                  <div className="flex justify-between items-start mb-2">
                    <h4 className="font-semibold text-sm">{pred.medicine_name}</h4>
                    <span className={`text-[10px] uppercase px-2 py-0.5 rounded-full font-bold ${
                      pred.urgency === 'high' ? 'bg-red-500/20 text-red-400 border border-red-500/20' :
                      pred.urgency === 'medium' ? 'bg-amber-500/20 text-amber-400 border border-amber-500/20' :
                      'bg-blue-500/20 text-blue-400 border border-blue-500/20'
                    }`}>
                      {pred.urgency}
                    </span>
                  </div>
                  <p className="text-xs text-slate-400 mb-2 leading-relaxed">{pred.reason}</p>
                  <div className="flex items-center justify-between mt-2 pt-2 border-t border-white/5">
                    <span className="text-xs text-slate-500">Rec. Qty:</span>
                    <span className="text-sm font-bold text-brand-400">+{pred.recommended_qty}</span>
                  </div>
                </div>
              ))
            )}
          </div>
        </div>

        {/* Recent Activity */}
        <div className="glass-card p-5 lg:col-span-2 flex flex-col">
          <div className="flex items-center justify-between mb-4">
            <h3 className="section-title">Recent Activity</h3>
            <a href="/transactions" className="text-xs text-brand-400 hover:text-brand-300 font-medium">
              View All &rarr;
            </a>
          </div>
          
          <div className="overflow-x-auto">
            <table className="data-table">
              <thead>
                <tr>
                  <th>Time</th>
                  <th>Type</th>
                  <th>Medicine</th>
                  <th>Qty</th>
                  <th>Total Price</th>
                  <th>User</th>
                </tr>
              </thead>
              <tbody>
                {activities.length === 0 ? (
                  <tr>
                    <td colSpan={6} className="text-center py-8 text-slate-500">No recent transactions.</td>
                  </tr>
                ) : (
                  activities.map(act => (
                    <tr key={act.id}>
                      <td className="whitespace-nowrap text-xs">
                        {format(new Date(act.created_at), 'dd MMM, HH:mm')}
                      </td>
                      <td>
                        <span className={`inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider ${
                          act.type === 'in' ? 'bg-emerald-500/10 text-emerald-400' : 'bg-brand-500/10 text-brand-400'
                        }`}>
                          {act.type === 'in' ? <ArrowDownRight className="w-3 h-3" /> : <ArrowUpRight className="w-3 h-3" />}
                          {act.type}
                        </span>
                      </td>
                      <td className="font-medium text-slate-200">{act.medicine_name}</td>
                      <td>{act.quantity}</td>
                      <td className="font-mono text-xs">{formatIDR(act.total_price)}</td>
                      <td className="text-slate-400">{act.user_name}</td>
                    </tr>
                  ))
                )}
              </tbody>
            </table>
          </div>
        </div>

      </div>
    </div>
  );
}
