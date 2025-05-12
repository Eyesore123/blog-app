import { useState } from 'react';
import { router } from '@inertiajs/react';
import { useConfirm } from '@/context/ConfirmationContext';

interface User {
  id: number;
  name: string;
  email: string;
  is_active: boolean;
}

interface AdminUserManagementProps {
  users?: User[]; // Make the prop optional
}

export default function AdminUserManagement({ users = [] }: AdminUserManagementProps) {
  const { confirm } = useConfirm();

  const toggleUserActiveStatus = (userId: number, currentStatus: boolean) => {
    const action = currentStatus ? 'deactivate' : 'activate';
    const actionTitle = currentStatus ? 'Deactivate User' : 'Activate User';
    const actionMessage = currentStatus
      ? 'Are you sure you want to deactivate this user?'
      : 'Do you want to activate this user?';

    confirm({
      title: actionTitle,
      message: actionMessage,
      confirmText: action.charAt(0).toUpperCase() + action.slice(1),
      cancelText: 'Cancel',
      type: currentStatus ? 'warning' : 'info',
    }).then((confirmed) => {
      if (confirmed) {
        router.post(`/admin/users/${userId}/toggle`);
      }
    });
  };

  const deleteUser = (userId: number) => {
    confirm({
      title: 'Delete User',
      message: 'WARNING: This will permanently delete the user account. Proceed?',
      confirmText: 'Delete',
      cancelText: 'Cancel',
      type: 'danger',
    }).then((confirmed) => {
      if (confirmed) {
        router.delete(`/admin/users/${userId}`);
      }
    });
  };

  return (
    <div className="w-full max-w-3xl mx-auto !mt-8 !mb-30">
      <h2 className="text-2xl font-bold !mb-4">User Management</h2>
      <table className="w-full border-collapse">
        <thead>
          <tr className="bg-[#5800FF]/10">
            <th className="!p-2 border">ID</th>
            <th className="!p-2 border">Name</th>
            <th className="!p-2 border">Email</th>
            <th className="!p-2 border">Active?</th>
            <th className="!p-2 border">Actions</th>
          </tr>
        </thead>
        <tbody>
          {users.map((user) => (
            <tr key={user.id} className="hover:opacity-80">
              <td className="!p-2 border">{user.id}</td>
              <td className="!p-2 border">{user.name}</td>
              <td className="!p-2 border">{user.email}</td>
              <td className="!p-2 border">{user.is_active ? 'Yes' : 'No'}</td>
              <td className="!p-2 border flex gap-2 flex-wrap">
                <button
                  onClick={() => toggleUserActiveStatus(user.id, user.is_active)}
                  className={`${
                    user.is_active
                      ? 'bg-yellow-400 hover:bg-yellow-500 text-black'
                      : 'bg-green-500 hover:bg-green-600 text-white'
                  } !px-2 !py-1 rounded`}
                >
                  {user.is_active ? 'Deactivate' : 'Activate'}
                </button>
                <button
                  onClick={() => deleteUser(user.id)}
                  className="bg-red-500 text-white !px-2 !py-1 rounded hover:bg-red-600"
                >
                  Delete
                </button>
              </td>
            </tr>
          ))}
        </tbody>
      </table>
    </div>
  );
}
