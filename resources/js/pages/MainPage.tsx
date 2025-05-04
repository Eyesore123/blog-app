import { usePage, Link } from '@inertiajs/react';
import { router } from '@inertiajs/react';
import { Toaster } from 'sonner';
import SignOutButton from '@/components/SignOutButton';
import { CreatePost } from '../components/CreatePost';
import { BlogPost } from '../components/BlogPost';
import useTheme from '../hooks/useTheme';
import '../../css/app.css';

interface BlogPostType {
  id: number;
  title: string;
  content: string;
  topic: string;
  author: string;
  created_at: string;
}

interface PageProps {
    posts: BlogPostType[];
    topics: string[];
    currentTopic: string | null;
    currentPage: number;
    hasMore: boolean;
    total: number;
    user: { name: string } | null;
    [key: string]: any;
  }

export default function MainPage() {
  const { props } = usePage<PageProps>();
  const { posts, topics, currentTopic, currentPage, hasMore, total, user } = props;
  const { theme, toggleTheme } = useTheme();

  const handlePageChange = (page: number) => {
    const params = new URLSearchParams();
    if (currentTopic) params.append('topic', currentTopic);
    params.append('page', page.toString());
    router.get('/', Object.fromEntries(params));
  };
  
  const handleTopicChange = (topic: string | null) => {
    const params = new URLSearchParams();
    if (topic) params.append('topic', topic);
    router.get('/', Object.fromEntries(params));
  };

  return (
    <div className={`min-h-screen ${theme}`}>
      <div className="min-h-screen bg-[var(--bg-primary)] text-[var(--text-primary)]">
        <header className="sticky top-0 z-10 bg-[var(--nav-bg)] text-[var(--nav-text)] !p-4 flex justify-between items-center">
          <div className="flex items-center gap-4">
            {user && <span className="text-[#FFC600]">Welcome, {user.name}!</span>}
          </div>
          <div className="flex items-center gap-14">
            <button onClick={toggleTheme} className="p-2 hover:text-[#FFC600] transition-colors">
              {theme === 'dark' ? '🌞' : '🌙'}
            </button>
            {user ? (
              <SignOutButton />
            ) : (
              <Link
                href={route('login')}
                className="!px-4 !py-2 rounded bg-[#5800FF] text-white hover:bg-[#E900FF] transition-colors"
              >
                Sign In
              </Link>
            )}
          </div>
        </header>

        <div className="text-center !py-8 bg-gradient-to-r from-[#5800FF] via-[#E900FF] to-[#FFC600] text-white">
          <h1 className="text-4xl font-bold">Joni's Blog</h1>
        </div>

        <main className="!p-8">
          <div className="w-full  !mx-auto flex !gap-18">
            {/* Sidebar */}
            <aside className="!w-80 !lg:w-150 lg:!ml-50">
              <div className="sticky top-24 !space-y-6">
                <div className="rounded-lg bg-[#5800FF]/10 !p-4">
                  <h3 className="font-semibold !mb-2">About</h3>
                  <p className="opacity-80">
                    Welcome to my personal blog where I share my thoughts and experiments with web development, design, and technology.
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
                      <li>No topics available</li>
                    )}

                  </ul>
                </div>
              </div>
            </aside>

            {/* Main content */}
            <div className="flex-1">
              {user && <CreatePost />}
              <div className="!space-y-8">
                {posts.length === 0 ? (
                  <div className="text-center opacity-70">No blog posts yet.</div>
                ) : (
                  <>
                    {posts.map((post) => (
                    <BlogPost key={post.id} post={{ ...post, _id: post.id.toString() }} />
                  ))}
                    <div className="flex justify-center items-center gap-10 !mt-8">
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
