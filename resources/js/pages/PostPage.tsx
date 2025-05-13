import React, { useState, useEffect } from 'react';
import { router } from '@inertiajs/react';
import { usePage } from '@inertiajs/react';
import { Toaster } from 'sonner';
import Header from '../components/Header';
import SearchComponent from '@/components/SearchComponent';
import '../../css/app.css';
import YearFilterComponent from '@/components/YearFilterComponent';
import ArchivesComponent from '@/components/ArchiveComponent';
import RecentActivityFeed from '@/components/RecentActivityFeed';
import { RssSubscribeLink } from '@/components/RssSubscribeLink';
import { Navbar } from '@/components/Navbar';
import { useTheme } from '../context/ThemeContext';
import axiosInstance from "../components/axiosInstance";
import { getCsrfToken } from "../components/auth";
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
}

const PostPage: React.FC<PostPageProps> = ({ post }) => {
  const { props } = usePage<PostPageProps>();
  const { theme } = useTheme();
  const allPosts: Post[] | AllPosts = props.allPosts ?? {};
  const { auth, topics, currentTopic } = props;
  const user = auth?.user;
  
  // Convert is_admin to boolean explicitly
  const isAdmin = user ? Boolean(user.is_admin) : false;
  const isSignedIn = Boolean(user);
  
  const [newComment, setNewComment] = useState("");
  const [submitting, setSubmitting] = useState(false);
  const [comments, setComments] = useState<Comment[]>([]);
  const { showAlert } = useAlert();
  const normalizedPosts = Array.isArray(allPosts)
  ? allPosts
  : allPosts?.data || [];

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
      }
    }
    fetchComments();
  }, [post.id]);

  async function handleSubmitComment(e: React.FormEvent) {
    e.preventDefault();
    if (!newComment) return;

    const newCommentData = {
      post_id: post.id,
      content: newComment,
    };

    setSubmitting(true);
    await getCsrfToken();

    try {
      const response = await axiosInstance.post('/api/comments', newCommentData);
      setComments([...comments, response.data]);
      setNewComment("");
    } catch (error) {
      console.error('Failed to post comment', error);
    } finally {
      setSubmitting(false);
    }
  }

  const handleTopicChange = (topic: string | null) => {
    const params = new URLSearchParams();
    if (topic) params.append('topic', topic);
    router.get('/', Object.fromEntries(params));
  };

  const handleDeleteComment = async (commentId: string) => {
    const { confirm } = useConfirm();
  const confirmed = await confirm({
    title: 'Delete Comment',
    message: 'Are you sure you want to delete this comment?',
    confirmText: 'Delete',
    cancelText: 'Cancel',
    type: 'danger'
  });
  
  if (!confirmed) return;
  
  // Use the URL format that matches your web.php route
  router.delete(`/api/comments/${commentId}`, {
    onSuccess: () => {
      console.log(`Comment ${commentId} deleted`);
      
      // Update the UI based on the comment's position in the thread
      const comment = comments.find(c => c._id === commentId);
      const hasReplies = comments.some(c => c.parent_id === commentId);
      const isReply = comment?.parent_id;
      
      if (hasReplies || isReply) {
        // If it's part of a conversation, mark as deleted but keep in the list
        setComments(comments.map(c =>
          c._id === commentId
            ? { ...c, deleted: true, content: "[Message removed by moderator]" }
            : c
        ));
        
        // Show success message
        showAlert('Comment has been removed', 'success');
      } else {
        // If it's a standalone comment, remove it completely
        setComments(comments.filter(c => c._id !== commentId));
        
        // Show success message
        showAlert('Comment has been deleted', 'success');
      }
    },
    onError: (errors) => {
      console.error('Failed to delete comment', errors);
      showAlert('Error deleting comment. Please try again.', 'error');
    }
  });
};


  // console.log('BlogPost image path:', post.image_url);

  return (
  <div className={`min-h-screen ${theme}`}>
    <div className="min-h-screen bg-[var(--bg-primary)] text-[var(--text-primary)]">
      <Navbar />
      <Header />
      <main className="!p-4 md:!p-8">
        {/* Change to flex-col on mobile, row on larger screens */}
        <div className="w-full !mx-auto flex flex-col lg:flex-row md:!gap-10">
          {/* Sidebar - full width on mobile, fixed width on desktop */}
          <aside className="w-full lg:w-120 !mb-8 lg:!mb-0 xl:!ml-10 2xl:!ml-40 overflow-y-auto xl:!-mt-24">
            <div className="lg:sticky lg:top-24 !space-y-4 md:!space-y-6 w-full lg:!w-80 xl:!w-120">
              <div className="rounded-lg bg-[#5800FF]/10 !p-4">
                <h3 className="font-semibold !mb-2">About This Post</h3>
                <p className="opacity-80">
                  This is a blog post about {post.topic || 'various topics'}.
                </p>
              </div>

              <div className="rounded-lg bg-[#5800FF]/10 !p-4">
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

              <div className="rounded-lg bg-[#5800FF]/10 !p-4">
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
                <RecentActivityFeed />
                <RssSubscribeLink />
              </div>
            </div>
          </aside>

          {/* Main content - full width on mobile, flex-1 on desktop */}
          <div className="flex-1 flex flex-col items-center w-full">
            <article className="rounded-lg bg-[#5800FF]/5 !p-6 md:!p-8 lg:!p-10 w-full md:w-[400px] lg:w-[600px] xl:w-[650px] 2xl:w-[830px] 2xl:!mr-30 !mb-8">
              <h2 className="text-3xl font-bold !mb-8 text-center lg:text-left">
                {post.title}
              </h2>

              {/* Image */}
              {post.image_url && (
                <div className="w-full flex flex-row justify-center items-center lg:justify-start lg:items-start !mb-6 md:!mb-20 !mt-4 md:!mt-40">
                  <img
                    src={post.image_url}
                    alt={post.title}
                    className="w-full md:w-100 lg:w-150 h-auto rounded-lg cursor-pointer hover:opacity-80"
                    onError={(e) => {
                      console.error('Image failed to load:', post.image_url);
                      e.currentTarget.style.display = 'none'; // Hide image if it fails to load
                    }}
                  />
                </div>
              )}

              {/* Post Content */}
              <div className="prose max-w-none opacity-90 !mb-8 text-sm md:text-base">
                {post.content}
              </div>

              {/* Comments Section */}
              <div className="!mt-10 !pt-6 border-t border-[#5800FF]/20">
                <h3 className="text-xl font-semibold !mb-4">
                  Comments ({comments.length})
                </h3>

                <div className="!mt-4 !space-y-4">
                  {comments.length > 0 ? (
                    comments.map((comment) => (
                      <div
                        key={comment._id}
                        className="bg-[#5800FF]/10 rounded-lg !p-4 shadow-sm"
                      >
                        <p className="font-medium text-sm">
                          {comment.authorName}
                        </p>
                        {comment.deleted ? (
                          <p className="opacity-60 italic text-sm">
                            [Message removed by moderator]
                          </p>
                        ) : (
                          <p className="opacity-80 text-sm">
                            {comment.content}
                          </p>
                        )}
                        <p className="text-xs opacity-60 italic">
                          {new Date(comment.createdAt).toLocaleString()}
                        </p>

                        {!comment.deleted && isAdmin && (
                          <button
                            onClick={() => handleDeleteComment(comment._id)}
                            className="text-red-500 text-xs hover:underline mt-2"
                          >
                            Delete
                          </button>
                        )}
                      </div>
                    ))
                  ) : (
                    <p className="text-sm opacity-60 italic">
                      No comments yet. Be the first!
                    </p>
                  )}

                  {/* Comment Form */}
                  {isSignedIn ? (
                    <form onSubmit={handleSubmitComment} className="!mt-6">
                      <textarea
                        placeholder="Write a comment..."
                        value={newComment}
                        onChange={(e) => setNewComment(e.target.value)}
                        className="w-full !p-3 rounded-lg border border-[#5800FF]/20 bg-[var(--bg-primary)] focus:outline-none focus:ring-2 focus:ring-[#5800FF]"
                      />
                      <button
                        type="submit"
                        disabled={!newComment || submitting}
                        className="!mt-4 !px-6 !py-2 bg-[#5800FF] text-white rounded-lg hover:bg-[#E900FF] disabled:opacity-50 transition-colors"
                      >
                        {submitting ? 'Posting...' : 'Post Comment'}
                      </button>
                    </form>
                  ) : (
                    <p className="text-sm opacity-70 italic">
                      <a
                        href="/login"
                        className="underline text-[#5800FF] hover:text-[#E900FF]"
                      >
                        Sign in to write a comment
                      </a>
                    </p>
                  )}
                </div>
              </div>
            </article>
          </div>
        </div>
      </main>
      <Toaster />
    </div>
  </div>
);
  
};

export default PostPage;
