import React, { useState } from 'react';
import { Head } from '@inertiajs/react';
import { CreatePost } from '../components/CreatePost';
import { Navbar } from '../components/Navbar';
import Header from '@/components/Header';
import AdminUserManagement from '@/components/AdminUserManagement';
import AdminPostPreview from '@/components/AdminPostPreview';

export interface User {
  id: number;
  name: string;
  email: string;
  is_active: boolean;
}

export interface Post {
  id?: number;
  title: string;
  content: string;
  topic?: string;
  author?: string;
  created_at?: string;
  image_url?: string | null;
  updated_at?: string;
  _id?: string;
  slug?: string;
  [key: string]: any;
}

export default function AdminDashboard({ users }: { users: User[] }) {
  const [previewPost, setPreviewPost] = useState<Post>({
    title: '',
    content: '',
    image_url: '',
  });

  return (
    <>
      <Navbar />
      <Header />
      <Head title="Admin Dashboard" />
      <div className="!pt-20 !px-4 flex flex-col items-center justify-center bg-[var(--bg-primary)] text-[var(--text-primary)]">
        <h1 className="text-3xl text-amber-300 font-bold">Admin Dashboard</h1>
        <p className="text-lg text-gray-500 !mt-4 !mb-18">Welcome to the admin dashboard.</p>

        {/* Post creation form */}
       <CreatePost onPreviewChange={setPreviewPost} />

        {/* Live preview */}
        {(previewPost.title || previewPost.content || previewPost.image_url) && (
          <AdminPostPreview previewPost={previewPost} />
        )}

        {/* User management */}
        <AdminUserManagement users={users} />
      </div>
    </>
  );
}
