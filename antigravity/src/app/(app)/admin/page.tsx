'use client';
/**
 * app/(app)/admin/page.tsx
 * ─────────────────────────────────────────────────────────────
 * Superadmin User Management
 * Handles user CRUD (name, email, role, password).
 * Only accessible by users with 'superadmin' role.
 * ─────────────────────────────────────────────────────────────
 */

import { useState, useEffect, FormEvent } from 'react';
import { useRouter } from 'next/navigation';
import { Shield, ShieldAlert, Plus, Edit2, Trash2, Loader2, AlertCircle, Key } from 'lucide-react';
import { format } from 'date-fns';
import type { User, AuthUser } from '@/types';

export default function AdminPage() {
  const router = useRouter();
  const [currentUser, setCurrentUser] = useState<AuthUser | null>(null);
  const [users, setUsers] = useState<User[]>([]);
  const [loading, setLoading] = useState(true);

  // Modal State
  const [isModalOpen, setIsModalOpen] = useState(false);
  const [isSubmitting, setIsSubmitting] = useState(false);
  const [errorMsg, setErrorMsg] = useState('');
  const [successMsg, setSuccessMsg] = useState('');
  
  // Edit mode tracking
  const [editingId, setEditingId] = useState<number | null>(null);
  
  // Form fields
  const [form, setForm] = useState({
    name: '',
    email: '',
    password: '',
    role: 'admin'
  });

  useEffect(() => {
    // Check role and fetch users
    fetch('/api/auth/me')
      .then(res => res.json())
      .then(json => {
        if (!json.success || json.data.role !== 'superadmin') {
          router.push('/dashboard'); // kick out non-admins
        } else {
          setCurrentUser(json.data);
          loadUsers();
        }
      })
      .catch(console.error);
  }, [router]);

  const loadUsers = async () => {
    setLoading(true);
    try {
      const res = await fetch('/api/users');
      const json = await res.json();
      if (json.success) setUsers(json.data);
    } catch (err) {
      console.error(err);
    } finally {
      setLoading(false);
    }
  };

  const handleOpenModal = (user?: User) => {
    setErrorMsg('');
    setSuccessMsg('');
    if (user) {
      setEditingId(user.id);
      setForm({ name: user.name, email: user.email, role: user.role, password: '' });
    } else {
      setEditingId(null);
      setForm({ name: '', email: '', role: 'admin', password: '' });
    }
    setIsModalOpen(true);
  };

  const handleSubmit = async (e: FormEvent) => {
    e.preventDefault();
    setIsSubmitting(true);
    setErrorMsg('');
    setSuccessMsg('');

    try {
      if (editingId) {
        // Update user
        const updateData: any = { name: form.name, role: form.role };
        if (form.password) updateData.password = form.password; // only send if changing

        const res = await fetch(`/api/users/${editingId}`, {
          method: 'PATCH',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify(updateData),
        });
        const json = await res.json();
        if (!res.ok) throw new Error(json.error);
        setSuccessMsg('User updated successfully.');
      } else {
        // Create user
        const res = await fetch('/api/users', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify(form),
        });
        const json = await res.json();
        if (!res.ok) throw new Error(json.error);
        setSuccessMsg('User created successfully.');
      }
      
      await loadUsers();
      setTimeout(() => setIsModalOpen(false), 1500);
    } catch (err: any) {
      setErrorMsg(err.message);
    } finally {
      setIsSubmitting(false);
    }
  };

  const handleDelete = async (id: number) => {
    if (id === currentUser?.id) {
      alert("You cannot delete your own account.");
      return;
    }
    if (!confirm("Are you sure you want to permanently delete this user?")) return;

    try {
      const res = await fetch(`/api/users/${id}`, { method: 'DELETE' });
      if (!res.ok) throw new Error("Delete failed");
      await loadUsers();
    } catch (err) {
      console.error(err);
      alert("Failed to delete user.");
    }
  };

  if (!currentUser) return null; // Prevent flash of content if redirecting

  return (
    <div className="space-y-6">
      <div className="flex flex-col md:flex-row md:items-end justify-between gap-4">
        <div>
          <h1 className="text-2xl font-display font-bold text-white flex items-center gap-2">
            <ShieldAlert className="w-6 h-6 text-brand-500" /> Superadmin Console
          </h1>
          <p className="text-slate-400 text-sm mt-1">Manage system access and staff accounts.</p>
        </div>
        <button onClick={() => handleOpenModal()} className="btn-primary shrink-0">
          <Plus className="w-4 h-4" /> Add User
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
                  <th>Name</th>
                  <th>Email</th>
                  <th>Role</th>
                  <th>Joined</th>
                  <th className="text-right">Actions</th>
                </tr>
              </thead>
              <tbody>
                {users.length === 0 ? (
                  <tr><td colSpan={5} className="text-center py-8 text-slate-500">No users found.</td></tr>
                ) : (
                  users.map(u => (
                    <tr key={u.id}>
                      <td className="font-semibold text-slate-200">
                        {u.name}
                        {u.id === currentUser.id && <span className="ml-2 text-[10px] bg-brand-500/20 text-brand-400 px-2 py-0.5 rounded-full">YOU</span>}
                      </td>
                      <td className="text-slate-400">{u.email}</td>
                      <td>
                        <span className={`inline-flex items-center gap-1 px-2.5 py-1 rounded-md text-[10px] font-bold uppercase tracking-wider ${
                          u.role === 'superadmin' ? 'bg-purple-500/10 text-purple-400 border border-purple-500/20' 
                                                  : 'bg-slate-500/10 text-slate-400 border border-slate-500/20'
                        }`}>
                          {u.role === 'superadmin' && <Shield className="w-3 h-3" />}
                          {u.role}
                        </span>
                      </td>
                      <td className="text-xs text-slate-500">
                        {format(new Date(u.created_at), 'dd MMM yyyy')}
                      </td>
                      <td className="text-right">
                        <div className="flex items-center justify-end gap-2">
                          <button 
                            onClick={() => handleOpenModal(u)} 
                            aria-label={`Edit ${u.name}`}
                            className="p-2 rounded-lg bg-white/5 text-slate-400 hover:text-brand-400 hover:bg-brand-500/10 transition-colors"
                          >
                            <Edit2 className="w-4 h-4" />
                          </button>
                          <button 
                            onClick={() => handleDelete(u.id)} 
                            disabled={u.id === currentUser.id}
                            aria-label={`Delete ${u.name}`}
                            className="p-2 rounded-lg bg-white/5 text-slate-400 hover:text-red-400 hover:bg-red-500/10 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                          >
                            <Trash2 className="w-4 h-4" />
                          </button>
                        </div>
                      </td>
                    </tr>
                  ))
                )}
              </tbody>
            </table>
          </div>
        )}
      </div>

      {/* ── Modal ────────────────────────────────────────────── */}
      {isModalOpen && (
        <div className="fixed inset-0 z-[100] flex items-center justify-center p-4">
          <div className="absolute inset-0 bg-black/60 backdrop-blur-sm" onClick={() => !isSubmitting && setIsModalOpen(false)} />
          
          <div className="relative glass-card w-full max-w-md p-6 shadow-2xl">
            <h2 className="text-xl font-bold mb-4">{editingId ? 'Edit User' : 'Add New User'}</h2>
            
            {errorMsg && (
              <div className="flex items-center gap-2 p-3 rounded-lg bg-red-500/10 text-red-400 text-sm mb-4 border border-red-500/20">
                <AlertCircle className="w-4 h-4 shrink-0" /> {errorMsg}
              </div>
            )}
            
            {successMsg && (
              <div className="flex items-center gap-2 p-3 rounded-lg bg-emerald-500/10 text-emerald-400 text-sm mb-4 border border-emerald-500/20">
                ✓ {successMsg}
              </div>
            )}

            <form onSubmit={handleSubmit} className="space-y-4">
              <div>
                <label htmlFor="user-name" className="block text-xs text-slate-400 mb-1">Full Name</label>
                <input 
                  id="user-name"
                  required type="text" className="input-field" 
                  value={form.name} onChange={e => setForm({...form, name: e.target.value})}
                />
              </div>

              <div>
                <label htmlFor="user-email" className="block text-xs text-slate-400 mb-1">Email Address</label>
                <input 
                  id="user-email"
                  required={!editingId} type="email" className="input-field disabled:opacity-50" 
                  disabled={!!editingId} // Don't allow changing email for simplicity
                  value={form.email} onChange={e => setForm({...form, email: e.target.value})}
                />
              </div>

              <div>
                <label htmlFor="user-role" className="block text-xs text-slate-400 mb-1">Access Role</label>
                <select 
                  id="user-role"
                  className="input-field appearance-none bg-surface-card"
                  value={form.role} onChange={e => setForm({...form, role: e.target.value})}
                >
                  <option value="admin">Admin (Staff/Operator)</option>
                  <option value="superadmin">Superadmin (Full Access)</option>
                </select>
              </div>

              <div>
                <label htmlFor="user-password" className="block text-xs text-slate-400 mb-1">
                  {editingId ? 'New Password (leave blank to keep current)' : 'Password'}
                </label>
                <div className="relative">
                  <Key className="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-500" />
                  <input 
                    id="user-password"
                    type="password" minLength={6} className="input-field pl-10" 
                    required={!editingId}
                    placeholder={editingId ? "••••••••" : "Min 6 chars"}
                    value={form.password} onChange={e => setForm({...form, password: e.target.value})}
                  />
                </div>
              </div>

              <div className="flex justify-end gap-3 mt-6 pt-4 border-t border-white/10">
                <button type="button" onClick={() => setIsModalOpen(false)} className="btn-ghost">Close</button>
                <button type="submit" disabled={isSubmitting} className="btn-primary">
                  {isSubmitting && <Loader2 className="w-4 h-4 animate-spin" />}
                  {editingId ? 'Update User' : 'Create User'}
                </button>
              </div>
            </form>
          </div>
        </div>
      )}
    </div>
  );
}
