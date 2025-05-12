import { Head } from '@inertiajs/react';
import { CreatePost } from '../components/CreatePost';
import { Navbar } from '../components/Navbar';
import Header from '@/components/Header';
import AdminUserManagement from '@/components/AdminUserManagement';

export interface User {
  id: number;
  name: string;
  email: string;
  is_active: boolean;
}

export default function AdminDashboard({ users }: { users: User[] }) {
  console.log('users prop:', users);
  return (
    <>
        <Navbar />
      <Header />
      <Head title="Admin Dashboard" />
      <div className="!pt-20 !px-4 flex flex-col items-center justify-center bg-[var(--bg-primary)] text-[var(--text-primary)]">
        <h1 className="text-3xl text-amber-300 font-bold">Admin Dashboard</h1>
        <p className="text-lg text-gray-500 !mt-4 !mb-18">Welcome to the admin dashboard.</p>
        <CreatePost />
        <AdminUserManagement users={users} />
      </div>
    </>
  );
}