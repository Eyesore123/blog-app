import React, { useState, useEffect } from 'react';
import { Head, usePage } from '@inertiajs/react';
import { CreatePost } from '../components/CreatePost';
import { Navbar } from '../components/Navbar';
import Header from '@/components/Header';
import AdminUserManagement from '@/components/AdminUserManagement';
import AdminPostPreview from '@/components/AdminPostPreview';
import PostPanel from '@/components/PostPanel';
import axiosInstance from '../components/axiosInstance';
import { Post } from '@/types/post'; // adjust path based on your structure


interface User {
  id: number;
  name: string;
  email: string;
  is_active: boolean;
}

interface AdminDashboardProps {
  users: User[];
  posts: Post[];
}

export default function AdminDashboard() {
  const { users, posts } = usePage<{
    props: AdminDashboardProps;
  }>().props;

  console.log('users:', users);
  console.log('posts:', posts);

  const [currentView, setCurrentView] = useState<'create' | 'users' | 'posts'>('create');
  const [previewPost, setPreviewPost] = useState<Post | null>(null);

  useEffect(() => {
    async function fetchInitialPost() {
      try {
        const response = await axiosInstance.get('/api/posts/initial');
        setPreviewPost(response.data);
      } catch (error) {
        console.error('Failed to fetch initial post', error);
      }
    }
    fetchInitialPost();
  }, []);

  const shouldShowPreview =
    previewPost &&
    (previewPost.title?.trim() ||
      previewPost.content?.trim() ||
      previewPost.image_url);

  return (
    <>
      <Head title="Admin Dashboard" />
      <Navbar />
      <Header />

      <div className="!pt-20 !px-4 bg-[var(--bg-primary)] text-[var(--text-primary)] min-h-screen">
        <h1 className="text-3xl text-amber-300 font-bold text-center">Admin Dashboard</h1>
        <p className="text-lg text-gray-500 text-center !my-8">
          Welcome to the admin dashboard.
        </p>

        {/* Menu Navigation */}
        <div className="flex justify-center !mb-8 gap-4">
          {(['create', 'users', 'posts'] as const).map((view) => (
            <button
              key={view}
              onClick={() => setCurrentView(view)}
              className={`!px-4 !py-2 rounded ${
                currentView === view ? 'bg-amber-400 text-black' : 'bg-gray-700 text-white'
              }`}
            >
              {view === 'create'
                ? 'Create Post'
                : view === 'users'
                ? 'User Management'
                : 'Posts & Translations'}
            </button>
          ))}
        </div>

        {/* Views */}
        <div className="flex flex-col items-center justify-center w-full">
          {currentView === 'create' && (
            <>
              <CreatePost
                onPreviewChange={(preview) =>
                  setPreviewPost({ id: 0, ...preview })
                }
              />
              {shouldShowPreview && <AdminPostPreview previewPost={previewPost} />}
            </>
          )}

          {currentView === 'users' && <AdminUserManagement users={users} />}

          {currentView === 'posts' && (
            <div className="w-full max-w-6xl">
              {posts && posts.length > 0 ? (
                <PostPanel allPosts={posts as Post[]} />
              ) : (
                <div className="text-center text-gray-400">No posts available</div>
              )}
            </div>
          )}
        </div>
      </div>
    </>
  );
}
