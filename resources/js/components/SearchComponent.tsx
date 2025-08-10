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

// Normalize string: lowercase, remove punctuation, collapse whitespace
function normalize(str: string): string {
  return str
    .toLowerCase()
    .replace(/[\p{P}$+<=>^`|~]/gu, '') // Remove punctuation
    .replace(/\s+/g, ' ') // Collapse whitespace
    .trim();
}

function getMatchScore(post: Post, query: string): number {
  const q = normalize(query);
  if (!q) return 0;
  const title = normalize(post.title);
  const topic = normalize(post.topic);

  // Exact match
  if (title === q || topic === q) return 100;

  // Starts with query
  if (title.startsWith(q) || topic.startsWith(q)) return 80;

  // Any word starts with query
  const titleWords = title.split(' ');
  const topicWords = topic.split(' ');
  if (
    titleWords.some(word => word.startsWith(q)) ||
    topicWords.some(word => word.startsWith(q))
  ) return 70;

  // Includes query
  if (title.includes(q) || topic.includes(q)) return 50;

  return 0;
}

const SearchComponent: React.FC<SearchComponentProps> = ({ posts = [] }) => {
  const [query, setQuery] = useState('');
  const filteredPosts = query.length === 0
    ? []
    : posts
        .map(post => ({
          post,
          score: getMatchScore(post, query)
        }))
        .filter(({ score }) => score > 0)
        .sort((a, b) => b.score - a.score)
        .map(({ post }) => post);

  return (
    <div className="rounded-lg !pb-4">
      <h3 className="font-semibold !mb-2">Search Posts</h3>
      <input
        type="text"
        value={query}
        onChange={(e) => setQuery(e.target.value)}
        placeholder="Search by title or topic"
        className="w-50 md:w-70 !p-2 rounded border border-[#5800FF]/30 focus:outline-none focus:ring-2 focus:ring-[#5800FF]/50"
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
            <li className="text-sm opacity-60 italic !mt-4 !ml-2">No matches found</li>
          )}
        </ul>
      )}
    </div>
  );
};

export default SearchComponent;