import React, { useState, useEffect } from 'react';
import { router } from '@inertiajs/react';
import { usePage } from '@inertiajs/react';
import { Toaster } from 'sonner';
import Header from '../components/Header';
import SearchComponent from '@/components/SearchComponent';
import '../../css/app.css';
import { Navbar } from '@/components/Navbar';
import { useTheme } from '../context/ThemeContext';
import axiosInstance from "../components/axiosInstance";
import { getCsrfToken } from "../components/auth";

interface User {
  id: number;
  name: string;
  role: string;
  is_admin?: boolean;
}

interface Comment {
  _id: string;
  authorName: string;
  content: string;
  createdAt: string;
  image?: string;
}

interface Post {
  id: number;
  title: string;
  slug?: string;
  content: string;
  image_url: string | null;
  topic: string;
}

interface AuthUser {
  name: string;
  token: string | null;
  is_admin: boolean;
}

interface PostPageProps {
  [key: string]: any;
  post: Post;
  comments?: Comment[];
  user?: User | null;
  auth?: {
    user: AuthUser | null;
  };
  allPosts?: any[];
}

const PostPage: React.FC<PostPageProps> = ({ post }) => {
  const { props } = usePage<PostPageProps>();
  const { theme } = useTheme();
  const { auth, allPosts } = props;
  const user = auth?.user;
  const isAdmin = user?.is_admin ?? false;
  const isSignedIn = Boolean(user);
  
  const [newComment, setNewComment] = useState("");
  const [submitting, setSubmitting] = useState(false);
  const [comments, setComments] = useState<Comment[]>([]);

  useEffect(() => {
    async function fetchComments() {
      try {
        const response = await axiosInstance.get(`api/comments/${post.id}`);
        const formattedComments = response.data.map((comment: any) => ({
          ...comment,
          createdAt: new Date(comment.createdAt).toISOString(),
        }));
        setComments(formattedComments);
      } catch (error) {
        console.error('Failed to fetch comments', error);
      }
    }
    fetchComments();
  }, [post.id]);

  async function handleSubmitComment(e: React.FormEvent) {
    e.preventDefault();
    if (!newComment) return;

    const newCommentData = {
      post_id: post.id,
      content: newComment,
    };

    setSubmitting(true);
    await getCsrfToken();

    try {
      const response = await axiosInstance.post('/api/comments', newCommentData);
      setComments([...comments, response.data]);
      setNewComment("");
    } catch (error) {
      console.error('Failed to post comment', error);
      alert('Error posting comment');
    } finally {
      setSubmitting(false);
    }
  }

  async function handleDeleteComment(commentId: string) {
    if (!confirm("Are you sure you want to delete this comment?")) return;
    try {
      await axiosInstance.delete(`/api/comments/${commentId}`);
      setComments(comments.filter((comment) => comment._id !== commentId));
    } catch (error) {
      console.error('Failed to delete comment', error);
      alert('Error deleting comment');
    }
  }

  console.log('BlogPost image path:', post.image_url);

  return (
    <div className={`min-h-screen ${theme}`}>
      <div className="min-h-screen bg-[var(--bg-primary)] text-[var(--text-primary)]">
        <Navbar />
        <Header />
        <main className="!p-8">
          <div className="w-full !mx-auto flex md:!gap-10 xl:!gap-18">
            {/* Sidebar */}
            <aside className="!w-80 lg:!w-120 lg:!ml-50">
              <div className="sticky top-24 !space-y-6 !w-60 md:!w-80 !-ml-0 !xl:ml-0 lg:!w-100 xl:!w-120">
                <div className="rounded-lg bg-[#5800FF]/10 !p-4">
                  <h3 className="font-semibold !mb-2">About This Post</h3>
                  <p className="opacity-80">
                    This is a blog post about {post.topic || 'various topics'}.
                  </p>
                </div>
                <div className="rounded-lg bg-[#5800FF]/10 !p-4">
                  <h3 className="font-semibold !mb-2">Actions</h3>
                  <ul className="!space-y-1">
                    <li>
                      <button
                        onClick={() => router.visit('/')}
                        className="w-full text-left !px-2 !py-1 rounded hover:bg-[#5800FF]/20"
                      >
                        Back to All Posts
                      </button>
                    </li>
                    {isAdmin && (
                      <li>
                        <button
                          onClick={() => router.visit(`/post/${post.id}/edit`)}
                          className="w-full text-left !px-2 !py-1 rounded hover:bg-[#5800FF]/20"
                        >
                          Edit Post
                        </button>
                      </li>
                    )}
                  </ul>
                </div>
                <div className="rounded-lg bg-[#5800FF]/10 !p-4">
                <SearchComponent 
                  posts={Array.isArray(allPosts) ? allPosts : 
                        (allPosts && allPosts.data ? allPosts.data : [])} 
                />
              </div>
              </div>
            </aside>
            
            {/* Main content */}
            <div className="flex-1 justify-center items-center flex flex-col max-w-500">
              <article className="rounded-lg bg-[#5800FF]/5 !p-6 md:!w-260 !max-w-260">
                <h2 className="text-3xl font-bold !mb-10">{post.title}</h2>
                {/* Image */}
                {post.image_url && (
                  <div className="!mb-6">
                    <img
                      src={post.image_url.startsWith('/') ? post.image_url : `/${post.image_url}`}
                      alt={post.title}
                      className="w-100 lg:w-200 h-auto rounded-lg"
                      onError={(e) => {
                        console.error('Image failed to load:', post.image_url);
                        e.currentTarget.style.display = 'none';
                      }}
                    />
                  </div>
                )}

                <div className="prose max-w-none opacity-90 !mb-8">{post.content}</div>
                
                <div className="!mt-10 !pt-6 border-t border-[#5800FF]/20">
                  <h3 className="text-xl font-semibold !mb-4">Comments ({comments.length})</h3>
                  
                  <div className="!mt-4 !space-y-4">
                    {comments.length > 0 ? (
                      comments.map((comment) => (
                        <div key={comment._id} className="bg-[#5800FF]/10 rounded !p-3">
                          <p className="font-medium text-sm">{comment.authorName}</p>
                          <p className="opacity-80">{comment.content}</p>
                          <p className="text-xs opacity-60 italic">{new Date(comment.createdAt).toLocaleString()}</p>
                          
                          {isAdmin && (
                            <button
                              onClick={() => handleDeleteComment(comment._id)}
                              className="text-red-500 text-xs hover:underline"
                            >
                              Delete
                            </button>
                          )}
                        </div>
                      ))
                    ) : (
                      <p className="text-sm opacity-60 italic">No comments yet. Be the first!</p>
                    )}
                    
                    {isSignedIn ? (
                      <form onSubmit={handleSubmitComment} className="!mt-6">
                        <textarea
                          placeholder="Write a comment..."
                          value={newComment}
                          onChange={(e) => setNewComment(e.target.value)}
                          className="w-full !p-2 rounded border border-[#5800FF]/20 bg-[var(--bg-primary)]"
                        />
                        <button
                          type="submit"
                          disabled={!newComment || submitting}
                          className="!mt-2 !px-4 !py-2 bg-[#5800FF] text-white rounded hover:bg-[#E900FF] disabled:opacity-50 transition-colors"
                        >
                          {submitting ? 'Posting...' : 'Post Comment'}
                        </button>
                      </form>
                    ) : (
                      <p className="text-sm opacity-70 italic">
                        <a href="/login" className="underline text-[#5800FF] hover:text-[#E900FF]">
                          Sign in to write a comment
                        </a>
                      </p>
                    )}
                  </div>
                </div>
              </article>
            </div>
          </div>
        </main>
        <Toaster />
      </div>
    </div>
  );
};

export default PostPage;
