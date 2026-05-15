'use client';

import { useState, useEffect } from 'react';
import {
  Chart as ChartJS, CategoryScale, LinearScale, PointElement, LineElement, Title, Tooltip, Legend, Filler
} from 'chart.js';
import { Line } from 'react-chartjs-2';
import { Loader2, TrendingUp, AlertTriangle, Clock, ArrowDownRight, ArrowUpRight, PackageOpen } from 'lucide-react';
import type { DashboardStats, MonthlyChartData, InventoryLog, PredictionResult } from '@/types';
import { format } from 'date-fns';

/**
 * src/app/(app)/dashboard/page.tsx
 * ─────────────────────────────────────────────────────────────
 * Primary Dashboard Interface
 * Integrates Chart.js for data visualization.
 * Highlights "Expired Soon" and "Low Stock" alerts prominently.
 * ─────────────────────────────────────────────────────────────
 */

ChartJS.register(CategoryScale, LinearScale, PointElement, LineElement, Title, Tooltip, Legend, Filler);

export default function DashboardPage() {
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);
  
  const [stats, setStats] = useState<DashboardStats | null>(null);
  const [monthlyChart, setMonthlyChart] = useState<MonthlyChartData[]>([]);
  const [activities, setActivities] = useState<InventoryLog[]>([]);
  const [predictions, setPredictions] = useState<PredictionResult[]>([]);

  useEffect(() => {
    fetch('/api/dashboard')
      .then(res => res.json())
      .then(json => {
        if (!json.success) throw new Error(json.error);
        setStats(json.data.stats);
        setMonthlyChart(json.data.monthlyChart);
        setActivities(json.data.activities);
        setPredictions(json.data.predictions);
      })
      .catch((err: Error) => setError(err.message))
      .finally(() => setLoading(false));
  }, []);

  if (loading) {
    return (
      <div className="h-[60vh] flex flex-col items-center justify-center gap-4 text-slate-400">
        <Loader2 className="w-8 h-8 animate-spin text-brand-500" />
        <p className="tracking-widest text-sm uppercase font-bold">Initializing Intelligence...</p>
      </div>
    );
  }

  if (error || !stats) {
    return (
      <div className="glass-card p-6 text-center text-red-400 border-red-500/20 bg-red-500/5">
        <AlertTriangle className="w-10 h-10 mx-auto mb-3" />
        <p className="font-semibold">{error || 'Failed to sync data.'}</p>
      </div>
    );
  }

  const formatIDR = (num: number) => new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(num);

  const lineChartData = {
    labels: monthlyChart.map(d => d.month),
    datasets: [
      {
        label: 'Net Profit',
        data: monthlyChart.map(d => d.profit),
        borderColor: '#10b981', backgroundColor: 'rgba(16, 185, 129, 0.1)',
        borderWidth: 2, tension: 0.4, fill: true,
      },
      {
        label: 'Revenue',
        data: monthlyChart.map(d => d.revenue),
        borderColor: '#6366f1', backgroundColor: 'transparent',
        borderWidth: 2, borderDash: [5, 5], tension: 0.4,
      }
    ],
  };

  return (
    <div className="space-y-6">
      <div>
        <h1 className="text-2xl font-bold gradient-text">Command Center</h1>
        <p className="text-slate-400 text-sm mt-1">Real-time pharmacy analytics & stock health.</p>
      </div>

      {/* ── PRIORITY ALERTS ── */}
      {(stats.expiring_soon > 0 || stats.low_stock_count > 0) && (
        <div className="flex flex-col md:flex-row gap-4 mb-6">
          {stats.expiring_soon > 0 && (
            <div className="flex-1 glass-card p-4 border-amber-500/30 bg-amber-500/10 flex items-start gap-4">
              <div className="p-3 bg-amber-500/20 rounded-xl text-amber-400 shrink-0">
                <Clock className="w-6 h-6" />
              </div>
              <div>
                <h3 className="text-amber-400 font-bold text-lg">{stats.expiring_soon} Items Expiring Soon</h3>
                <p className="text-amber-400/80 text-xs mt-1">Review items expiring within 90 days to prevent stock waste.</p>
              </div>
            </div>
          )}
          {stats.low_stock_count > 0 && (
            <div className="flex-1 glass-card p-4 border-red-500/30 bg-red-500/10 flex items-start gap-4">
              <div className="p-3 bg-red-500/20 rounded-xl text-red-400 shrink-0">
                <AlertTriangle className="w-6 h-6" />
              </div>
              <div>
                <h3 className="text-red-400 font-bold text-lg">{stats.low_stock_count} Low Stock Warnings</h3>
                <p className="text-red-400/80 text-xs mt-1">Items have fallen below their configured safety stock threshold.</p>
              </div>
            </div>
          )}
        </div>
      )}

      {/* ── KPI METRICS ── */}
      <div className="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <div className="glass-card p-5">
          <p className="text-xs font-bold text-slate-400 uppercase">Monthly Profit</p>
          <h3 className="text-xl md:text-2xl font-bold text-white mt-2">{formatIDR(stats.monthly_profit)}</h3>
          <TrendingUp className="w-5 h-5 text-emerald-400 absolute top-5 right-5" />
        </div>
        <div className="glass-card p-5">
          <p className="text-xs font-bold text-slate-400 uppercase">Total Items</p>
          <h3 className="text-xl md:text-2xl font-bold text-white mt-2">{stats.total_medicines}</h3>
          <PackageOpen className="w-5 h-5 text-brand-400 absolute top-5 right-5" />
        </div>
      </div>

      {/* ── FINANCIAL CHART ── */}
      <div className="glass-card p-5 h-[350px] flex flex-col">
        <h3 className="text-sm font-bold text-slate-200 mb-4 uppercase tracking-wider">Financial Performance</h3>
        <div className="flex-1 w-full min-h-0">
          <Line 
            data={lineChartData} 
            options={{ 
              responsive: true, maintainAspectRatio: false,
              scales: { y: { grid: { color: 'rgba(255,255,255,0.05)' }, ticks: { color: '#94a3b8' } }, x: { grid: { display: false }, ticks: { color: '#94a3b8' } } },
              plugins: { legend: { labels: { color: '#f1f5f9' } } }
            }} 
          />
        </div>
      </div>

      {/* ── SEASONAL ENGINE & ACTIVITY ── */}
      <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div className="glass-card p-5 lg:col-span-1 flex flex-col max-h-[400px]">
          <h3 className="text-sm font-bold text-slate-200 mb-4 uppercase tracking-wider flex items-center gap-2">
            <span>🌧️</span> Seasonal Advice
          </h3>
          <div className="flex-1 overflow-y-auto pr-2 space-y-3 custom-scrollbar">
            {predictions.length === 0 ? (
              <p className="text-sm text-slate-500 text-center py-8">No current seasonal actions required.</p>
            ) : (
              predictions.map(pred => (
                <div key={pred.medicine_id} className="p-3 rounded-xl bg-black/20 border border-white/5">
                  <div className="flex justify-between items-start mb-1">
                    <h4 className="font-bold text-sm text-slate-200">{pred.medicine_name}</h4>
                    <span className={`text-[10px] uppercase font-bold px-2 py-0.5 rounded-md ${
                      pred.urgency === 'high' ? 'bg-red-500/20 text-red-400' : 'bg-amber-500/20 text-amber-400'
                    }`}>{pred.urgency}</span>
                  </div>
                  <p className="text-xs text-slate-400 leading-relaxed">{pred.reason}</p>
                </div>
              ))
            )}
          </div>
        </div>

        <div className="glass-card p-5 lg:col-span-2">
          <h3 className="text-sm font-bold text-slate-200 mb-4 uppercase tracking-wider">Recent Transactions</h3>
          <div className="overflow-x-auto">
            <table className="data-table">
              <thead><tr><th>Time</th><th>Type</th><th>Item</th><th>Qty</th><th>Value</th></tr></thead>
              <tbody>
                {activities.map(act => (
                  <tr key={act.id}>
                    <td className="whitespace-nowrap text-xs">{format(new Date(act.created_at), 'dd MMM, HH:mm')}</td>
                    <td>
                      <span className={`inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10px] font-bold uppercase ${
                        act.type === 'in' ? 'bg-emerald-500/10 text-emerald-400' : 'bg-brand-500/10 text-brand-400'
                      }`}>
                        {act.type === 'in' ? <ArrowDownRight className="w-3 h-3" /> : <ArrowUpRight className="w-3 h-3" />}
                        {act.type}
                      </span>
                    </td>
                    <td className="font-bold">{act.medicine_name}</td>
                    <td className="font-mono">{act.quantity}</td>
                    <td className="font-mono text-emerald-400">{formatIDR(act.total_price)}</td>
                  </tr>
                ))}
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  );
}
