// components/Comments.tsx
import { useEffect, useState } from 'react';
import { router } from '@inertiajs/react';
import axiosInstance from './axiosInstance';
import { useTheme } from '@/context/ThemeContext';

interface Comment {
  id: number;
  content: string;
  post_title: string;
  post_slug: string;
  created_at: string;
}

interface CommentsProps {
  userId: number;
}

export default function Comments({ userId }: CommentsProps) {
  const [comments, setComments] = useState<Comment[]>([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);
  const { theme } = useTheme();

  useEffect(() => {
  async function fetchComments() {
    try {
      const response = await axiosInstance.get(`/api/comments/user/${userId}`);
      setComments(response.data);
      setLoading(false);
    } catch (error: any) {
      console.error('Failed to fetch comments:', error);
      setError(error.response?.data?.error || 'Failed to load comments.');
      setLoading(false);
    }
  }

  fetchComments();
}, [userId]);

if (loading) return <p className="text-gray-500 !mt-4">Loading comments...</p>;
if (error) return <p className="text-red-500 !mt-4">{error}</p>;

return (
  <div className="!mt-8 xl:!mt-6">
    <h2 className="text-xl font-bold !mb-8">My Comments</h2>
    {comments.length === 0 ? (
      <p className="text-gray-500">You haven't posted any comments yet.</p>
    ) : (
      <ul className="!space-y-4">
        {comments.map((comment) => (
          <li key={comment.id} className="border !p-4 rounded-lg">
            <p className="text-sm text-gray-600">
              On post:{" "}
              <span
                onClick={() =>
                  comment.post_slug
                    ? router.visit(`/post/${comment.post_slug}`)
                    : null
                }
                className="text-[#5800FF] hover:underline cursor-pointer"
              >
                {comment.post_title || "Unknown Post"}
              </span>
            </p>
            <p
              className={`mt-1 ${
                theme === "dark" ? "text-white" : "text-black"
              }`}
            >
              {comment.content || "No content available"}
            </p>
            <p
              className={`text-xs mt-1 ${
                theme === "dark" ? "text-white" : "text-gray-400"
              }`}
            >
              {comment.created_at
                ? new Date(comment.created_at).toLocaleString()
                : "Unknown date"}
            </p>
          </li>
        ))}
      </ul>
    )}
  </div>
);
  
}
