import { usePage } from '@inertiajs/react';
import { router } from '@inertiajs/react';
import { Toaster, toast } from 'sonner';
import { BlogPost } from '../components/BlogPost';
import Header from '../components/Header';
import '../../css/app.css';
import { Navbar } from '@/components/Navbar';
import SearchComponent from '@/components/SearchComponent';
import YearFilterComponent from '@/components/YearFilterComponent';
import ArchivesComponent from '@/components/ArchiveComponent';
import RecentActivityFeed from '@/components/RecentActivityFeed';
import { PortfolioLink } from '@/components/PortfolioLink';
import { RssSubscribeLink } from '@/components/RssSubscribeLink';
import { useTheme } from '../context/ThemeContext';
import { useEffect } from 'react';

interface BlogPostType {
  id: number;
  title: string;
  content: string;
  topic: string;
  author: string;
  created_at: string;
  image_url?: string | null;
  updated_at?: string;
  _id?: string;
  slug?: string;
  [key: string]: any;
}

interface PaginatedPosts {
  current_page: number;
  data: BlogPostType[];
}

interface PageProps {
  posts: BlogPostType[];
  allPosts: PaginatedPosts;
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
  flash?: {
    message?: string;
  };
  [key: string]: any;
}

export default function MainPage() {
  const { props } = usePage<PageProps>();
  const { theme } = useTheme();
  const { posts, allPosts, allPostsForFilter, topics, currentTopic, currentPage, hasMore, total, flash } = props;

  const isAdmin = Boolean(props.auth?.user?.is_admin);

  // Show flash message once on mount
  useEffect(() => {
  if (flash?.alert) {
    const { type, message } = flash.alert;
    if (message) {
      switch (type) {
        case 'error':
          toast.error(message);
          break;
        case 'info':
        default:
          toast.success(message);
      }
    }
  }
}, [flash]);

  // Helper to scroll smoothly to top, then call callback when scroll finished
  const scrollToTopAndThen = (callback: () => void) => {
    window.scrollTo({ top: 0, behavior: 'smooth' });

    const checkIfNearTop = () => {
      if (window.scrollY < 400) {
        callback();
      } else {
        requestAnimationFrame(checkIfNearTop);
      }
    };

    requestAnimationFrame(checkIfNearTop);
  };

  const handlePageChange = (page: number) => {
    scrollToTopAndThen(() => {
      const params = new URLSearchParams();
      if (currentTopic) params.append('topic', currentTopic);
      params.append('page', (page + 1).toString());
      router.get('/', Object.fromEntries(params));
    });
  };

  const handleTopicChange = (topic: string | null) => {
    scrollToTopAndThen(() => {
      const params = new URLSearchParams();
      if (topic) params.append('topic', topic);
      router.get('/', Object.fromEntries(params));
    });
  };

  return (
    <div className={`min-h-160 ${theme}`}>
      <div className="min-h-160 bg-[var(--bg-primary)] text-[var(--text-primary)]">
        <Navbar />
        <Header />
        <main className="!p-4 md:!p-8 !gap-1">
          <div className="w-full !mx-auto flex flex-col lg:flex-row md:!gap-0">
            <aside className="w-full lg:!w-120 lg:!ml-20 xl:!ml-30 !mr-10 !mb-8 lg:!mb-0">
              <div className="lg:top-24 !space-y-4 md:!space-y-6 w-full lg:!w-80 xl:!w-120">
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
                      <li className="!ml-2">No topics available</li>
                    )}
                  </ul>
                </div>

                <div className="rounded-lg bg-[#5800FF]/10 !p-4">
                  <SearchComponent posts={allPosts.data} />
                  <YearFilterComponent posts={allPostsForFilter ?? allPosts.data} />
                  <ArchivesComponent />
                  <RssSubscribeLink />
                  <RecentActivityFeed key="recent-activity-feed" />
                  <PortfolioLink />
                </div>
              </div>
            </aside>

            <div className="lg:flex-1 flex flex-col items-center lg:-translate-x-14">
              <div className="!space-y-6 md:!space-y-8">
                {posts.length === 0 ? (
                  <div className="text-center opacity-70 !mt-8 md:!mt-30">No blog posts yet.</div>
                ) : (
                  <>
                    {posts.map((post) => (
                      <div key={post.id} className="flex-1 justify-center items-center flex flex-col w-full">
                        <BlogPost post={{ ...post, _id: post.id.toString(), postUrl: '/posts/' + post.slug }} />
                      </div>
                    ))}
                    <div className="flex justify-center items-center !gap-4 md:!gap-10 !mt-8 md:!mt-18">
                      <button
                        onClick={() => handlePageChange(currentPage - 1)}
                        disabled={currentPage === 0}
                        className="paginationbutton !px-3 !py-1 md:!px-4 md:!py-2 bg-[#5800FF] text-white rounded hover:bg-[#E900FF] disabled:opacity-50 transition-colors text-sm md:text-base"
                      >
                        Previous
                      </button>
                      <span className="text-sm md:text-base">
                        Page {currentPage + 1} of {Math.ceil(total / 6)}
                      </span>
                      <button
                        onClick={() => handlePageChange(currentPage + 1)}
                        disabled={!hasMore}
                        className="paginationbutton !px-3 !py-1 md:!px-4 md:!py-2 bg-[#5800FF] text-white rounded hover:bg-[#E900FF] disabled:opacity-50 transition-colors text-sm md:text-base"
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
