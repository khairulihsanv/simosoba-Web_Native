'use client';
/**
 * app/(app)/transactions/page.tsx
 * ─────────────────────────────────────────────────────────────
 * Stock In/Out Transactions
 * Records movements and displays a paginated audit log.
 * ─────────────────────────────────────────────────────────────
 */

import { useState, useEffect, FormEvent } from 'react';
import { ArrowDownRight, ArrowUpRight, Search, Plus, Loader2, AlertCircle } from 'lucide-react';
import { format } from 'date-fns';
import type { InventoryLog, Medicine } from '@/types';

export default function TransactionsPage() {
  const [logs, setLogs] = useState<InventoryLog[]>([]);
  const [medicines, setMedicines] = useState<Medicine[]>([]);
  const [loading, setLoading] = useState(true);
  
  // Pagination
  const [page, setPage] = useState(1);
  const [totalPages, setTotalPages] = useState(1);

  // New Transaction Form State
  const [isModalOpen, setIsModalOpen] = useState(false);
  const [isSubmitting, setIsSubmitting] = useState(false);
  const [errorMsg, setErrorMsg] = useState('');
  
  const [form, setForm] = useState({
    medicine_id: '',
    type: 'out', // default to dispensing
    quantity: 1,
    notes: ''
  });

  useEffect(() => {
    loadTransactions(page);
    loadMedicines(); // for the dropdown
  }, [page]);

  const loadTransactions = async (p: number) => {
    setLoading(true);
    try {
      const res = await fetch(`/api/transactions?page=${p}&limit=20`);
      const json = await res.json();
      if (json.success) {
        setLogs(json.data);
        setTotalPages(json.meta.pages);
      }
    } catch (err) {
      console.error(err);
    } finally {
      setLoading(false);
    }
  };

  const loadMedicines = async () => {
    try {
      const res = await fetch('/api/medicines');
      const json = await res.json();
      if (json.success) setMedicines(json.data);
    } catch (err) {
      console.error(err);
    }
  };

  const handleSubmit = async (e: FormEvent) => {
    e.preventDefault();
    setIsSubmitting(true);
    setErrorMsg('');

    try {
      const res = await fetch('/api/transactions', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          medicine_id: Number(form.medicine_id),
          type: form.type,
          quantity: Number(form.quantity),
          notes: form.notes
        }),
      });
      const json = await res.json();
      
      if (!res.ok) throw new Error(json.error || 'Failed to record transaction');
      
      await loadTransactions(1);
      setPage(1);
      setIsModalOpen(false);
      setForm({ medicine_id: '', type: 'out', quantity: 1, notes: '' });
    } catch (err: any) {
      setErrorMsg(err.message);
    } finally {
      setIsSubmitting(false);
    }
  };

  const formatIDR = (num: number) => new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR' }).format(num);

  return (
    <div className="space-y-6">
      <div className="flex flex-col md:flex-row md:items-end justify-between gap-4">
        <div>
          <h1 className="text-2xl font-display font-bold gradient-text">Stock Transactions</h1>
          <p className="text-slate-400 text-sm mt-1">Audit log of all stock movements.</p>
        </div>
        <button onClick={() => setIsModalOpen(true)} className="btn-primary shrink-0">
          <Plus className="w-4 h-4" /> Record Movement
        </button>
      </div>

      <div className="glass-card p-5">
        {loading ? (
          <div className="py-12 flex justify-center"><Loader2 className="w-8 h-8 animate-spin text-brand-500" /></div>
        ) : (
          <div className="overflow-x-auto">
            <table className="data-table">
              <thead>
                <tr>
                  <th>Date & Time</th>
                  <th>Type</th>
                  <th>Medicine</th>
                  <th>Qty</th>
                  <th>Unit Price</th>
                  <th>Total Price</th>
                  <th>User</th>
                </tr>
              </thead>
              <tbody>
                {logs.length === 0 ? (
                  <tr><td colSpan={7} className="text-center py-8 text-slate-500">No transactions found.</td></tr>
                ) : (
                  logs.map(log => (
                    <tr key={log.id}>
                      <td className="whitespace-nowrap text-sm">
                        {format(new Date(log.created_at), 'dd MMM yyyy, HH:mm')}
                      </td>
                      <td>
                        <span className={`inline-flex items-center gap-1 px-2.5 py-1 rounded-md text-[10px] font-bold uppercase tracking-wider ${
                          log.type === 'in' ? 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20' 
                                            : 'bg-brand-500/10 text-brand-400 border border-brand-500/20'
                        }`}>
                          {log.type === 'in' ? <ArrowDownRight className="w-3 h-3" /> : <ArrowUpRight className="w-3 h-3" />}
                          {log.type === 'in' ? 'Stock In' : 'Dispense'}
                        </span>
                      </td>
                      <td>
                        <p className="font-semibold text-slate-200">{log.medicine_name}</p>
                        {log.notes && <p className="text-xs text-slate-500 italic mt-0.5">{log.notes}</p>}
                      </td>
                      <td className="font-mono">{log.quantity}</td>
                      <td className="font-mono text-xs text-slate-400">{formatIDR(log.unit_price)}</td>
                      <td className="font-mono text-sm font-semibold">{formatIDR(log.total_price)}</td>
                      <td className="text-xs text-slate-400">{log.user_name}</td>
                    </tr>
                  ))
                )}
              </tbody>
            </table>
          </div>
        )}
        
        {/* Pagination controls */}
        {!loading && totalPages > 1 && (
          <div className="flex items-center justify-between mt-6 pt-4 border-t border-white/5">
            <button 
              disabled={page === 1}
              onClick={() => setPage(p => p - 1)}
              className="btn-ghost px-3 py-1 text-xs"
            >
              Previous
            </button>
            <span className="text-xs text-slate-400">Page {page} of {totalPages}</span>
            <button 
              disabled={page === totalPages}
              onClick={() => setPage(p => p + 1)}
              className="btn-ghost px-3 py-1 text-xs"
            >
              Next
            </button>
          </div>
        )}
      </div>

      {/* ── Modal ────────────────────────────────────────────── */}
      {isModalOpen && (
        <div className="fixed inset-0 z-[100] flex items-center justify-center p-4">
          <div className="absolute inset-0 bg-black/60 backdrop-blur-sm" onClick={() => setIsModalOpen(false)} />
          
          <div className="relative glass-card w-full max-w-md p-6 shadow-2xl">
            <h2 className="text-xl font-bold mb-4">Record Stock Movement</h2>
            
            {errorMsg && (
              <div className="flex items-center gap-2 p-3 rounded-lg bg-red-500/10 text-red-400 text-sm mb-4 border border-red-500/20">
                <AlertCircle className="w-4 h-4 shrink-0" /> {errorMsg}
              </div>
            )}

            <form onSubmit={handleSubmit} className="space-y-4">
              <div>
                <label className="block text-xs text-slate-400 mb-1">Transaction Type</label>
                <div className="flex rounded-xl p-1 bg-black/30">
                  <button
                    type="button"
                    onClick={() => setForm({...form, type: 'in'})}
                    className={`flex-1 flex items-center justify-center gap-2 py-2 rounded-lg text-sm font-semibold transition-all ${
                      form.type === 'in' ? 'bg-emerald-500/20 text-emerald-400 shadow-[0_0_15px_rgba(16,185,129,0.2)]' : 'text-slate-500'
                    }`}
                  >
                    <ArrowDownRight className="w-4 h-4" /> Stock In
                  </button>
                  <button
                    type="button"
                    onClick={() => setForm({...form, type: 'out'})}
                    className={`flex-1 flex items-center justify-center gap-2 py-2 rounded-lg text-sm font-semibold transition-all ${
                      form.type === 'out' ? 'bg-brand-500/20 text-brand-400 shadow-[0_0_15px_rgba(99,102,241,0.2)]' : 'text-slate-500'
                    }`}
                  >
                    <ArrowUpRight className="w-4 h-4" /> Dispense
                  </button>
                </div>
              </div>

              <div>
                <label className="block text-xs text-slate-400 mb-1">Medicine *</label>
                <select 
                  required 
                  className="input-field appearance-none bg-surface-card"
                  value={form.medicine_id}
                  onChange={e => setForm({...form, medicine_id: e.target.value})}
                >
                  <option value="" disabled>Select a medicine...</option>
                  {medicines.map(m => (
                    <option key={m.id} value={m.id}>{m.name} (Stock: {m.stock_current} {m.unit})</option>
                  ))}
                </select>
              </div>

              <div>
                <label className="block text-xs text-slate-400 mb-1">Quantity *</label>
                <input 
                  required 
                  type="number" 
                  min="1"
                  className="input-field font-mono"
                  value={form.quantity}
                  onChange={e => setForm({...form, quantity: Number(e.target.value)})}
                />
              </div>

              <div>
                <label className="block text-xs text-slate-400 mb-1">Notes (Optional)</label>
                <input 
                  type="text" 
                  className="input-field"
                  placeholder="e.g. Supplier XYZ / Prescription #123"
                  value={form.notes}
                  onChange={e => setForm({...form, notes: e.target.value})}
                />
              </div>

              <div className="flex justify-end gap-3 mt-6 pt-4 border-t border-white/10">
                <button type="button" onClick={() => setIsModalOpen(false)} className="btn-ghost">Cancel</button>
                <button type="submit" disabled={isSubmitting} className="btn-primary">
                  {isSubmitting && <Loader2 className="w-4 h-4 animate-spin" />}
                  Confirm
                </button>
              </div>
            </form>
          </div>
        </div>
      )}
    </div>
  );
}
