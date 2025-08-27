import React, { useState, useEffect } from 'react';
import { router, useForm } from '@inertiajs/react';
import { Toaster, toast } from 'sonner';
import Header from '../components/Header';
import { Navbar } from '@/components/Navbar';
import { useTheme } from '../context/ThemeContext';
import axiosInstance from '../components/axiosInstance';
import { getCsrfToken } from '../components/auth';

interface Tag {
  id: number;
  name: string;
}

interface Post {
  id: number;
  title: string;
  slug?: string;
  content: string;
  image_url: string | null;
  topic: string;
  tags?: Tag[];
}

interface EditPostPageProps {
  post: Post;
}

const EditPostPage: React.FC<EditPostPageProps> = ({ post }) => {
  const { theme } = useTheme();

  // Local state
  const [title, setTitle] = useState(post.title);
  const [content, setContent] = useState(post.content);
  const [topic, setTopic] = useState(post.topic);
  const [tags, setTags] = useState<string[]>([]);
  const [loading, setLoading] = useState<boolean>(true);
  const [allTags, setAllTags] = useState<string[]>([]); // For autocomplete (not implemented yet)
  const [tagInput, setTagInput] = useState('');
  const [submitting, setSubmitting] = useState(false);
  const [imageFile, setImageFile] = useState<File | null>(null);
  const [previewUrl, setPreviewUrl] = useState<string | null>(null);

  // Inertia form (we only use it for image & tags sync)
  const { setData } = useForm({
    title: '',
    content: '',
    topic: '',
    published: true,
    image: null as File | null,
    tags: [] as string[],
  });

  // On mount, initialize tags array (just the names) and formData.tags
  useEffect(() => {
    const initialTagNames = post.tags?.map((t) => t.name) || [];
    setTags(initialTagNames);
    setData('tags', initialTagNames);
  }, [post.tags, setData]);

  // Preview image when imageFile changes
  useEffect(() => {
    if (imageFile) {
      const url = URL.createObjectURL(imageFile);
      setPreviewUrl(url);
      return () => URL.revokeObjectURL(url);
    }
    setPreviewUrl(null);
  }, [imageFile]);

    useEffect(() => {
    const fetchTags = async () => {
      const response = await axiosInstance.get('/api/tags');
      const tags = await response.data;
      setAllTags(tags);
      setLoading(false);
    };
    fetchTags();
  }, []);

  const handleAddTag = () => {
    const trimmed = tagInput.trim();
    if (trimmed && !tags.includes(trimmed)) {
      const next = [...tags, trimmed];
      setTags(next);
      setData('tags', next);
    }
    setTagInput('');
  };

  const handleRemoveTag = (tag: string) => {
    const next = tags.filter((t) => t !== tag);
    setTags(next);
    setData('tags', next);
  };

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    setSubmitting(true);

    await getCsrfToken();

    try {
      const formData = new FormData();
      formData.append('title', title);
      formData.append('content', content);
      formData.append('topic', topic);
      formData.append('_method', 'PUT');

      // tags[]
      tags.forEach((tag) => formData.append('tags[]', tag));

      if (imageFile) {
        formData.append('image', imageFile);
      }

      await axiosInstance.post(`/api/posts/${post.id}`, formData);
      toast.success('Post updated successfully');
      setTimeout(() => {
        router.visit(`/post/${post.id}`, {
        data: { flash: { success: 'Post updated successfully!' } },
        preserveState: true,
      });
      }, 2000);
    } catch (error: any) {
      if (error.response?.data?.errors) {
        console.error('Validation Errors:', error.response.data.errors);
        toast.error('Validation error — check your fields');
      } else {
        console.error('Error updating post', error);
        toast.error('Error updating post');
      }
    } finally {
      setSubmitting(false);
    }
  };

  return (
    <div className={`min-h-160 ${theme}`}>
      <div className="min-h-160 bg-[var(--bg-primary)] text-[var(--text-primary)]">
        <Navbar />
        <Header />
        <div className="w-full mx-auto flex justify-center items-center gap-4 md:gap-10 xl:gap-18 !mt-20">
          <main className="!p-8 mx-auto flex flex-col min-w-200 justify-center gap-8">
            <h1 className="text-3xl font-bold !mb-6">Edit Post</h1>

            <form onSubmit={handleSubmit} encType="multipart/form-data" className="!space-y-4">
              {/* Title */}
              <div>
                <label className="block !mb-3 font-medium">Title</label>
                <input
                  type="text"
                  value={title}
                  onChange={(e) => setTitle(e.target.value)}
                  className="w-full !p-2 rounded border border-[#5800FF] bg-[var(--bg-primary)]"
                />
              </div>

              {/* Content */}
              <div>
                <label className="block !mb-3 font-medium">Content</label>
                <textarea
                  value={content}
                  onChange={(e) => setContent(e.target.value)}
                  className="edittextarea w-full !p-2 rounded border border-[#5800FF] bg-[var(--bg-primary)]"
                  rows={1}
                />
              </div>

              {/* Image Upload */}
              <div>
                <label className="block !mb-3 font-medium">Image Upload</label>
                <input
                  type="file"
                  accept="image/*"
                  onChange={(e) => {
                    const file = e.target.files?.[0] || null;
                    setImageFile(file);
                    setData('image', file);
                  }}
                  className="w-full !p-2 rounded border border-[#5800FF] bg-[var(--bg-primary)]"
                />

                {previewUrl ? (
                  <div className="!mt-2">
                    <p className="text-sm !mb-3">New Image Preview:</p>
                    <img src={previewUrl} alt="Preview" className="max-w-xs rounded shadow" />
                  </div>
                ) : post.image_url ? (
                  <div className="!mt-2">
                    <p className="text-sm !mb-3">Current Image:</p>
                    <a
                      href={`/storage/${post.image_url.replace(/^uploads\//, '')}`}
                      target="_blank"
                      rel="noopener noreferrer"
                    >
                      <img src={post.image_url} alt="Current" className="max-w-xs rounded shadow" />
                    </a>
                  </div>
                ) : (
                  <p className="text-sm mt-1 text-gray-500">No image uploaded.</p>
                )}
              </div>

               {/* TAGS */}
              <div>
                <label className="block !mb-3 font-medium">Tags</label>
                <div className="flex gap-4 !mb-2">
                  <input
                    type="text"
                    value={tagInput}
                    onChange={(e) => setTagInput(e.target.value)}
                    onKeyDown={(e) => {
                      if (e.key === 'Enter') {
                        e.preventDefault();
                        handleAddTag();
                      }
                    }}
                    placeholder="Add tag and press Enter"
                    className="flex-grow !p-2 rounded border border-[#5800FF] bg-[var(--bg-primary)]"
                  />
                  <button
                    type="button"
                    onClick={handleAddTag}
                    className="bg-[#5800FF] text-white !px-3 !py-1 rounded hover:bg-[#E900FF] transition"
                  >
                    Add
                  </button>
                </div>

                {loading ? (
                  <p className="!mb-2">Loading tags...</p>
                ) : (
                  allTags && allTags.length > 0 && (
                    <div className="!mb-2 !flex !flex-wrap !gap-2">
                      {allTags
                        .filter(tag => !tags.includes(tag))
                        .map(tag => (
                          <button
                            key={tag}
                            type="button"
                            className="!bg-gray-200 !text-[#5800FF] !rounded !px-3 !py-1 !text-sm hover:!bg-[#5800FF] hover:!text-white transition"
                            onClick={() => {
                              const next = [...tags, tag];
                              setTags(next);
                              setData('tags', next);
                            }}
                          >
                            {tag}
                          </button>
                        ))}
                    </div>
                  )
                )}

                <div className="!mt-2 flex flex-wrap gap-4">
                  {tags.map((tag) => (
                    <div
                      key={tag}
                      className="flex items-center bg-[#5800FF] text-white rounded !px-3 !py-1 text-sm cursor-default select-none"
                    >
                      {tag}
                      <button
                        type="button"
                        onClick={() => handleRemoveTag(tag)}
                        className="!ml-2 !text-sm !text-white"
                      >
                        x
                      </button>
                    </div>
                  ))}
                </div>
              </div>

              {/* Topic */}
              <div>
                <label className="block !mb-3 font-medium">Topic</label>
                <input
                  type="text"
                  value={topic}
                  onChange={(e) => setTopic(e.target.value)}
                  className="w-full !p-2 rounded border border-[#5800FF] bg-[var(--bg-primary)]"
                />
              </div>

              {/* Save */}
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
