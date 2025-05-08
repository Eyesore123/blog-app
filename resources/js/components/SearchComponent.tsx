import React, { useState } from 'react';
import { router } from '@inertiajs/react';
interface Post {
  id: number;
  title: string;
  topic: string;
}

interface SearchComponentProps {
  posts: Post[];
}

const SearchComponent: React.FC<SearchComponentProps> = ({ posts = [] }) => {
    const [query, setQuery] = useState('');
    const filteredPosts = posts.filter((post) =>
        post.title.toLowerCase().includes(query.toLowerCase()) ||
        post.topic.toLowerCase().includes(query.toLowerCase())

    );

    return (
        <div className="rounded-lg !pb-4">
          <h3 className="font-semibold !mb-2">Search Posts</h3>
          <input
            type="text"
            value={query}
            onChange={(e) => setQuery(e.target.value)}
            placeholder="Search by title or topic"
            className="w-50 md:w-112 !p-2 !mb-2 rounded border border-[#5800FF]/30 focus:outline-none focus:ring-2 focus:ring-[#5800FF]/50"
          />
          {query.length > 0 && (
            <ul className="!space-y-1 max-h-48 overflow-y-auto">
              {filteredPosts.length > 0 ? (
                filteredPosts.map((post) => (
                  <li key={post.id}>
                    <button
                      onClick={() => router.visit(`/post/${post.id}`)}
                      className="w-full text-left !px-2 !py-1 rounded hover:bg-[#5800FF]/20"
                    >
                      <span className="font-medium">{post.title}</span>
                      <span className="text-xs opacity-60 block">{post.topic}</span>
                    </button>
                  </li>
                ))
              ) : (
                <li className="text-sm opacity-60 italic">No matches found</li>
              )}
            </ul>
          )}
        </div>
      );
    };

    export default SearchComponent;