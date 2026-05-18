'use client';
/**
 * app/(app)/transactions/page.tsx
 * ─────────────────────────────────────────────────────────────
 * Stock In/Out Transactions
 * Records movements and displays a paginated audit log.
 * ─────────────────────────────────────────────────────────────
 */

import { useState, useEffect, FormEvent, useRef } from 'react';
import { ArrowDownRight, ArrowUpRight, Search, Plus, Loader2, AlertCircle, Scan } from 'lucide-react';
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

  // Scanner State
  const [scannerActive, setScannerActive] = useState(false);
  const [scannerLoaded, setScannerLoaded] = useState(false);
  const html5QrCodeRef = useRef<any>(null);

  const startScanner = () => {
    setScannerActive(true);
    if (typeof window !== 'undefined' && !(window as any).Html5Qrcode) {
      const script = document.createElement('script');
      script.src = 'https://unpkg.com/html5-qrcode';
      script.onload = () => {
        setScannerLoaded(true);
        initQrCode();
      };
      document.body.appendChild(script);
    } else {
      setScannerLoaded(true);
      setTimeout(initQrCode, 100);
    }
  };

  const initQrCode = () => {
    if (typeof window === 'undefined' || !(window as any).Html5Qrcode) return;
    try {
      const Html5Qrcode = (window as any).Html5Qrcode;
      const Html5QrcodeSupportedFormats = (window as any).Html5QrcodeSupportedFormats;
      
      const qrCode = new Html5Qrcode("reader");
      html5QrCodeRef.current = qrCode;
      
      const config = { 
        fps: 10, 
        qrbox: { width: 200, height: 200 },
        formatsToSupport: [
          Html5QrcodeSupportedFormats.QR_CODE,
          Html5QrcodeSupportedFormats.EAN_13,
          Html5QrcodeSupportedFormats.UPC_A,
          Html5QrcodeSupportedFormats.CODE_128
        ]
      };
      
      qrCode.start(
        { facingMode: "environment" }, 
        config,
        (decodedText: string) => {
          stopScanner();
          let idToSelect = decodedText;
          if (decodedText.startsWith("OBAT:")) {
            idToSelect = decodedText.split(":")[1];
          }
          const matched = medicines.find(m => String(m.id) === String(idToSelect) || m.name.toLowerCase().includes(decodedText.toLowerCase()));
          if (matched) {
            setForm(prev => ({ ...prev, medicine_id: String(matched.id) }));
          } else {
            alert(`Medicine with code "${decodedText}" not found.`);
          }
        },
        () => { /* scanning... */ }
      ).catch((err: any) => console.error("Error starting scanner:", err));
    } catch (e) {
      console.error(e);
    }
  };

  const stopScanner = () => {
    setScannerActive(false);
    if (html5QrCodeRef.current) {
      html5QrCodeRef.current.stop().then(() => {
        html5QrCodeRef.current = null;
      }).catch((e: any) => console.error(e));
    }
  };

  // Clean up scanner on unmount
  useEffect(() => {
    return () => {
      if (html5QrCodeRef.current) {
        html5QrCodeRef.current.stop().catch((e: any) => console.error(e));
      }
    };
  }, []);

  const handleCloseModal = () => {
    stopScanner();
    setIsModalOpen(false);
    setForm({ medicine_id: '', type: 'out', quantity: 1, notes: '' });
  };

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
          <div className="absolute inset-0 bg-black/60 backdrop-blur-sm" onClick={handleCloseModal} />
          
          <div className="relative glass-card w-full max-w-md p-6 shadow-2xl">
            <h2 className="text-xl font-bold mb-4">Record Stock Movement</h2>
            
            {errorMsg && (
              <div className="flex items-center gap-2 p-3 rounded-lg bg-red-500/10 text-red-400 text-sm mb-4 border border-red-500/20">
                <AlertCircle className="w-4 h-4 shrink-0" /> {errorMsg}
              </div>
            )}

            <form onSubmit={handleSubmit} className="space-y-4">
              <div>
                <label id="tx-type-label" className="block text-xs text-slate-400 mb-1">Transaction Type</label>
                <div className="flex rounded-xl p-1 bg-black/30" role="group" aria-labelledby="tx-type-label">
                  <button
                    type="button"
                    onClick={() => setForm({...form, type: 'in'})}
                    aria-pressed={form.type === 'in'}
                    className={`flex-1 flex items-center justify-center gap-2 py-2 rounded-lg text-sm font-semibold transition-all ${
                      form.type === 'in' ? 'bg-emerald-500/20 text-emerald-400 shadow-[0_0_15px_rgba(16,185,129,0.2)]' : 'text-slate-500'
                    }`}
                  >
                    <ArrowDownRight className="w-4 h-4" /> Stock In
                  </button>
                  <button
                    type="button"
                    onClick={() => setForm({...form, type: 'out'})}
                    aria-pressed={form.type === 'out'}
                    className={`flex-1 flex items-center justify-center gap-2 py-2 rounded-lg text-sm font-semibold transition-all ${
                      form.type === 'out' ? 'bg-brand-500/20 text-brand-400 shadow-[0_0_15px_rgba(99,102,241,0.2)]' : 'text-slate-500'
                    }`}
                  >
                    <ArrowUpRight className="w-4 h-4" /> Dispense
                  </button>
                </div>
              </div>

              <div>
                <div className="flex justify-between items-center mb-1.5">
                  <label htmlFor="tx-medicine" className="block text-xs text-slate-400">Medicine *</label>
                  <button
                    type="button"
                    onClick={startScanner}
                    className="flex items-center gap-1.5 text-xs text-brand-400 hover:text-brand-300 font-bold bg-brand-500/10 px-2.5 py-1 rounded-lg border border-brand-500/20 transition-all"
                  >
                    <Scan className="w-3.5 h-3.5" /> Scan QR/Barcode
                  </button>
                </div>

                {scannerActive && (
                  <div className="mb-4 p-3 bg-black/40 border border-white/10 rounded-xl">
                    <div id="reader" className="w-full overflow-hidden rounded-lg bg-black"></div>
                    <button
                      type="button"
                      onClick={stopScanner}
                      className="btn-ghost w-full py-1.5 text-xs mt-2 border border-white/10"
                    >
                      Close Scanner
                    </button>
                  </div>
                )}

                <select 
                  id="tx-medicine"
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
                <label htmlFor="tx-quantity" className="block text-xs text-slate-400 mb-1">Quantity *</label>
                <input 
                  id="tx-quantity"
                  required 
                  type="number" 
                  min="1"
                  className="input-field font-mono"
                  value={form.quantity}
                  onChange={e => setForm({...form, quantity: Number(e.target.value)})}
                />
              </div>

              <div>
                <label htmlFor="tx-notes" className="block text-xs text-slate-400 mb-1">Notes (Optional)</label>
                <input 
                  id="tx-notes"
                  type="text" 
                  className="input-field"
                  placeholder="e.g. Supplier XYZ / Prescription #123"
                  value={form.notes}
                  onChange={e => setForm({...form, notes: e.target.value})}
                />
              </div>

              <div className="flex justify-end gap-3 mt-6 pt-4 border-t border-white/10">
                <button type="button" onClick={handleCloseModal} className="btn-ghost">Cancel</button>
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
