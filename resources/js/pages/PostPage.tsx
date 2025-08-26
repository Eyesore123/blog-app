import React, { useState, useEffect } from 'react';
import { router, usePage } from '@inertiajs/react';
import { Toaster, toast } from 'sonner';
import Header from '../components/Header';
import SearchComponent from '@/components/SearchComponent';
import '../../css/app.css';
import YearFilterComponent from '@/components/YearFilterComponent';
import ArchivesComponent from '@/components/ArchiveComponent';
import RecentActivityFeed from '@/components/RecentActivityFeed';
import { BlogPost } from '@/components/BlogPost';
import { RssSubscribeLink } from '@/components/RssSubscribeLink';
import { PortfolioLink } from '@/components/PortfolioLink';
import { Navbar } from '@/components/Navbar';
import SuggestedPosts from '@/components/SuggestedPosts';
import { useTheme } from '../context/ThemeContext';
import axiosInstance from "../components/axiosInstance";
import { useAlert } from '@/context/AlertContext';
import { useConfirm } from '@/context/ConfirmationContext';

interface User {
  id: number;
  name: string;
  role: string;
  is_admin?: boolean | number;
}

interface Comment {
  _id: string;
  authorName: string;
  content: string;
  parent_id?: string | null;
  createdAt: string;
  image?: string;
  deleted?: boolean;
}

interface Post {
  id: number;
  title: string;
  slug?: string;
  content: string;
  image_url: string | null;
  topic: string;
  created_at: string;
}

interface AllPosts {
  data?: Post[];
}

interface AuthUser {
  name: string;
  token: string | null;
  is_admin: boolean | number;
}

interface PostPageProps {
  [key: string]: any;
  post: Post;
  comments?: Comment[];
  user?: User | null;
  auth?: {
    user: AuthUser | null;
  };
  allPosts: Post[] | AllPosts;
  topics?: string[];
  currentTopic?: string | null;
  isPostPage?: boolean;
  flash?: {
    success?: string;
    error?: string;
    info?: string;
  };
}

const PostPage: React.FC<PostPageProps> = ({ post }) => {
  const { props } = usePage<PostPageProps>();
  const { theme } = useTheme();
  const allPosts: Post[] | AllPosts = props.allPosts ?? {};
  const { auth, topics, currentTopic, flash } = props;
  const user = auth?.user;
  const { confirm } = useConfirm();

  // Convert is_admin to boolean explicitly
  const isAdmin = user ? Boolean(user.is_admin) : false;
  const isSignedIn = Boolean(user);

  const [newComment, setNewComment] = useState("");
  const [submitting, setSubmitting] = useState(false);
  const [comments, setComments] = useState<Comment[]>([]);
  const { showAlert } = useAlert();

  const normalizedPosts = Array.isArray(allPosts) ? allPosts : allPosts?.data || [];

  useEffect(() => {
    async function fetchComments() {
      try {
        const response = await axiosInstance.get(`api/comments/${post.id}`);
        const formattedComments = response.data.map((comment: any) => ({
          ...comment,
          createdAt: new Date(comment.createdAt).toISOString(),
        }));
        setComments(formattedComments);
      } catch (error) {
        console.error('Failed to fetch comments', error);
        toast.error('Failed to load comments');
      }
    }
    fetchComments();
  }, [post.id]);

  // Show flash messages from server on page load
  useEffect(() => {
  if (flash?.success) {
    showAlert(flash.success, 'success');
  }
  if (flash?.error) {
    showAlert(flash.error, 'error');
  }
  if (flash?.info) {
    showAlert(flash.info, 'info');
  }
}, [flash]);


  const handleTopicChange = (topic: string | null) => {
    const params = new URLSearchParams();
    if (topic) params.append('topic', topic);
    router.get('/', Object.fromEntries(params));
  };

  // Handler to delete comment with flash messages
  const handleDeleteComment = async (commentId: string) => {
    try {
      await axiosInstance.delete(`/api/comments/${commentId}`);
      setComments(comments.filter((comment) => comment._id !== commentId));
      toast.success('Comment deleted successfully!');
    } catch (error) {
      console.error('Failed to delete comment', error);
      toast.error('Failed to delete comment. Please try again.');
    }
  };

  return (
    <div className={`min-h-160 ${theme}`}>
      <div className="min-h-160 bg-[var(--bg-primary)] text-[var(--text-primary)]">
        <Navbar />
        <Header />
        <main className="!p-4 md:!p-8 !gap-1">
          <div className="flex flex-col lg:flex-row gap-4 md:gap-30 custom-2xl-gap items-center lg:items-start lg:!pt-3">
          <aside className="lg:block w-full md:!w-2/3 xl:!w-2/3 xl:!ml-10 2xl:!ml-30 xl:max-w-120 xl:!mr-10 !mb-8 lg:!mb-0 mx-auto">
              <div className={`lg:top-24 !space-y-4 md:!space-y-6 flexcontainer w-full lg:!w-80 xl:!w-120 bg-[var(--bg-primary)] lg:!mt-0 rounded-t-lg 2xl:!pt-4`}>
                <div className="rounded-lg bg-[#5800FF]/10 !p-4 2xl:!pl-6">
                  <h3 className="font-semibold !mb-2">About This Post</h3>
                  <p className="opacity-80">
                    This is a blog post about {(post.topic || 'various topics').toLowerCase()}. You can find more posts on similar topics by using the search (type the name of the topic). You can also browse posts using the dropdown menu, archive page or the recommended posts section below each post. Clicking a tag on post returns all posts with the same tag.
                  </p>
                </div>

                <div className="rounded-lg bg-[#5800FF]/10 !p-4 2xl:!pl-6">
                  <h3 className="font-semibold !mb-2">Actions</h3>
                  <ul className="!space-y-1">
                    <li>
                      <button
                        onClick={() => router.visit('/')}
                        className="w-full text-left !px-2 !py-1 rounded hover:bg-[#5800FF]/20"
                      >
                        Back to All Posts
                      </button>
                    </li>
                    {isAdmin && (
                      <li>
                        <button
                          onClick={() => router.visit(`/post/${post.id}/edit`)}
                          className="w-full text-left !px-2 !py-1 rounded hover:bg-[#5800FF]/20"
                        >
                          Edit Post
                        </button>
                      </li>
                    )}
                  </ul>
                </div>

                <div className="rounded-lg bg-[#5800FF]/10 !p-4 2xl:!pl-6">
                  {topics && (
                    <div className="!mb-4">
                      <h3 className="font-semibold !mb-2">Topics</h3>
                      <ul className="!space-y-1">
                        <li>
                          <button
                            onClick={() => handleTopicChange(null)}
                            className={`w-full text-left !px-2 !py-1 rounded ${
                              currentTopic === null
                                ? 'bg-[#5800FF] text-white'
                                : 'hover:bg-[#5800FF]/20'
                            }`}
                          >
                            All Topics
                          </button>
                        </li>
                        {topics.map((topic) => (
                          <li key={topic}>
                            <button
                              onClick={() => handleTopicChange(topic)}
                              className={`w-full text-left !px-2 !py-1 rounded ${
                                currentTopic === topic
                                  ? 'bg-[#5800FF] text-white'
                                  : 'hover:bg-[#5800FF]/20'
                              }`}
                            >
                              {topic}
                            </button>
                          </li>
                        ))}
                      </ul>
                    </div>
                  )}
                  <SearchComponent posts={normalizedPosts} />
                  <YearFilterComponent posts={normalizedPosts} />
                  <ArchivesComponent />
                  <RecentActivityFeed key="recent-activity-feed" />
                  <RssSubscribeLink />
                  <PortfolioLink />
                </div>
              </div>
            </aside>

            <div className="flex flex-col items-center lg:-translate-x-14">
              <div className="!space-y-6 md:!space-y-8">
                <BlogPost
                  post={post}
                  comments={comments}
                  isAdmin={isAdmin}
                  onReply={(commentId: string) => {
               
                    // Add reply logic here
                  }}
                  onEdit={(commentId: string, newContent: string) => {
        
                  }}
                  onDelete={handleDeleteComment}
                  isPostPage={true}
                  showComments={true}
                />
                  {post.slug ? (
                <SuggestedPosts slug={post.slug} />
              ) : (
                <div>Check out my other posts!</div>
              )}
              </div>
            </div>
          </div>
        </main>
        <Toaster />
      </div>
    </div>
  );
};

export default PostPage;
