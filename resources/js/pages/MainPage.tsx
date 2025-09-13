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
import TagComponent from '@/components/Tags';
import { WebsiteAnalyzerLink } from '@/components/WebsiteAnalyzerLink';
import { PortfolioLink } from '@/components/PortfolioLink';
import { RssSubscribeLink } from '@/components/RssSubscribeLink';
import { useTheme } from '../context/ThemeContext';
import { useEffect } from 'react';
import { useState } from 'react';
import { BookComponent } from '@/components/BookComponent';

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
  allPostsForFilter?: BlogPostType[];
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
    alert?: {
      type: 'error' | 'info' | 'success';
      message?: string;
    };
  };
  [key: string]: any;
}

export default function MainPage() {
  const { props } = usePage<PageProps>();
  const { theme } = useTheme();
  const [allTags, setAllTags] = useState<string[]>([]);
  const {
    posts,
    allPosts,
    allPostsForFilter = allPosts.data,
    topics,
    currentTopic,
    currentPage,
    hasMore,
    total,
    flash,
  } = props;

  const isAdmin = !!props.auth?.user?.is_admin;
  const [inputValue, setInputValue] = useState(currentPage + 1);

  useEffect(() => {
  const fetchTags = async () => {
    const response = await fetch('/api/tags');
    const tags = await response.json();
    setAllTags(tags);
  };
  fetchTags();
}, []);

  useEffect(() => {
    if (flash?.alert) {
      const { type, message } = flash.alert;
      if (message) {
        switch (type) {
          case 'error':
            toast.error(message);
            break;
          case 'info':
          case 'success':
          default:
            toast.success(message);
        }
      }
    }
  }, [flash]);

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
    if (page < 0 || (!hasMore && page > currentPage)) return;

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

  // Helper function to handle first page button click
const handleFirstPage = () => {
  handlePageChange(0);
};

// Helper function to handle last page button click
const handleLastPage = () => {
  handlePageChange(Math.ceil(total / 6) - 1);
};

// Helper function to handle input field navigation
const handlePageInput = (e) => {
  if (e.key === 'Enter') {
    const pageNumber = inputValue;
    if (pageNumber >= 1 && pageNumber <= Math.ceil(total / 6)) {
      handlePageChange(pageNumber - 1);
    }
  }
};

  return (
    <div className={`min-h-160 ${theme}`}>
      <div className="min-h-160 bg-[var(--bg-primary)] text-[var(--text-primary)]">
        <Navbar />
        <Header />
        
        <main className="!p-4 md:!p-8 !gap-1 main relative">
          <div className="flex flex-col lg:flex-row gap-4 md:gap-30 custom-2xl-gap items-center lg:items-start lg:!pt-1 2xl:!pt-1">
            <aside className="lg:block w-full md:!w-2/3 xl:!w-2/3 xl:!ml-10  xl:max-w-120 2xl:!ml-30 xl:!mr-10 !mb-8 lg:!mb-0 mx-auto">
              <div className={`lg:top-24 !space-y-4 md:!space-y-6 flexcontainer w-full lg:!w-80 xl:!w-120 bg-[var(--bg-primary)] lg:!mt-0 rounded-t-lg`}>
                <div className=" bg-[#5800FF]/10 !pl-4 md:!pl-10 !mb-0 !pr-4 !pb-10 2xl:!pl-6 bg-solid rounded-t-lg">
                  <h3 className="font-semibold !mb-4 !pt-4 md:!pt-8">About</h3>
                  <p className="opacity-80">
                    Hi there! This is my personal blog where I explore web development, design, tech, business, and marketing — along with the occasional reflection on life and what it means to live in society.
                  </p>
                </div>

                <div className="bg-[#5800FF]/10 !pl-4 md:!pl-10 !mb-0 !pr-4 !pb-8 2xl:!pl-6">
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

                <div className="bg-[#5800FF]/10 !p-4 !pl-4 md:!pl-10 2xl:!pl-6">
                  <SearchComponent posts={allPostsForFilter} />
                  <YearFilterComponent posts={allPostsForFilter} />
                  <ArchivesComponent />
                  <TagComponent
                    tags={allTags}
                    onTagClick={(tag) => router.visit(`/posts/tag/${tag}`)}
                  />
                  <RssSubscribeLink />
                  <RecentActivityFeed key="recent-activity-feed" />
                  <PortfolioLink />
                  <WebsiteAnalyzerLink />
                  <BookComponent />
                </div>
              </div>
            </aside>

            <div className="flex flex-col items-center lg:-translate-x-14">
              <div className="!space-y-6 md:!space-y-8">
                {posts.length === 0 ? (
                  <div className="text-center opacity-70 !mt-8 md:!mt-30">No blog posts yet.</div>
                ) : (
                  <>
                    {posts.map((post) => (
                      <div key={post.id} className="flex-1 flex-container justify-center items-center flex flex-col w-full">
                        <BlogPost post={{ ...post, _id: post.id.toString(), postUrl: '/posts/' + post.slug }} />
                      </div>
                    ))}
                    
                    
                    <div className="flex justify-center items-center lg:!ml-10 !gap-2 md:!gap-4 !mt-8 md:!mt-18 customdiv">
                      <button
                        onClick={handleFirstPage}
                        disabled={currentPage === 0}
                        className="paginationbutton !px-2 !py-1 md:!px-4 md:!py-2 bg-[#5800FF] text-white rounded hover:bg-[#E900FF] disabled:opacity-50 transition-colors text-sm md:text-base"
                      >
                        <img src="/first.svg" alt="First" />
                      </button>
                      <button
                        onClick={() => handlePageChange(currentPage - 1)}
                        disabled={currentPage === 0}
                        className="paginationbutton !px-2 !py-1 md:!px-4 md:!py-2 bg-[#5800FF] text-white rounded hover:bg-[#E900FF] disabled:opacity-50 transition-colors text-sm md:text-base"
                      >
                        Previous
                      </button>
                      <span className="text-sm md:text-base">
                        Page {currentPage + 1} of {Math.ceil(total / 6)}
                      </span>
                      
                     <input
                        type="number"
                        value={inputValue}
                        onChange={(e) => setInputValue(parseInt(e.target.value))}
                        onKeyDown={handlePageInput}
                        className={`w-10 md:w-12 !h-8 text-sm !pr-1 md:text-base text-center ${theme === 'dark' ? 'bg-primary' : 'bg-white'} border border-gray-300 rounded`}
                      />

                      <button
                        onClick={() => handlePageChange(currentPage + 1)}
                        disabled={!hasMore}
                        className="paginationbutton !px-2 !py-1 md:!px-4 md:!py-2 bg-[#5800FF] text-white rounded hover:bg-[#E900FF] disabled:opacity-50 transition-colors text-sm md:text-base"
                      >
                        Next
                      </button>
                      <button
                        onClick={handleLastPage}
                        disabled={currentPage === Math.ceil(total / 6) - 1}
                        className="paginationbutton !px-2 !py-1 md:!px-4 md:!py-2 bg-[#5800FF] text-white rounded hover:bg-[#E900FF] disabled:opacity-50 transition-colors text-sm md:text-base"
                      >
                        <img src="/last.svg" alt="Last" />
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
