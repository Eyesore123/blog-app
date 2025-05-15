import React from 'react';
import { usePage } from '@inertiajs/react';
import { router } from '@inertiajs/react';
import { Toaster } from 'sonner';
import { BlogPost } from '../components/BlogPost';
import Header from '../components/Header';
import SearchComponent from '@/components/SearchComponent';
import YearFilterComponent from '@/components/YearFilterComponent';
import { Navbar } from '@/components/Navbar';
import '../../css/app.css';
import { useTheme } from '../context/ThemeContext';

interface BlogPostType {
  id: number;
  title: string;
  content: string;
  topic: string;
  author?: string;
  created_at: string;
  image_url?: string | null;
  updated_at?: string;
  _id?: string;
  slug?: string;
  [key: string]: any;
}

interface PageProps {
  posts: {
    data: BlogPostType[];
    current_page: number;
    last_page: number;
    total: number;
  };
  allPosts: BlogPostType[];
  topics: string[];
  currentTopic: string | null;
  currentPage: number;
  hasMore: boolean;
  total: number;
  user: { name: string } | null;
  auth?: {
    user: {
      is_admin: boolean;
      name: string;
    } | null;
  };
  archiveYear: number;
  [key: string]: any;
}

export default function ArchiveView() {
  const { props } = usePage<PageProps>();
  const { theme } = useTheme();
  const {
    posts,
    allPosts,
    topics,
    currentTopic,
    currentPage,
    hasMore,
    total,
    archiveYear,
    auth,
  } = props;

  const isAdmin = auth?.user?.is_admin ?? false;

  const handlePageChange = (page: number) => {
    const params = new URLSearchParams();
    if (currentTopic) params.append('topic', currentTopic);
    params.append('page', (page + 1).toString());
    router.get(`/archives/${archiveYear}`, Object.fromEntries(params));
  };

  const handleTopicChange = (topic: string | null) => {
    const params = new URLSearchParams();
    if (topic) params.append('topic', topic);
    router.get(`/archives/${archiveYear}`, Object.fromEntries(params));
  };

  return (
  <div className={`min-h-screen ${theme}`}>
    <div className="min-h-screen bg-[var(--bg-primary)] text-[var(--text-primary)]">
      <Navbar />
      <Header />
      <main className="!p-4 md:!p-8 !gap-1">
        {/* Change to flex-col on mobile, row on larger screens */}
        <div className="w-full !mx-auto flex flex-col lg:flex-row md:!gap-10 xl:!gap-18">
          {/* Sidebar - full width on mobile, fixed width on desktop */}
          <aside className="w-full lg:!w-120 lg:!ml-50 !mb-8 lg:!mb-0">
            <div className="lg:sticky lg:top-24 !space-y-4 md:!space-y-6 w-full lg:!w-80 xl:!w-120">
              <div className="rounded-lg bg-[#5800FF]/10 !p-4">
                <h3 className="font-semibold !mb-2">About</h3>
                <p className="opacity-80">
                  Viewing posts from <strong>{archiveYear}</strong>. Browse other years or topics to explore more.
                </p>
              </div>

              <div className="rounded-lg bg-[#5800FF]/10 !p-4">
                <h3 className="font-semibold !mb-2">Topics</h3>
                <ul className="!space-y-1">
                  <li>
                    <button
                      onClick={() => handleTopicChange(null)}
                      className={`w-full text-left !px-2 !py-1 rounded ${
                        currentTopic === null ? 'bg-[#5800FF] text-white' : 'hover:bg-[#5800FF]/20'
                      }`}
                    >
                      All Topics
                    </button>
                  </li>
                  {topics && topics.length > 0 ? (
                    topics.map((topic) => (
                      <li key={topic}>
                        <button
                          onClick={() => handleTopicChange(topic)}
                          className={`w-full text-left !px-2 !py-1 rounded ${
                            currentTopic === topic ? 'bg-[#5800FF] text-white' : 'hover:bg-[#5800FF]/20'
                          }`}
                        >
                          {topic}
                        </button>
                      </li>
                    ))
                  ) : (
                    <li className="!ml-2">No topics available</li>
                  )}
                </ul>
              </div>

              <div className="rounded-lg bg-[#5800FF]/10 !p-4">
                <SearchComponent posts={allPosts} />
                <YearFilterComponent posts={allPosts} />
              </div>
            </div>
          </aside>

          {/* Main content - full width on mobile, flex-1 on desktop */}
          <div className="flex-1 flex flex-col items-center w-full">
            <div className="!space-y-8">
              <h2 className="text-2xl font-bold w-full !mb-10 xl:!ml-10 text-center lg:text-left">
              Archive — Posts from {archiveYear}:
            </h2>
              {posts.data.length === 0 ? (
                <div className="text-center opacity-70 !mt-30">
                  No blog posts found for {archiveYear}.
                </div>
              ) : (
                <>
                  {posts.data.map((post) => {
                    const imageUrl = post.image_url ? `${window.location.origin}/${post.image_url}` : null;
                    const slug = post.slug && post.slug !== 'slug' ? post.slug : undefined;
                    const author = post.author && post.author !== 'author' ? post.author : 'Unknown';

                    return (
                      <div key={post.id} className="relative">
                        <BlogPost
                          post={{
                            title: post.title,
                            content: post.content,
                            topic: post.topic,
                            id: post.id,
                            _id: post.id.toString(),
                            image_url: imageUrl,
                            slug,
                            author,
                            created_at: post.created_at,
                            updated_at: post.updated_at || post.created_at,
                            postUrl: slug ? `/post/${slug}` : `/post/${post.id}`,
                          }}
                        />
                      </div>
                    );
                  })}

                  <div className="flex justify-center items-center gap-10 !mt-18">
                    <button
                      onClick={() => handlePageChange(currentPage - 1)}
                      disabled={currentPage === 0}
                      className="!px-4 !py-2 bg-[#5800FF] text-white rounded hover:bg-[#E900FF] disabled:opacity-50 transition-colors"
                    >
                      Previous
                    </button>
                    <span>
                      Page {currentPage + 1} of {Math.ceil(total / 6)}
                    </span>
                    <button
                      onClick={() => handlePageChange(currentPage + 1)}
                      disabled={!hasMore}
                      className="!px-4 !py-2 bg-[#5800FF] text-white rounded hover:bg-[#E900FF] disabled:opacity-50 transition-colors"
                    >
                      Next
                    </button>
                  </div>
                </>
              )}
            </div>
          </div>
        </div>
      </main>
      <Toaster />
    </div>
  </div>
);
}
