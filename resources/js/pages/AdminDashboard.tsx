import { Head } from '@inertiajs/react';
import { CreatePost } from '../components/CreatePost';
import { Navbar } from '../components/Navbar';
import Header from '@/components/Header';

export default function AdminDashboard() {
  return (
    <>
        <Navbar />
      <Header />
      <Head title="Admin Dashboard" />
      <div className="!pt-20 !px-4 flex flex-col items-center justify-center bg-[var(--bg-primary)] text-[var(--text-primary)]">
        <h1 className="text-3xl text-amber-300 font-bold">Admin Dashboard</h1>
        <CreatePost />
      </div>
    </>
  );
}
