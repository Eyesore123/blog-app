import React, { useEffect, useState } from 'react';
import ReactMarkdown from 'react-markdown';

export interface Post {
  id?: number;
  title: string;
  content: string;
  image_url?: string | null;
  created_at?: string;
  updated_at?: string;
}

interface AdminPostPreviewProps {
  posts?: Post[];
  previewPost?: Post | null; // Optional single preview
  onDeletePost?: (id: number) => void;
}

export default function AdminPostPreview({
  posts = [],
  previewPost,
  onDeletePost,
}: AdminPostPreviewProps) {
  const [currentPreview, setCurrentPreview] = useState(previewPost);

  // Update local state whenever previewPost changes
  useEffect(() => {
    if (previewPost) {
      setCurrentPreview(previewPost);
    } else {
      setCurrentPreview(null); // Explicitly set to null if no previewPost
    }
  }, [previewPost]);

  const renderPost = (post: Post) => (
    <article
      key={post.id || 'preview'}
      className="!mb-6 !p-4 bg-[#5800FF]/5 rounded-lg border border-[#5800FF]/10 w-full max-w-2xl"
    >
      <h3 className="text-xl font-semibold mb-2">{post.title || '(No title)'}</h3>

      {post.image_url ? (
        <img
          src={post.image_url}
          alt={post.title || 'Post image'}
          className="w-full max-w-md rounded !mb-10 !mt-10"
          onError={(e) => {
            console.error(`Failed to load image: ${post.image_url}`); // Log error for debugging
            e.currentTarget.style.display = 'none'; // Hide the image if it fails to load
          }}
        />
      ) : (
        <p className="text-gray-500 italic !mb-10">No image available.</p> // Fallback for missing images
      )}

      <div className="text-sm opacity-80 !mb-4 whitespace-pre-wrap">
        <ReactMarkdown>{post.content || '(No content)'}</ReactMarkdown>
      </div>

      {onDeletePost && post.id !== undefined && (
        <button
          onClick={() => onDeletePost(post.id!)}
          className="mt-4 bg-red-600 text-white px-3 py-1 rounded hover:bg-red-800 text-sm"
        >
          Delete
        </button>
      )}
    </article>
  );

  return (
    <div className="w-full flex flex-col justify-center items-center max-w-screen-lg !mt-12">
      <h2 className="text-2xl font-bold !mb-20 text-amber-300">
        {currentPreview ? 'Live Preview' : 'Post Previews'}
      </h2>

      {currentPreview
        ? renderPost(currentPreview)
        : posts.length === 0
        ? <p className="text-gray-500">No posts available.</p>
        : posts.map(renderPost)}
    </div>
  );
}