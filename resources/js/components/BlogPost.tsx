import { useState, useEffect, useRef } from "react";
import { usePage, Link } from "@inertiajs/react";
import { Head } from '@inertiajs/react';
import { router } from '@inertiajs/react';
import axiosInstance from "./axiosInstance";
import { getCsrfToken } from "../components/auth";
import '../../css/app.css';
import { useAlert } from "../context/AlertContext";
import { useConfirm } from "@/context/ConfirmationContext";
import ShareButtons from "./ShareButtons";
import { Helmet } from "react-helmet-async";
import ReactMarkdown from 'react-markdown';

interface Post {
  title: string;
  content: string;
  topic: string;
  id: number;
  _id?: string;
  image_url?: string | null;
  slug?: string;
  author?: string;
  created_at: string;
  updated_at?: string;
  postUrl?: string;
}

interface Comment {
  _id: string;
  authorName: string;
  content: string;
  createdAt: string;
  image?: string;
  parent_id?: string | null;
  deleted?: boolean;
  user_id: number;
  edited?: boolean;
}

interface AuthUser {
  name: string;
  token: string | null;
  is_admin: number | boolean;
  id: number;
}

interface PageProps {
  [key: string]: any
  auth: {
    user: AuthUser | null;
  };
  seo: {
    title: string;
    description: string;
    keywords?: string;
    image?: string;
    url: string;
  };
}

interface BlogPostProps {
  post: Post;
  isPostPage?: boolean;
}


export function BlogPost({ post, isPostPage = false }: BlogPostProps) {
  const { auth, seo } = usePage<PageProps>().props;
  const seoProps = { ...seo };
  const user = auth.user;
  const isSignedIn = Boolean(user);
  const token = user?.token;
  // Convert is_admin to boolean explicitly
  const isAdmin = user ? Boolean(user.is_admin) : false;
  const [showComments, setShowComments] = useState(false);
  const [replyingTo, setReplyingTo] = useState<string | null>(null);
   const [message, setMessage] = useState(""); // To store the message from backend
  const [newComment, setNewComment] = useState("");
  const [submitting, setSubmitting] = useState(false);
  const { showAlert } = useAlert();
  const { confirm } = useConfirm();
  const [comments, setComments] = useState<Comment[]>([]);
  // const [remainingComments, setRemainingComments] = useState<number | null>(null);
  const [editingCommentId, setEditingCommentId] = useState<string | null>(null);
const editCommentRef = useRef<HTMLTextAreaElement>(null);

  // Refs for uncontrolled inputs
  const replyInputRef = useRef<HTMLTextAreaElement>(null);
  const mainCommentRef = useRef<HTMLTextAreaElement>(null);

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

    const handleDeletePost = async (postId: number) => {
  // Use the custom confirm dialog
  const confirmed = await confirm({
    title: 'Delete Post',
    message: 'Are you sure you want to delete this post? This action cannot be undone.',
    confirmText: 'Delete',
    cancelText: 'Cancel',
    type: 'danger'
  });
  
  if (confirmed) {
    router.delete(`/posts/${postId}`, {
      onSuccess: () => {
        console.log(`Post ${postId} deleted`);
        showAlert('Post deleted successfully', 'success');
      },
      onError: () => {
        showAlert('Failed to delete post', 'error');
      }
    });
  }
};

//     useEffect(() => {
//   async function fetchRemainingComments() {
//     try {
//       const response = await axiosInstance.get('/api/comments/remaining');
//       setRemainingComments(response.data.remaining);
//     } catch (error) {
//       console.error('Failed to fetch remaining comments count', error);
//     }
//   }

//   if (isSignedIn) {
//     fetchRemainingComments();
//   }
// }, [isSignedIn]);


  async function handleSubmitComment(e: React.FormEvent) {
      e.preventDefault();
      if (!mainCommentRef.current || !mainCommentRef.current.value.trim()) return;
      if (!post?.id) {
        showAlert('Post ID missing', 'error');
        return;
      }

      const commentContent = mainCommentRef.current.value;
      const newCommentData = {
        post_id: post.id,
        content: commentContent,
      };

      setSubmitting(true);
      await getCsrfToken(); // Ensure you handle CSRF token correctly

      try {
      const response = await axiosInstance.post('/api/comments', newCommentData);
      setComments(prev => [...prev, response.data]);
      setMessage(response.data.message || "Comment posted successfully"); // Set message from backend
      mainCommentRef.current.value = "";

      setTimeout(() => {
        setMessage("");
      }, 5000);
    } catch (error) {
      console.error('Failed to post comment', error);
      setMessage("Error posting comment. We apologize for the inconvenience. If you hit the limit of 10 messages per day, please try again tomorrow.");

      setTimeout(() => {
        setMessage("");
      }, 5000);
    } finally {
      setSubmitting(false);
    }
  }

const handleImageClick = (e: React.MouseEvent<HTMLImageElement>) => {
  if (isPostPage) {
    // If on PostPage, toggle fullscreen
    const image = e.currentTarget;
    if (document.fullscreenElement === image) {
      document.exitFullscreen();
    } else {
      image.requestFullscreen();
    }
  } else {
    // Otherwise, navigate to the post page
    goToPostPage();
  }
};

  async function handleSubmitReply(e: React.FormEvent, parentId: string) {
    e.preventDefault();
    if (!replyInputRef.current || !replyInputRef.current.value.trim()) return;
    
    const replyContent = replyInputRef.current.value;
    const replyData = {
      post_id: post.id,
      content: replyContent,
      parent_id: parentId,
    };
    
    setSubmitting(true);
    await getCsrfToken();
    
    try {
      const response = await axiosInstance.post('/api/comments', replyData);
      setComments([...comments, response.data]);
      setReplyingTo(null);
    } catch (error) {
      console.error('Failed to post reply', error);
      showAlert('Error posting reply', 'error');
    } finally {
      setSubmitting(false);
      // setRemainingComments(prev => prev !== null ? Math.max(prev - 1, 0) : prev);
    }
  }

  // Helper function to check if a comment chain should be removed
  const shouldRemoveCommentChain = (commentId: string, updatedComments: Comment[]) => {
    // Get the root comment of the chain
    const rootCommentId = getRootCommentId(commentId, updatedComments);
    
    // Get all comments in this chain
    const chainComments = getCommentChain(rootCommentId, updatedComments);
    
    // Check if all comments in the chain are deleted
    return chainComments.length > 0 && chainComments.every(c => c.deleted);
  };

  // Get the root comment ID of a comment chain
  const getRootCommentId = (commentId: string, commentsList: Comment[]): string => {
    const comment = commentsList.find(c => c._id === commentId);
    if (!comment || !comment.parent_id) {
      return commentId; // This is already a root comment or not found
    }
    return getRootCommentId(comment.parent_id, commentsList);
  };

  // Get all comments in a chain (root + all replies)
  const getCommentChain = (rootId: string, commentsList: Comment[]): Comment[] => {
    const result: Comment[] = [];
    
    // Add the root comment
    const rootComment = commentsList.find(c => c._id === rootId);
    if (rootComment) {
      result.push(rootComment);
    }
    
    // Add all replies (direct and indirect)
    const addReplies = (parentId: string) => {
      const replies = commentsList.filter(c => c.parent_id === parentId);
      replies.forEach(reply => {
        result.push(reply);
        addReplies(reply._id);
      });
    };
    
    addReplies(rootId);
    return result;
  };

  const handleDeleteComment = async (commentId: string) => {
  const confirmed = await confirm({
    title: 'Delete Comment',
    message: 'Are you sure you want to delete this comment?',
    confirmText: 'Delete',
    cancelText: 'Cancel',
    type: 'danger'
  });
  
  if (!confirmed) return;
  
  try {
    const response = await axiosInstance.delete(`/api/comments/${commentId}`)
      
      console.log(`Comment ${commentId} deleted`, response.data);
      
      const updatedComments = comments.map(c => 
        c._id === commentId 
          ? { ...c, deleted: true, content: "[Message removed by moderator]" } 
          : c
      );
      
      // Check if we should remove the entire chain
      if (shouldRemoveCommentChain(commentId, updatedComments)) {
        // Get the root comment ID
        const rootId = getRootCommentId(commentId, updatedComments);
        
        // Get all comments in the chain
        const chainCommentIds = getCommentChain(rootId, updatedComments).map(c => c._id);
        
        // Remove the entire chain
        setComments(updatedComments.filter(c => !chainCommentIds.includes(c._id)));
      } else {
        // Just update the single comment
        setComments(updatedComments);
      }
    } catch (error: any) {
      console.error('Failed to delete comment', error);
      
      if (error.response && error.response.status === 401) {
        showAlert('You need to be logged in to delete comments', 'error');
        window.location.href = '/login';
      } else {
        showAlert('Error deleting comment. Please try again.', 'error');
      }
    }
  };
  
  function goToPostPage() {
    if (post.slug) {
      router.visit(`/post/${post.slug}`);
    } else {
      // Use the post ID as a fallback when slug is missing
      router.visit(`/post/${post.id}`);
      console.log('Using post ID for navigation since slug is missing');
    }
  }
  
  const hasValidImageUrl = Boolean(
    post.image_url &&
    post.image_url !== 'null' &&
    post.image_url !== 'undefined'
  );

  const getReplies = (commentId: string) => {
    return comments.filter(comment => comment.parent_id === commentId);
  };

  // Get top-level comments
  const getTopLevelComments = () => {
    return comments.filter(comment => !comment.parent_id);
  };

  // Render a comment and its replies
const renderComment = (comment: Comment, level = 0) => {
  const maxNestingLevel = 10;
  const isReplying = replyingTo === comment._id;
  const isEditing = editingCommentId === comment._id;
  const replies = getReplies(comment._id);
  const isDeleted = comment.deleted;
  const isCommentOwner = Boolean(user && comment.user_id && user.id === comment.user_id);
  const hasReplies = comments.some(c => c.parent_id === comment._id);
  
  // Limit nesting on mobile
  const effectiveLevel = window.innerWidth < 768 ? Math.min(level, 3) : level;
  const indentClass = effectiveLevel > 0 ? `!ml-${Math.min(effectiveLevel * 4, 12)}` : '';
  
  async function handleEditComment(commentId: string) {
    if (!editCommentRef.current || !editCommentRef.current.value.trim()) return;
    
    const updatedContent = editCommentRef.current.value;
    
    setSubmitting(true);
    await getCsrfToken();
    
    try {
      const response = await axiosInstance.put(`/api/comments/${commentId}`, {
        content: updatedContent
      });
      
      // Update the comment in the local state
      setComments(comments.map(comment =>
        comment._id === commentId
          ? { ...comment, content: updatedContent, edited: true }
          : comment
      ));
      
      setEditingCommentId(null);
      showAlert('Comment updated successfully', 'success');
    } catch (error) {
      console.error('Failed to update comment', error);
      showAlert('Error updating comment. Please try again.', 'error');
    } finally {
      setSubmitting(false);
    }
  }
  
  // Add this function to handle user comment deletion
  async function handleUserDeleteComment(commentId: string) {
    // Check if the comment has replies
    const hasReplies = comments.some(c => c.parent_id === commentId);
    
    if (hasReplies) {
      showAlert('Cannot delete a comment with replies', 'warning');
      return;
    }
    
    const confirmed = await confirm({
      title: 'Delete Comment',
      message: 'Are you sure you want to delete your comment?',
      confirmText: 'Delete',
      cancelText: 'Cancel',
      type: 'warning'
    });
    
    if (!confirmed) return;
    
    try {
      await axiosInstance.delete(`/api/comments/${commentId}`);
      
      // Remove the comment from the UI
      setComments(comments.filter(c => c._id !== commentId));
      showAlert('Your comment has been deleted', 'success');
    } catch (error) {
      console.error('Failed to delete comment', error);
      showAlert('Error deleting comment. Please try again.', 'error');
    }
  }

  // Helper function for meta description, use in production:

  // const plainTextContent = post.content.replace(/<[^>]+>/g, '');
  // const description = plainTextContent.length > 150
  // ? plainTextContent.slice(0, 147) + '...'
  // : plainTextContent;

//   console.log('isDeleted:', isDeleted);
// console.log('level:', level);
// console.log('maxNestingLevel:', maxNestingLevel);
// console.log('isSignedIn:', isSignedIn);
// console.log('isCommentOwner:', isCommentOwner);
// console.log('isEditing:', isEditing);
// console.log('isAdmin:', isAdmin);
  
  return (
    <div key={comment._id} className={`bg-[#5800FF]/${10 - Math.min(level, 5) * 2} rounded !p-2 md:!p-3 ${indentClass}`}>
      <p className="font-medium text-xs md:text-sm">{comment.authorName}</p>
      
      {isDeleted ? (
        <p className="opacity-60 italic text-xs md:text-sm">[Message removed by moderator]</p>
      ) : isEditing ? (
        <form onSubmit={(e) => { e.preventDefault(); handleEditComment(comment._id); }} className="!mt-2">
          <textarea
            ref={editCommentRef}
            className="w-full !p-2 h-20 rounded border border-[#5800FF]/20 focus:border-[#5800FF] focus:ring-1 focus:ring-[#5800FF] outline-none text-xs md:text-sm"
            rows={3}
            defaultValue={comment.content}
          />
          <div className="flex gap-2 !mt-1">
            <button
              type="submit"
              disabled={submitting}
              className="!px-2 !py-1 md:!px-3 md:!py-1 bg-[#5800FF] text-white rounded hover:bg-[#E900FF] disabled:opacity-50 transition-colors text-[10px] md:text-sm"
            >
              {submitting ? "Saving..." : "Save Changes"}
            </button>
            <button
              type="button"
              onClick={() => setEditingCommentId(null)}
              className="!px-2 !py-1 md:!px-3 md:!py-1 border border-[#5800FF]/20 rounded hover:bg-[#5800FF]/10 text-[10px] md:text-sm"
            >
              Cancel
            </button>
          </div>
        </form>
      ) : (
        <div>
          <p className="opacity-80 break-words !max-w-full md:!max-w-240 overflow-wrap-anywhere h-auto text-xs md:text-sm">
            {comment.content}
          </p>
          {comment.edited && (
            <p className="text-[10px] opacity-60 italic">(edited)</p>
          )}
        </div>
      )}
      
      {/* Only show timestamp when not editing */}
      {!isEditing && (
        <p className="text-[10px] md:text-xs opacity-60 italic">{new Date(comment.createdAt).toLocaleString()}</p>
      )}
      
      <div className="flex gap-2 !mt-1 md:!mt-2">
        {/* Reply button - show for non-deleted comments if user is signed in */}
        {!isDeleted && level < maxNestingLevel && isSignedIn && (
          <button
            onClick={() => {
              setReplyingTo(comment._id);
              setEditingCommentId(null); // Reset editingCommentId when entering reply mode
            }}
              className="text-[10px] md:text-xs text-[#E900FF] hover:underline"
          >
            Reply
          </button>
        )}
        
        {/* Edit button - show for comment owner if not deleted */}
        {!isDeleted && isCommentOwner && !isEditing && (
          <button
            onClick={() => {
              setEditingCommentId(comment._id);
              setReplyingTo(null);
            }}
            className="text-[10px] text-[#FFC600] md:text-xs hover:underline"
          >
            Edit
          </button>
        )}
        
        {/* Delete button - show for comment owner if no replies */}
        {!isDeleted && isCommentOwner && !hasReplies && (
          <button
            onClick={() => handleUserDeleteComment(comment._id)}
            className="text-red-500 text-[10px] md:text-xs hover:underline"
          >
            Delete
          </button>
        )}
        
        {/* Admin delete button */}
        {!isDeleted && isAdmin && (
          <button
            onClick={() => handleDeleteComment(comment._id)}
            className="text-red-500 text-[10px] md:text-xs hover:underline"
          >
            Delete (Admin)
          </button>
        )}
      </div>
      
      {isReplying && (
        <form onSubmit={(e) => handleSubmitReply(e, comment._id)} className="!mt-2 md:!mt-3">
          <textarea
            ref={replyInputRef}
            placeholder={`Reply to ${comment.authorName}...`}
            className="w-full !p-2 rounded border border-[#5800FF]/20 focus:border-[#5800FF] focus:ring-1 focus:ring-[#5800FF] outline-none text-xs md:text-sm"
            rows={2}
            defaultValue=""
          />
          <div className="flex gap-2 !mt-1">
            <button
              type="submit"
              disabled={submitting}
              className="!px-2 !py-1 md:!px-3 md:!py-1 bg-[#5800FF] text-white rounded hover:bg-[#E900FF] disabled:opacity-50 transition-colors text-[10px] md:text-sm"
            >
              {submitting ? "Posting..." : "Post Reply"}
            </button>
            <button
              type="button"
              onClick={() => setReplyingTo(null)}
              className="!px-2 !py-1 md:!px-3 md:!py-1 border border-[#5800FF]/20 rounded hover:bg-[#5800FF]/10 text-[10px] md:text-sm"
            >
              Cancel
            </button>
          </div>
        </form>
      )}
      
      {replies.length > 0 && (
        <div className="!mt-2 md:!mt-3 !space-y-2 md:!space-y-3">
          {replies.map(reply => renderComment(reply, level + 1))}
        </div>
      )}
    </div>
  );
};

const postUrl = `/posts/${post.id}`;
  return (
  <>
    <Head>
      <link
        rel="alternate"
        type="application/rss+xml"
        title="RSS Feed for Joni's Blog"
        href="/feed"
        />
  </Head>
    <Helmet>
        <title>{seoProps.title}</title>
        <meta name="description" content={seoProps.description} />
        <meta name="keywords" content={seoProps.keywords || 'blog, post'} />
        <meta property="og:title" content={seoProps.title} />
        <meta property="og:description" content={seoProps.description} />
        <meta property="og:image" content={seoProps.image || '/default-image.jpg'} />
        <meta property="og:url" content={seoProps.url} />
      </Helmet>

    <article className="flex flex-col justify-center items-center lg:items-start lg:justify-start rounded-lg bg-[#5800FF]/5 !p-4 md:!pl-10 w-full md:!w-150 xl:!w-170 2xl:!w-220 !mb-6 md:!mb-10 xl:!ml-0">
      <h2
        className="text-xl md:text-2xl font-bold flex justify-start !mb-4 md:!mb-10 cursor-pointer hover:underline"
        onClick={goToPostPage}
      >
        {post.title}
      </h2>
      
     {hasValidImageUrl && post.image_url && (
  <div className="w-full flex flex-row justify-center items-center lg:justify-start lg:items-start !mb-6 md:!mb-20 !mt-4">
    <img
      src={post.image_url.replace('http://127.0.0.1:8000/', '')}
      alt={post.title}
      className="w-full md:w-100 lg:w-150 h-auto cursor-pointer hover:opacity-80"
      onClick={handleImageClick}
      onError={(e) => {
      console.error('Image failed to load:', post.image_url);
      console.log('Error details:', e);
      e.currentTarget.style.display = 'none';
    }}
    />
  </div>
)}
      
      <ReactMarkdown
  children={(post.content || '(No content)').replace(/\n/g, '\n\n')}
  components={{
    p: ({ node, ...props }) => <p className="mb-4" {...props} />,
    ul: ({ node, ...props }) => <ul className="list-disc ml-6 mb-4" {...props} />,
    ol: ({ node, ...props }) => <ol className="list-decimal ml-6 mb-4" {...props} />,
    li: ({ node, ...props }) => <li className="mb-2" {...props} />,
    strong: ({ node, ...props }) => <strong className="font-bold" {...props} />,
    em: ({ node, ...props }) => <em className="italic" {...props} />,
    blockquote: ({ node, ...props }) => (
      <blockquote className="border-l-4 border-gray-300 pl-4 italic text-gray-600 mb-4" {...props} />
    ),
    code: ({ inline, ...props }) =>
      inline ? (
        <code className="bg-gray-100 text-red-500 px-1 rounded" {...props} />
      ) : (
        <pre className="bg-gray-100 p-2 rounded overflow-x-auto">
          <code {...props} />
        </pre>
      ),
  }}
  skipHtml={false}
/>
      
      <div className="text-xs md:text-sm flex flex-col justify-center items-center  md:justify-start md:items-start text-gray-500 !mt-3 !pt-4 md:!pt-6 !space-y-1 border-t border-[#5800FF]/20">
        {post.created_at &&  (
          <div>
            Created: {new Date(post.created_at).toLocaleString()}
          </div>
        )}
        {post.updated_at && post.updated_at !== post.created_at && (
          <div className="italic">
            Updated: {new Date(post.updated_at).toLocaleString()}
          </div>
        )}

        <ShareButtons postUrl={postUrl} postTitle={post.title} />
        {isAdmin && (
          <button
            onClick={() => handleDeletePost(post.id)}
            className="right-4 md:top-10 md:right-30 !px-3 !py-1 bg-red-600 text-white rounded hover:bg-red-800 transition-colors !mt-4"
          >
            Delete
          </button>
        )}
      </div>
      
      <div className="!mt-4 md:!mt-6 !pt-4 md:!pt-6 border-t border-[#5800FF]/20 w-full">
  <button
    onClick={() => setShowComments(!showComments)}
    className="text-xs md:text-sm opacity-70 hover:opacity-100 transition-opacity"
  >
    {showComments ? "Hide Comments" : `Show Comments (${comments.length})`}
  </button>

  {showComments && (
    <div className="!mt-3 md:!mt-4 !space-y-3 md:!space-y-4 w-full">
      {comments.length > 0 ? (
        getTopLevelComments().map(comment => renderComment(comment))
      ) : (
        <p className="text-xs md:text-sm opacity-60 italic">No comments yet. Be the first!</p>
      )}
            
            {isSignedIn && replyingTo === null && (
              <form onSubmit={handleSubmitComment} className="!mt-4 md:!mt-6">
                <textarea
                  ref={mainCommentRef}
                  placeholder="Write a comment..."
                  className="w-full !p-2 rounded border border-[#5800FF]/20 focus:border-[#5800FF] focus:ring-1 focus:ring-[#5800FF] outline-none text-sm md:text-base"
                  rows={3}
                  defaultValue=""
                />
                <button
                  type="submit"
                  disabled={submitting}
                  className="!mt-2 !px-3 !py-1 md:!px-4 md:!py-2 bg-[#5800FF] text-white rounded hover:bg-[#E900FF] disabled:opacity-50 transition-colors text-sm md:text-base"
                >
                  {submitting ? "Posting..." : "Post Comment"}
                </button>
              </form>
            )}

            {/* {isSignedIn && remainingComments !== null && (
              <p className="text-xs text-gray-500 !mt-6">
                You can post {remainingComments} more comment{remainingComments !== 1 ? 's' : ''} today.
              </p>
            )} */}

            {/* Display the message from backend */}
                {message && (
              <div
                className={`message ${message.includes("Error") ? "error" : "success"} !p-2 rounded-md !mt-2 w-auto`}
                style={{
                  transition: "opacity 0.5s ease-in-out",
                }}
              >
                {message}
              </div>
            )}
            
            {!isSignedIn && (
              <p className="text-xs md:text-sm !mt-2 md:!mt-4">
                <Link href="/login" className="text-[#5800FF] hover:underline">
                  Sign in
                </Link>{" "}
                to leave a comment.
              </p>
            )}
          </div>
        )}
      </div>
    </article>
  </>
);

}
