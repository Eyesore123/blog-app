import React, { useState, useEffect } from 'react';
import ReactMarkdown from 'react-markdown';
import { Post } from '@/types/post';

interface PostPanelProps {
  allPosts: Post[];
  [key: string]: any;
}

const PostPanel = ({ allPosts }: PostPanelProps) => {
  const [selectedPost, setSelectedPost] = useState<Post | null>(null);

  useEffect(() => {
    if (allPosts.length > 0) {
      setSelectedPost(allPosts[0]);
    }
  }, [allPosts]);

  if (!allPosts || allPosts.length === 0) {
    return <div className="text-center !py-12 text-gray-500 text-lg">No posts available</div>;
  }

  return (
    <div className="grid grid-cols-1 lg:grid-cols-12 gap-6 !p-6">
      {/* Left Panel */}
      <aside className="lg:col-span-3 bg-purple-50 border border-purple-100 !p-4 rounded-2xl shadow-sm max-h-[90vh] overflow-y-auto">
        <h2 className="text-xl font-semibold !mb-4 text-purple-700">📚 Posts</h2>
        <ul className="space-y-2">
          {allPosts.map((post) => (
            <li
              key={post.id}
              className={`cursor-pointer !px-3 !py-2 rounded-xl transition-all duration-200 font-medium ${
                selectedPost?.id === post.id
                  ? 'bg-purple-200 text-purple-900 shadow-inner'
                  : 'hover:bg-purple-100 text-purple-800'
              }`}
              onClick={() => setSelectedPost(post)}
              role="button"
              tabIndex={0}
              onKeyDown={(e) => e.key === 'Enter' && setSelectedPost(post)}
            >
              {post.title}
            </li>
          ))}
        </ul>
      </aside>

      {/* Middle Panel */}
      <main className="lg:col-span-6 bg-white !p-6 rounded-2xl shadow-md overflow-y-auto max-h-[90vh]">
        {selectedPost ? (
          <>
            <h2 className="text-2xl font-bold !mb-4 text-gray-800">{selectedPost.title}</h2>
            {selectedPost.image_url && (
              <img
                src={selectedPost.image_url}
                alt={selectedPost.title}
                className="w-full h-auto rounded-lg !mb-5 shadow"
              />
            )}
            <div className="prose prose-sm max-w-none !text-black !mb-4 !mt-4 dark:text-gray-200">
              <ReactMarkdown>{selectedPost.content}</ReactMarkdown>
            </div>
            <div className="mt-6 text-sm text-gray-500 border-t !pt-2">
              Created: {selectedPost.created_at && new Date(selectedPost.created_at).toLocaleString()}
              {selectedPost.updated_at && selectedPost.updated_at !== selectedPost.created_at && (
                <div className="italic !mt-1">
                  Updated: {new Date(selectedPost.updated_at).toLocaleString()}
                </div>
              )}
            </div>
          </>
        ) : (
          <p className="text-gray-500 italic">Select a post to view details.</p>
        )}
      </main>

      {/* Right Panel */}
      <section className="lg:col-span-3 bg-gray-50 border border-gray-100 !p-6 rounded-2xl shadow-sm">
        {selectedPost ? (
          <>
            <h2 className="text-xl font-semibold !mb-4 text-gray-700">🌍 Translation</h2>
            <p className="italic text-sm text-gray-500 opacity-70">
              Translation feature coming soon...
            </p>
          </>
        ) : (
          <p className="text-gray-500 italic">Select a post to view its details.</p>
        )}
      </section>
    </div>
  );
};

export default PostPanel;
