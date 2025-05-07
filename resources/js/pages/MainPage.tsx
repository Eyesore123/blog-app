import { usePage } from '@inertiajs/react';
import { router } from '@inertiajs/react';
import { Toaster } from 'sonner';
import { BlogPost } from '../components/BlogPost';
import Header from '../components/Header';
import '../../css/app.css';
import { Navbar } from '@/components/Navbar';
import { useTheme } from '../context/ThemeContext';

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
  auth?: {
    user: {
      is_admin: boolean;
      name: string;
    } | null;
  };
  [key: string]: any;
}

export default function MainPage() {
  const { props } = usePage<PageProps>();
  const { theme } = useTheme();
  const { posts, topics, currentTopic, currentPage, hasMore, total } = props;

  const isAdmin = props.auth?.user?.is_admin ?? false;

  const handlePageChange = (page: number) => {
    const params = new URLSearchParams();
    if (currentTopic) params.append('topic', currentTopic);
    params.append('page', (page + 1).toString());
    router.get('/', Object.fromEntries(params));
  };

  const handleTopicChange = (topic: string | null) => {
    const params = new URLSearchParams();
    if (topic) params.append('topic', topic);
    router.get('/', Object.fromEntries(params));
  };

  const handleDeletePost = (postId: number) => {
    if (confirm('Are you sure you want to delete this post?')) {
      router.delete(`/posts/${postId}`, {
        onSuccess: () => {
          console.log(`Post ${postId} deleted`);
        },
      });
    }
  };

  return (
    <div className={`min-h-screen ${theme}`}>
      <div className="min-h-screen bg-[var(--bg-primary)] text-[var(--text-primary)]">
        <Navbar />
        <Header />
        <main className="!p-8">
          <div className="w-full !mx-auto flex md:!gap-10 xl:!gap-18">
            {/* Sidebar */}
            <aside className="!w-80 lg:!w-120 lg:!ml-50">
              <div className="sticky top-24 !space-y-6 !w-60 md:!w-80 !-ml-0  !xl:ml-0 lg:!w-100 xl:!w-120">
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
              </div>
            </aside>

            {/* Main content */}
            <div className="flex-1 justify-center items-center flex flex-col max-w-500">
              <div className="!space-y-8">
                {posts.length === 0 ? (
                  <div className="text-center opacity-70 !mt-30">No blog posts yet.</div>
                ) : (
                  <>
                    {posts.map((post) => (
                      <div key={post.id} className="relative">
                        <BlogPost post={{ ...post, _id: post.id.toString() }} />
                        {isAdmin && (
                          <button
                            onClick={() => handleDeletePost(post.id)}
                            className="absolute top-30 left-5 !px-3 !py-1 bg-red-600 text-white rounded hover:bg-red-800 transition-colors"
                          >
                            Delete
                          </button>
                        )}
                      </div>
                    ))}
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
