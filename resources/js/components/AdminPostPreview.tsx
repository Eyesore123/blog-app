import React from 'react';

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
  previewPost?: Post; // optional single preview
  onDeletePost?: (id: number) => void;
}

export default function AdminPostPreview({
  posts = [],
  previewPost,
  onDeletePost,
}: AdminPostPreviewProps) {
  const renderPost = (post: Post) => (
    <article
      key={post.id || 'preview'}
      className="!mb-6 !p-4 bg-[#5800FF]/5 rounded-lg border border-[#5800FF]/10 w-full max-w-2xl"
    >
      <h3 className="text-xl font-semibold mb-2">{post.title || '(No title)'}</h3>

      {post.image_url && (
        <img
            src={post.image_url}
            alt={post.title}
            className="w-full max-w-md rounded !mb-10 !mt-10"
            onError={(e) => {
            e.currentTarget.style.display = 'none';
            }}
        />
        )}

      <div className="text-sm opacity-80 !mb-4 whitespace-pre-wrap">{post.content || '(No content)'}</div>

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
        {previewPost ? 'Live Preview' : 'Post Previews'}
      </h2>

      {previewPost
        ? renderPost(previewPost)
        : posts.length === 0
        ? <p className="text-gray-500">No posts available.</p>
        : posts.map(renderPost)}
    </div>
  );
}
