<?php
// Simple security check - you should change this token
$validToken = getenv('ADMIN_SETUP_TOKEN');
$providedToken = $_GET['token'] ?? '';

if (!$validToken || $providedToken !== $validToken) {
    echo "Unauthorized";
    exit(1);
}

echo "<h1>Fix Admin View</h1>";

// Check if the Admin Dashboard view exists
$adminViewDir = __DIR__ . '/../resources/js/Pages/Admin';
if (!is_dir($adminViewDir)) {
    echo "<p>Admin view directory not found. Creating it...</p>";
    
    if (mkdir($adminViewDir, 0755, true)) {
        echo "<p>Created Admin view directory at: $adminViewDir</p>";
    } else {
        echo "<p>Failed to create Admin view directory. Check permissions.</p>";
        exit;
    }
}

$adminViewPath = $adminViewDir . '/Dashboard.tsx';
$adminViewExists = file_exists($adminViewPath);

if ($adminViewExists) {
    echo "<p>Admin Dashboard view already exists at: $adminViewPath</p>";
    
    // Create a backup
    $backupPath = $adminViewPath . '.backup-' . date('Y-m-d-H-i-s');
    copy($adminViewPath, $backupPath);
    echo "<p>Created backup at: $backupPath</p>";
    
    // Read the current view
    $adminViewContent = file_get_contents($adminViewPath);
    
    // Check if the view uses is_active
    if (strpos($adminViewContent, 'is_active') === false) {
        echo "<p>The Admin Dashboard view doesn't use the 'is_active' column. Updating it...</p>";
        
        // This is a complex task as we don't know the exact structure of the view
        // For now, let's just add a note to manually check the view
        echo "<p><strong>Note:</strong> Please manually check the Admin Dashboard view to ensure it correctly handles the 'is_active' column.</p>";
    } else {
        echo "<p>The Admin Dashboard view already uses the 'is_active' column.</p>";
    }
} else {
    echo "<p>Admin Dashboard view not found. Creating a basic one...</p>";
    
    // Create a basic Admin Dashboard view
    $adminViewContent = <<<EOD
import React from 'react';
import { Head, Link } from '@inertiajs/react';
import { Inertia } from '@inertiajs/inertia';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';

interface User {
  id: number;
  name: string;
  email: string;
  is_active: boolean;
}

interface Props {
  auth: any;
  users: User[];
}

export default function Dashboard({ auth, users }: Props) {
  const toggleUserStatus = (userId: number) => {
    Inertia.post(`/admin/users/\${userId}/toggle`);
  };

  const deleteUser = (userId: number) => {
    if (confirm('Are you sure you want to delete this user?')) {
      Inertia.delete(`/admin/users/\${userId}`);
    }
  };

  return (
    <AuthenticatedLayout
      user={auth.user}
      header={<h2 className="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">Admin Dashboard</h2>}
    >
      <Head title="Admin Dashboard" />

      <div className="py-12">
        <div className="max-w-7xl mx-auto sm:px-6 lg:px-8">
          <div className="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
            <div className="p-6 text-gray-900 dark:text-gray-100">
              <h3 className="text-lg font-semibold mb-4">User Management</h3>
              
              <div className="overflow-x-auto">
                <table className="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                  <thead className="bg-gray-50 dark:bg-gray-700">
                    <tr>
                      <th scope="col" className="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                        Name
                      </th>
                      <th scope="col" className="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                        Email
                      </th>
                      <th scope="col" className="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                        Status
                      </th>
                      <th scope="col" className="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                        Actions
                      </th>
                    </tr>
                  </thead>
                  <tbody className="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                    {users.map((user) => (
                      <tr key={user.id}>
                        <td className="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-gray-100">
                          {user.name}
                        </td>
                        <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300">
                          {user.email}
                        </td>
                        <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300">
                          <span className={\`px-2 inline-flex text-xs leading-5 font-semibold rounded-full \${user.is_active ? 'bg-green-100 text-green-800 dark:bg-green-800 dark:text-green-100' : 'bg-red-100 text-red-800 dark:bg-red-800 dark:text-red-100'}\`}>
                            {user.is_active ? 'Active' : 'Inactive'}
                          </span>
                        </td>
                        <td className="px-6 py-4 whitespace-nowrap text-sm font-medium">
                          <button
                            onClick={() => toggleUserStatus(user.id)}
                            className="text-indigo-600 dark:text-indigo-400 hover:text-indigo-900 dark:hover:text-indigo-200 mr-4"
                          >
                            {user.is_active ? 'Deactivate' : 'Activate'}
                          </button>
                          <button
                            onClick={() => deleteUser(user.id)}
                            className="text-red-600 dark:text-red-400 hover:text-red-900 dark:hover:text-red-200"
                          >
                            Delete
                          </button>
                        </td>
                      </tr>
                    ))}
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        </div>
      </div>
    </AuthenticatedLayout>
  );
}
EOD;

    // Write the Admin Dashboard view
    if (file_put_contents($adminViewPath, $adminViewContent)) {
        echo "<p>Created a basic Admin Dashboard view at: $adminViewPath</p>";
    } else {
        echo "<p>Failed to create Admin Dashboard view. Check permissions.</p>";
    }
}

echo "<p>Done. <a href='/admin'>Go to admin dashboard</a></p>";
