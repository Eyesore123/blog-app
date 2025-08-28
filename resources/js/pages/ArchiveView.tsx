import React from 'react';
import { usePage } from '@inertiajs/react';
import { router } from '@inertiajs/react';
import { Toaster } from 'sonner';
import { BlogPost } from '../components/BlogPost';
import Header from '../components/Header';
import SearchComponent from '@/components/SearchComponent';
import YearFilterComponent from '@/components/YearFilterComponent';
import TagComponent from '@/components/Tags';
import { Navbar } from '@/components/Navbar';
import '../../css/app.css';
import { useTheme } from '../context/ThemeContext';
import { useState } from 'react';
import { useEffect } from 'react';

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
  const [allTags, setAllTags] = useState<string[]>([]);
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
  const [inputValue, setInputValue] = useState(currentPage + 1);

    useEffect(() => {
    const fetchTags = async () => {
      const response = await fetch('/api/tags');
      const tags = await response.json();
      setAllTags(tags);
    };
    fetchTags();
  }, []);

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

    // Experimental

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
      <main className="!p-4 md:!p-8 !gap-1">
          <div className="flex flex-col lg:flex-row gap-4 md:gap-6 xl:gap-10 custom-2xl-gap">
            <aside className="hidden lg:block w-full md:!w-2/3 xl:!w-2/3 xl:!ml-20 2xl:!ml-30 xl:max-w-120 xl:!mr-10 !mb-8 lg:!mb-0 mx-auto">
              <div className="lg:top-24 !space-y-4 md:!space-y-6 w-full lg:!w-80 xl:!w-120">
                <div className="rounded-lg bg-[#5800FF]/10 !p-4 2xl:!pl-6">
                <h3 className="font-semibold !mb-2">About</h3>
                <p className="opacity-80">
                  Viewing posts from <strong>{archiveYear}</strong>. Browse other years or topics to explore more.
                </p>
              </div>

              <div className="rounded-lg bg-[#5800FF]/10 !p-4 2xl:!pl-6 ">
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

              <div className="rounded-lg bg-[#5800FF]/10 !p-4 2xl:!pl-6">
                <SearchComponent posts={allPosts} />
                <YearFilterComponent posts={allPosts} />
                <TagComponent
                    tags={allTags}
                    onTagClick={(tag) => router.visit(`/posts/tag/${tag}`)}
                  />
              </div>
            </div>
          </aside>

          {/* Add: lg:-translate-x-2? */}
          <div className="flex flex-col items-center w-full lg:-translate-x-14">
            <div className="!space-y-8">
              <h2 className="text-2xl font-bold w-full !mb-10 lg:!ml-20 xl:!ml-14 text-center lg:text-left xl:!mt-6">
              Archive — Posts from {archiveYear}:
            </h2>
              {posts.data.length === 0 ? (
                <div className="text-center opacity-70 !mt-30">
                  No blog posts found for {archiveYear}.
                </div>
              ) : (
                <>
                  {posts.data.map((post) => {
                    const imageUrl = post.image_url || undefined;
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

                  <div className="flex justify-center items-center lg:!ml-10 !gap-2 md:!gap-4 !mt-8 md:!mt-18 customdiv">
                      <button
                        onClick={handleFirstPage}
                        disabled={currentPage === 0}
                        className="paginationbutton !px-3 !py-1 md:!px-4 md:!py-2 bg-[#5800FF] text-white rounded hover:bg-[#E900FF] disabled:opacity-50 transition-colors text-sm md:text-base"
                      >
                        <img src="/first.svg" alt="First" />
                      </button>
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
                        className="paginationbutton !px-3 !py-1 md:!px-4 md:!py-2 bg-[#5800FF] text-white rounded hover:bg-[#E900FF] disabled:opacity-50 transition-colors text-sm md:text-base"
                      >
                        Next
                      </button>
                      <button
                        onClick={handleLastPage}
                        disabled={currentPage === Math.ceil(total / 6) - 1}
                        className="paginationbutton !px-3 !py-1 md:!px-4 md:!py-2 bg-[#5800FF] text-white rounded hover:bg-[#E900FF] disabled:opacity-50 transition-colors text-sm md:text-base"
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
