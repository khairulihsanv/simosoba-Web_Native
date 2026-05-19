import { motion } from 'framer-motion';
import Link from 'next/link';
import { 
  User, 
  UserPlus, 
  AlertTriangle,
  TrendingUp,
  CheckCircle,
  X,
  LogOut,
  Sun
} from 'lucide-react';
import { useState, useEffect } from 'react';

// Define the type for user data
type UserData = {
  id: number;
  name: string;
  email: string;
  role: string;
  status: string;
  lastLogin: string;
  createdAt: string;
};

export default function AdminPage() {
  const [users, setUsers] = useState<UserData[]>([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);
  const [showDeleteConfirm, setShowDeleteConfirm] = useState(false);
  const [userToDelete, setUserToDelete] = useState<number | null>(null);
  
  const isRainySeason = () => {
    const month = new Date().getMonth(); // 0-11, where 0 is January
    return month >= 9 || month <= 2; // Oct (9) to Mar (2)
  };

  useEffect(() => {
    // Simulate fetching users from API
    const fetchUsers = async () => {
      try {
        // In a real app, this would be an API call to /api/users
        setLoading(true);
        await new Promise(resolve => setTimeout(resolve, 1000));
        
        // Mock user data
        setUsers([
          {
            id: 1,
            name: 'Administrator',
            email: 'admin@antigravity.com',
            role: 'superadmin',
            status: 'active',
            lastLogin: '2026-05-19 10:30:00',
            createdAt: '2026-01-15 08:00:00'
          },
          {
            id: 2,
            name: 'John Pharmacist',
            email: 'john@antigravity.com',
            role: 'pharmacist',
            status: 'active',
            lastLogin: '2026-05-18 16:45:00',
            createdAt: '2026-03-22 09:15:00'
          },
          {
            id: 3,
            name: 'Jane Stock Manager',
            email: 'jane@antigravity.com',
            role: 'stockmanager',
            status: 'active',
            lastLogin: '2026-05-19 08:15:00',
            createdAt: '2026-02-10 14:30:00'
          },
          {
            id: 4,
            name: 'Bob Inspector',
            email: 'bob@antigravity.com',
            role: 'inspector',
            status: 'inactive',
            lastLogin: '2026-05-10 11:20:00',
            createdAt: '2026-01-20 16:45:00'
          }
        ]);
      } catch (err) {
        setError('Failed to load users');
        console.error(err);
      } finally {
        setLoading(false);
      }
    };
    
    fetchUsers();
  }, []);

  const handleDeleteUser = (userId: number) => {
    setUserToDelete(userId);
    setShowDeleteConfirm(true);
  };

  const handleConfirmDelete = async () => {
    if (userToDelete === null) return;
    
    try {
      // Simulate API call to delete user
      await new Promise(resolve => setTimeout(resolve, 800));
      
      // Remove user from state
      setUsers(users.filter(user => user.id !== userToDelete));
      setShowDeleteConfirm(false);
      setUserToDelete(null);
    } catch (err) {
      setError('Failed to delete user');
      console.error(err);
    }
  };

  const handleCancelDelete = () => {
    setShowDeleteConfirm(false);
    setUserToDelete(null);
  };

  const toggleUserStatus = async (userId: number) => {
    try {
      // Simulate API call to toggle user status
      await new Promise(resolve => setTimeout(resolve, 600));
      
      // Update user status in state
      setUsers(users.map(user => 
        user.id === userId 
          ? { ...user, status: user.status === 'active' ? 'inactive' : 'active' } 
          : user
      ));
    } catch (err) {
      setError('Failed to update user status');
      console.error(err);
    }
  };

  return (
    <motion.div
      initial={{ opacity: 0, y: 20 }}
      animate={{ opacity: 1, y: 0 }}
      transition={{ duration: 0.8 }}
      className="min-h-screen bg-gradient-to-b from-slate-50 to-slate-100 p-6"
    >
      <header className="mb-8">
        <div className="flex justify-between items-center mb-4">
          <h1 className="text-3xl font-bold text-black">Admin Panel</h1>
          <div className="flex items-center space-x-3">
            <span className="text-sm text-gray-600">
              Last updated: {new Date().toLocaleTimeString()}
            </span>
            <div className="flex items-center space-x-2">
              {isRainySeason() ? (
                <AlertTriangle className="h-5 w-5 text-yellow-500 animate-pulse" />
              ) : (
                <Sun className="h-5 w-5 text-yellow-500" />
              )}
              <span className="text-sm font-medium text-black">
                {isRainySeason() ? 'Rainy Season Protocol Active' : 'Normal Operations'}
              </span>
            </div>
          </div>
        </div>
        
        {/* Admin Alert */}
        {isRainySeason() && (
          <div className="border-2 border-black p-4 mb-6 relative">
            <div className="absolute -top-2 -left-2 bg-white w-full h-full border-2 border-yellow-500"></div>
            <div className="relative z-10 flex items-start space-x-3">
              <AlertTriangle className="h-6 w-6 text-yellow-500 mt-0.5" />
              <div>
                <h2 className="font-semibold text-black">Rainy Season Protocol Active</h2>
                <p className="text-gray-700">
                  Increased monitoring for disease prevention medications. All inventory 
                  adjustments require dual approval during this period.
                </p>
              </div>
            </div>
          </div>
        )}
      </header>

      {/* Error Message */}
      {error && (
        <div className="mb-4 p-3 bg-red-50 border-2 border-red-500 text-red-700 relative">
          <div className="absolute -top-2 -left-2 bg-white w-full h-full border-2 border-red-500"></div>
          <span className="relative z-10">{error}</span>
        </div>
      )}

      {/* Users Table */}
      <div className="border-2 border-black p-6 relative">
        <div className="absolute -top-4 -left-4 bg-white w-full h-full border-2 border-black"></div>
        <div className="relative z-10">
          <h2 className="text-xl font-bold text-black mb-4">User Management</h2>
          <div className="overflow-x-auto">
            <table className="min-w-full border-separate border-spacing-0">
              <thead>
                <tr className="border-b-2 border-black">
                  <th className="px-4 py-3 text-left text-black font-semibold">Name</th>
                  <th className="px-4 py-3 text-left text-black font-semibold">Email</th>
                  <th className="px-4 py-3 text-left text-black font-semibold">Role</th>
                  <th className="px-4 py-3 text-left text-black font-semibold">Status</th>
                  <th className="px-4 py-3 text-left text-black font-semibold">Last Login</th>
                  <th className="px-4 py-3 text-left text-black font-semibold">Actions</th>
                </tr>
              </thead>
              <tbody>
                 {loading ? (
                   <tr>
                     <td colSpan={6} className="px-4 py-6 text-center text-gray-500">
                       Loading users...
                     </td>
                   </tr>
                ) : (
                  users.map(user => (
                    <tr key={user.id} className="border-b border-black hover:bg-gray-50">
                      <td className="px-4 py-3 text-black font-medium">{user.name}</td>
                      <td className="px-4 py-3 text-black">{user.email}</td>
                      <td className="px-4 py-3 text-black">
                        <span className={`px-2 py-1 text-xs font-medium ${
                          user.role === 'superadmin' ? 'bg-red-100 text-red-800' :
                          user.role === 'pharmacist' ? 'bg-blue-100 text-blue-800' :
                          user.role === 'stockmanager' ? 'bg-green-100 text-green-800' :
                          'bg-gray-100 text-gray-800'
                        } rounded`}>
                          {user.role.charAt(0).toUpperCase() + user.role.slice(1)}
                        </span>
                      </td>
                      <td className="px-4 py-3 text-black">
                        <span className={`px-2 py-1 text-xs font-medium ${
                          user.status === 'active' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'
                        } rounded`}>
                          {user.status.charAt(0).toUpperCase() + user.status.slice(1)}
                        </span>
                      </td>
                      <td className="px-4 py-3 text-black">{user.lastLogin}</td>
                      <td className="px-4 py-3 text-black space-x-2">
                        {/* Status Toggle Button */}
                        <button
                          onClick={() => toggleUserStatus(user.id)}
                          className={`px-3 py-1 text-xs font-medium border-2 border-black rounded hover:bg-white hover:text-black transition-all duration-200 relative ${
                            user.status === 'active' ? 'bg-green-500 text-white' : 'bg-red-500 text-white'
                          }`}
                        >
                          <div className="absolute -top-1 -left-1 bg-white w-full h-full border-2 border-black"></div>
                          <span className="relative z-10">
                            {user.status === 'active' ? 'Deactivate' : 'Activate'}
                          </span>
                        </button>
                        
                        {/* Delete Button */}
                        <button
                          onClick={() => handleDeleteUser(user.id)}
                          className="px-3 py-1 text-xs font-medium border-2 border-black rounded hover:bg-white hover:text-black transition-all duration-200 relative bg-red-500 text-white"
                        >
                          <div className="absolute -top-1 -left-1 bg-white w-full h-full border-2 border-black"></div>
                          <span className="relative z-10">
                            <X className="h-4 w-4" /> Delete
                          </span>
                        </button>
                      </td>
                    </tr>
                  ))
                )}
              </tbody>
            </table>
          </div>
        </div>
      </div>

      {/* Add New User Button */}
      <div className="mt-6">
        <Link
          href="/admin/create-user"
          className="inline-block px-6 py-3 font-semibold text-black border-2 border-black hover:bg-white hover:text-black transition-all duration-300 relative"
        >
          <div className="absolute -top-2 -left-2 bg-white w-full h-full border-2 border-black"></div>
          <span className="relative z-10 flex items-center space-x-2">
            <UserPlus className="h-5 w-5" />
            Add New User
          </span>
        </Link>
      </div>

      {/* Delete Confirmation Modal */}
      {showDeleteConfirm && userToDelete !== null && (
        <div className="fixed inset-0 z-50 flex items-center justify-center bg-black/50">
          <div className="border-2 border-black p-6 relative bg-white w-96">
            <div className="absolute -top-4 -left-4 bg-white w-full h-full border-2 border-black"></div>
            <div className="relative z-10">
              <h2 className="text-xl font-bold text-black mb-4">Confirm Deletion</h2>
              <p className="mb-4 text-gray-700">
                Are you sure you want to delete this user? This action cannot be undone.
              </p>
              <div className="flex justify-end space-x-3">
                <button
                  onClick={handleCancelDelete}
                  className="px-4 py-2 font-semibold text-black border-2 border-black hover:bg-white hover:text-black transition-all duration-300 relative"
                >
                  <div className="absolute -top-2 -left-2 bg-white w-full h-full border-2 border-black"></div>
                  <span className="relative z-10">Cancel</span>
                </button>
                <button
                  onClick={handleConfirmDelete}
                  className="px-4 py-2 font-semibold text-black border-2 border-black bg-red-500 text-white hover:bg-red-600 transition-all duration-300 relative"
                >
                  <div className="absolute -top-2 -left-2 bg-white w-full h-full border-2 border-black"></div>
                  <span className="relative z-10">Delete User</span>
                </button>
              </div>
            </div>
          </div>
        </div>
      )}

      <footer className="mt-8 text-center text-gray-500">
        <p>
          Antigravity Medication Stock Monitoring System &copy; {new Date().getFullYear()}
        </p>
      </footer>
    </motion.div>
  );
}