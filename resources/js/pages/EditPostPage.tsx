import React, { useState, useEffect } from 'react';
import { router } from '@inertiajs/react';
import { Toaster, toast } from 'sonner';
import Header from '../components/Header';
import { Navbar } from '@/components/Navbar';
import { useTheme } from '../context/ThemeContext';
import axiosInstance from '../components/axiosInstance';
import { getCsrfToken } from '../components/auth';

interface Post {
  id: number;
  title: string;
  slug?: string;
  content: string;
  image_url: string | null;
  topic: string;
}

interface EditPostPageProps {
  post: Post;
}

const EditPostPage: React.FC<EditPostPageProps> = ({ post }) => {
  const { theme } = useTheme();

  const [title, setTitle] = useState(post.title || '');
  const [content, setContent] = useState(post.content || '');
  const [topic, setTopic] = useState(post.topic || '');
  const [submitting, setSubmitting] = useState(false);

  const [imageFile, setImageFile] = useState<File | null>(null);
  const [previewUrl, setPreviewUrl] = useState<string | null>(null);

  // Show preview of selected image
  useEffect(() => {
    if (imageFile) {
      const reader = new FileReader();
      reader.onloadend = () => {
        setPreviewUrl(reader.result as string);
      };
      reader.readAsDataURL(imageFile);
    } else {
      setPreviewUrl(null);
    }
  }, [imageFile]);

  async function handleSubmit(e: React.FormEvent) {
  e.preventDefault();
  setSubmitting(true);

  await getCsrfToken();

  try {
    const formData = new FormData();
    formData.append('title', title);
    formData.append('content', content);
    formData.append('topic', topic);
    formData.append('_method', 'PUT'); // <-- Add this

    if (imageFile) {
      console.log('Appending image file:', imageFile);
      formData.append('image', imageFile);
    } else {
      console.log('No image file selected');
    }

    // Optional debug
    formData.forEach((value, key) => {
      console.log(key, value);
    });

    // Send as POST, not PUT
    const response = await axiosInstance.post(`/api/posts/${post.id}`, formData);

    toast.success('Post updated successfully');
    router.visit(`/post/${post.id}`);
  } catch (error: any) {
    if (error.response) {
      console.error('Validation Errors:', error.response.data.errors);
      toast.error(
        error.response.data.message
          ? `Error updating post: ${error.response.data.message}`
          : 'Validation error — check your fields'
      );
    } else {
      console.error('Failed to update post', error);
      toast.error('Error updating post');
    }
  } finally {
    setSubmitting(false);
  }
}


  return (
    <div className={`min-h-screen ${theme}`}>
      <div className="min-h-screen bg-[var(--bg-primary)] text-[var(--text-primary)]">
        <Navbar />
        <Header />
        <div className="w-full mx-auto flex justify-center items-center gap-4 md:gap-10 xl:gap-18 !mt-20">
          <main className="!p-8 mx-auto flex flex-col min-w-200 justify-center gap-8">
            <h1 className="text-3xl font-bold !mb-6">Edit Post</h1>

            <form onSubmit={handleSubmit} encType="multipart/form-data" className="!space-y-4">
              <div>
                <label className="block !mb-1 font-medium">Title</label>
                <input
                  type="text"
                  value={title}
                  onChange={(e) => setTitle(e.target.value)}
                  className="w-full !p-2 rounded border border-[#5800FF]/20 bg-[var(--bg-primary)]"
                />
              </div>

              <div>
                <label className="block !mb-1 font-medium">Content</label>
                <textarea
                  value={content}
                  onChange={(e) => setContent(e.target.value)}
                  className="w-full !p-2 rounded border border-[#5800FF]/20 bg-[var(--bg-primary)]"
                  rows={8}
                />
              </div>

              <div>
                <label className="block !mb-1 font-medium">Image Upload</label>
                <input
                  type="file"
                  accept="image/*"
                  onChange={(e) => {
                    const file = e.target.files?.[0];
                    if (file) {
                      // Validate file type before setting it
                      const allowedTypes = ['image/jpeg', 'image/png', 'image/jpg', 'image/gif', 'image/webp', 'image/svg+xml'];
                      if (allowedTypes.includes(file.type)) {
                        setImageFile(file);
                      } else {
                        toast.error('Please upload a valid image file (jpeg, png, jpg, gif, webp, svg)');
                      }
                    } else {
                      setImageFile(null);
                    }
                  }}
                  className="w-full !p-2 rounded border border-[#5800FF]/20 bg-[var(--bg-primary)]"
                />

                {previewUrl ? (
                  <div className="!mt-2">
                    <p className="text-sm !mb-1">New Image Preview:</p>
                    <img src={previewUrl} alt="Preview" className="max-w-xs rounded shadow" />
                  </div>
                ) : post.image_url ? (
                  <div className="!mt-2">
                    <p className="text-sm mb-1">Current Image:</p>
                    <a href={post.image_url} target="_blank" rel="noopener noreferrer">
                      <img src={post.image_url} alt="Current" className="max-w-xs rounded shadow" />
                    </a>
                  </div>
                ) : (
                  <p className="text-sm mt-1 text-gray-500">No image uploaded.</p>
                )}
              </div>

              <div>
                <label className="block !mb-1 font-medium">Topic</label>
                <input
                  type="text"
                  value={topic}
                  onChange={(e) => setTopic(e.target.value)}
                  className="w-full !p-2 rounded border border-[#5800FF]/20 bg-[var(--bg-primary)]"
                />
              </div>

              <button
                type="submit"
                disabled={submitting}
                className="!px-4 !py-2 bg-[#5800FF] text-white rounded hover:bg-[#E900FF] disabled:opacity-50 transition-colors"
              >
                {submitting ? 'Saving...' : 'Save Changes'}
              </button>
            </form>

            <Toaster />
          </main>
        </div>
      </div>
    </div>
  );
};

export default EditPostPage;
