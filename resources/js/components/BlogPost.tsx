import { useState, useEffect } from "react";
import { usePage, Link } from "@inertiajs/react";
import { Inertia } from '@inertiajs/inertia';
import axiosInstance from "./axiosInstance";
import { getCsrfToken } from "../components/auth";
import '../../css/app.css';

interface Post {
  title: string;
  content: string;
  topic: string;
  id: number;
  _id?: string;
  image_url?: string | null;
  slug?: string;
}

interface Comment {
  _id: string;
  authorName: string;
  content: string;
  createdAt: string;
  image?: string;
}

interface AuthUser {
  name: string;
  token: string | null;
  is_admin: boolean;
}

export function BlogPost({ post }: { post: Post }) {
  const { auth } = usePage().props as { auth: { user: AuthUser | null } };
  const user = auth?.user;
  const isSignedIn = Boolean(user);
  const token = user?.token;

  const [showComments, setShowComments] = useState(false);
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

  function goToPostPage() {
    if (post.slug) {
      Inertia.visit(`/post/${post.slug}`);
    } else {
      // Use the post ID as a fallback when slug is missing
      Inertia.visit(`/post/${post.id}`);
      console.log('Using post ID for navigation since slug is missing');
    }
  }
  
  
  return (
    <article className="rounded-lg bg-[#5800FF]/5 !p-6 md:!w-260 !max-w-260">
      <h2
        className="text-2xl font-bold flex justify-start !mb-10 cursor-pointer hover:underline"
        onClick={goToPostPage}
      >
        {post.title}
      </h2>

      {post.image_url && (
        <div className="!mb-20 !mt-40">
          <img
            src={post.image_url}
            alt={post.title}
            className="w-200 h-auto rounded-lg cursor-pointer hover:opacity-80"
            onClick={goToPostPage}
            onError={(e) => {
              console.error('Image failed to load:', post.image_url);
              e.currentTarget.style.display = 'none';
            }}
          />
        </div>
      )}

      <div className="prose max-w-none opacity-90">{post.content}</div>

      <div className="!mt-6 !pt-6 border-t border-[#5800FF]/20">
        <button
          onClick={() => setShowComments(!showComments)}
          className="text-sm opacity-70 hover:opacity-100 transition-opacity"
        >
          {showComments ? "Hide Comments" : `Show Comments (${comments.length})`}
        </button>

        {showComments && (
          <div className="!mt-4 !space-y-4">
            {comments.length > 0 ? (
              comments.map((comment) => (
                <div key={comment._id} className="bg-[#5800FF]/10 rounded !p-3">
                  <p className="font-medium text-sm">{comment.authorName}</p>
                  <p className="opacity-80">{comment.content}</p>
                  <p className="text-xs opacity-60 italic">{new Date(comment.createdAt).toLocaleString()}</p>

                  {Boolean(user?.is_admin) && (
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
                <Link href={route('login')} className="underline text-[#5800FF] hover:text-[#E900FF]">
                  Sign in to write a comment
                </Link>
              </p>
            )}
          </div>
        )}
      </div>
    </article>
  );
}
