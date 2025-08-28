import React, { useEffect, useRef } from 'react';
import { usePage, router } from '@inertiajs/react';
import { Toaster } from 'sonner';
import { BlogPost } from '@/components/BlogPost';
import Header from '@/components/Header';
import '../../../css/app.css';
import { Navbar } from '@/components/Navbar';
import SearchComponent from '@/components/SearchComponent';
import YearFilterComponent from '@/components/YearFilterComponent';
import ArchivesComponent from '@/components/ArchiveComponent';
import RecentActivityFeed from '@/components/RecentActivityFeed';
import { RssSubscribeLink } from '@/components/RssSubscribeLink';
import { useTheme } from '@/context/ThemeContext';

interface BlogPostType {
  id: number;
  title: string;
  content: string;
  topic: string;
  author: string;
  created_at: string;
  image_url?: string | null;
  updated_at?: string;
  slug?: string;
  tags?: { id: number; name: string }[];
}

interface PageProps {
  posts: BlogPostType[];
  activeTag?: string;
  topics?: string[];
  currentTopic?: string | null;
  allPosts?: any;
  currentPage?: number;
  hasMore?: boolean;
  total?: number;
  auth?: { user: { is_admin: boolean } | null };
  [key: string]: any;
}

export default function PostsIndex() {
  const { props } = usePage<PageProps>();
  const { theme } = useTheme();

  const {
    posts = [],
    activeTag = null,
    topics = [],
    currentTopic = null,
    allPosts,
    currentPage,
    hasMore,
    total,
    auth,
  } = props;

  const isAdmin = Boolean(auth?.user?.is_admin);

  const handleTopicChange = (topic: string | null) => {
    const params = new URLSearchParams();
    if (topic) params.append('topic', topic);
    router.get('/', Object.fromEntries(params));
  };

  const paginationButtonRef = useRef<HTMLButtonElement | null>(null);

  useEffect(() => {
  if (paginationButtonRef.current) {
    paginationButtonRef.current.addEventListener('click', function() {
      window.scrollTo({ top: 0, behavior: 'smooth' });
    });
  }
}, []);

  return (
    <div className={`min-h-160 ${theme}`}>
      <div className="min-h-160 bg-[var(--bg-primary)] text-[var(--text-primary)]">
        <Navbar />
        <Header />

        <main className="!p-4 md:!p-8 !gap-1">
          <div className="flex flex-col lg:flex-row gap-4 md:gap-6 custom-2xl-gap">
            <aside className="hidden lg:block w-full md:!w-2/3 xl:!w-2/3 xl:!ml-20 2xl:!ml-30 xl:max-w-120 xl:!mr-10 !mb-8 lg:!mb-0 mx-auto">
              <div className="lg:top-24 !space-y-4 md:!space-y-6 w-full lg:!w-80 xl:!w-120 flexcontainer">
                <div className="rounded-lg bg-[#5800FF]/10 !p-4 2xl:!pl-6">
                  <h3 className="font-semibold !mb-2">About</h3>
                  <p className="opacity-80">
                    Hi there! This is my personal blog where I explore web development, design, tech, business, and marketing — along with the occasional reflection on life and what it means to live in society.
                  </p>
                </div>

                <div className="rounded-lg bg-[#5800FF]/10 !p-4 2xl:!pl-6">
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
                    {topics.length > 0 ? (
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

                <div className="rounded-lg bg-[#5800FF]/10 !p-4 2xl:!pl-6">
                  <SearchComponent posts={allPosts?.data || posts} />
                  <YearFilterComponent posts={allPosts?.data || posts} />
                  <ArchivesComponent />
                  <RssSubscribeLink />
                  <RecentActivityFeed key="recent-activity-feed" />
                </div>
              </div>
            </aside>

            {/* Main content */}
            <div className="flex flex-col items-center">
              <div className="!space-y-6 md:!space-y-8 w-full max-w-2xl">
                {/* Tag header */}
                {activeTag && (
                  <h2 className="text-2xl font-bold w-full !mb-10 lg:!ml-20 xl:!ml-14 text-center lg:text-left !mt-2">
                    Posts tagged <span className="text-[#5800FF]">#{activeTag}:</span>
                  </h2>
                )}

                {/* Posts list */}
                {posts.length === 0 ? (
                  <div className="text-center opacity-70 !mt-8">
                    No posts found{activeTag ? ` for #${activeTag}` : ''}.
                  </div>
                ) : (
                  posts.map((post) => (
                    <div key={post.id} className="flex flex-col w-full">
                      <BlogPost
                        post={{
                          ...post,
                          _id: post.id.toString(),
                          postUrl: `/posts/${post.slug}`,
                        }}
                      />
                    </div>
                  ))
                )}

                {allPosts && (
                  <div className="flex justify-center items-center !gap-4 md:!gap-10 !mt-8 md:!mt-18">
                    <button
                      ref={paginationButtonRef}
                      onClick={() => router.get('/', { page: (currentPage || 0) })}
                      disabled={(currentPage || 0) === 0}
                      className="paginationbutton !px-3 !py-1 md:!px-4 md:!py-2 bg-[#5800FF] text-white rounded hover:bg-[#E900FF] disabled:opacity-50 transition-colors text-sm md:text-base"
                    >
                      Previous
                    </button>
                    <span className="text-sm md:text-base">
                      Page {(currentPage || 0) + 1} of {Math.ceil((total || posts.length) / 6)}
                    </span>
                    <button
                      ref={paginationButtonRef}
                      onClick={() => router.get('/', { page: (currentPage || 0) + 2 })}
                      disabled={!hasMore}
                      className="paginationbutton !px-3 !py-1 md:!px-4 md:!py-2 bg-[#5800FF] text-white rounded hover:bg-[#E900FF] disabled:opacity-50 transition-colors text-sm md:text-base"
                    >
                      Next
                    </button>
                  </div>
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
