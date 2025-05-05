import { Head } from '@inertiajs/react';
import { CreatePost } from '../components/CreatePost';
import { Navbar } from '../components/Navbar';

export default function AdminDashboard() {
  return (
    <>
        <Navbar />
      <Head title="Admin Dashboard" />
      <div className="min-h-screen py-8 px-4 flex flex-col items-center justify-center bg-[var(--bg-primary)] text-[var(--text-primary)]">
        <h1 className="text-3xl text-amber-300 font-bold mb-6">Admin Dashboard</h1>
        <CreatePost />
      </div>
    </>
  );
}
