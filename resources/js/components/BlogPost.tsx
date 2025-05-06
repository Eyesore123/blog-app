import { useState, useEffect } from "react";
import { usePage, Link } from "@inertiajs/react";
import axiosInstance from "./axiosInstance"; // Assuming you have a separate axiosInstance setup
import { getCsrfToken } from "../components/auth"; // Adjust the import path as necessary
import '../../css/app.css';

export function BlogPost({ post }: { post: { title: string; content: string; topic: string; id: number } }) {
  const { auth } = usePage().props as unknown as { auth: { user: { name: string; token: string | null } | null } };
  const user = auth?.user;
  const isSignedIn = Boolean(user);
  const token = user?.token; // Assuming the token is available in the user object

  const [showComments, setShowComments] = useState(false);
  const [comments, setComments] = useState<{ _id: string; authorName: string; content: string }[]>([]);
  const [newComment, setNewComment] = useState("");
  const [submitting, setSubmitting] = useState(false);

  // Fetch comments when the component mounts
  useEffect(() => {
    async function fetchComments() {
      try {
        const response = await axiosInstance.get(`api/comments/${post.id}`);
        setComments(response.data);
      } catch (error) {
        console.error('Failed to fetch comments', error);
        console.log('Error loading comments');
      }
    }

    fetchComments();
  }, [post.id]);

  // Handle submitting a comment
  async function handleSubmitComment(e: React.FormEvent) {
    e.preventDefault();
    if (!newComment) return;

    const newCommentData = {
      post_id: post.id,
      content: newComment,
    };

    setSubmitting(true);

    // Ensure CSRF token is set before making the request
    await getCsrfToken(); // This will set the CSRF token for the next request

    try {
      const response = await axiosInstance.post(
        '/api/comments',
        newCommentData,
      );
      setComments([...comments, response.data]);
      setNewComment(""); // Clear the input field
    } catch (error) {
      console.error('Failed to post comment', error);
      alert('Error posting comment');
    } finally {
      setSubmitting(false);
    }
  }

  return (
    <article className="rounded-lg bg-[#5800FF]/5 !p-6 md:!w-300">
      <h2 className="text-2xl font-bold flex justify-start !mb-10">{post.title}</h2>
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
