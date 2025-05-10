import { useState, useEffect, useRef } from "react";
import { usePage, Link } from "@inertiajs/react";
import { Head } from '@inertiajs/react';
import { router } from '@inertiajs/react';
import axiosInstance from "./axiosInstance";
import { getCsrfToken } from "../components/auth";
import '../../css/app.css';

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
}

interface Comment {
  _id: string;
  authorName: string;
  content: string;
  createdAt: string;
  image?: string;
  parent_id?: string | null;
  deleted?: boolean;
}

interface AuthUser {
  name: string;
  token: string | null;
  is_admin: boolean;
}

export function BlogPost({ post }: { post: Post }) {
  const { auth } = usePage().props as { auth: { user: AuthUser | null } };
  const user = auth?.user;
  const isSignedIn = Boolean(user);
  const token = user?.token;
  const [showComments, setShowComments] = useState(false);
  const [replyingTo, setReplyingTo] = useState<string | null>(null);
  const [newComment, setNewComment] = useState("");
  const [submitting, setSubmitting] = useState(false);
  const [comments, setComments] = useState<Comment[]>([]);
  
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

  async function handleSubmitComment(e: React.FormEvent) {
    e.preventDefault();
    if (!mainCommentRef.current || !mainCommentRef.current.value.trim()) return;
    
    const commentContent = mainCommentRef.current.value;
    const newCommentData = {
      post_id: post.id,
      content: commentContent,
    };
    
    setSubmitting(true);
    await getCsrfToken();
    
    try {
      const response = await axiosInstance.post('/api/comments', newCommentData);
      setComments([...comments, response.data]);
      mainCommentRef.current.value = "";
    } catch (error) {
      console.error('Failed to post comment', error);
      alert('Error posting comment');
    } finally {
      setSubmitting(false);
    }
  }

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
      alert('Error posting reply');
    } finally {
      setSubmitting(false);
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
    if (!confirm('Are you sure you want to delete this comment?')) return;
    
    try {
      // Use axios directly for API requests instead of Inertia router
      const response = await axiosInstance.delete(`/api/comments/${commentId}`);
      
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
    } catch (error) {
      console.error('Failed to delete comment', error);
      
      if (error.response && error.response.status === 401) {
        alert('You need to be logged in to delete comments');
        window.location.href = '/login';
      } else {
        alert('Error deleting comment. Please try again.');
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
    const replies = getReplies(comment._id);
    const isDeleted = comment.deleted;
    
    return (
      <div key={comment._id} className={`bg-[#5800FF]/${10 - level * 2} rounded !p-3 ${level > 0 ? `!ml-${level * 4}` : ''}`}>
        <p className="font-medium text-sm">{comment.authorName}</p>
        
        {isDeleted ? (
          <p className="opacity-60 italic text-sm">[Message removed by moderator]</p>
        ) : (
          <p className="opacity-80 break-words !max-w-240 overflow-wrap-anywhere h-auto">{comment.content}</p>
        )}
        
        <p className="text-xs opacity-60 italic">{new Date(comment.createdAt).toLocaleString()}</p>
        
        <div className="flex gap-2 !mt-2">
          {!isDeleted && level < maxNestingLevel && isSignedIn && (
            <button
              onClick={() => setReplyingTo(comment._id)}
              className="text-xs text-[#E900FF] hover:underline"
            >
              Reply
            </button>
          )}
          
          {!isDeleted && Boolean(user?.is_admin) && (
            <button
              onClick={() => handleDeleteComment(comment._id)}
              className="text-red-500 text-xs hover:underline"
            >
              Delete
            </button>
          )}
        </div>
        
        {isReplying && (
          <form onSubmit={(e) => handleSubmitReply(e, comment._id)} className="!mt-3">
            <textarea
              ref={replyInputRef}
              placeholder={`Reply to ${comment.authorName}...`}
              className="w-full !p-2 rounded border border-[#5800FF]/20 focus:border-[#5800FF] focus:ring-1 focus:ring-[#5800FF] outline-none"
              rows={2}
              defaultValue=""
            />
            <div className="flex gap-2 !mt-1">
              <button
                type="submit"
                disabled={submitting}
                className="!px-3 !py-1 bg-[#5800FF] text-white rounded hover:bg-[#E900FF] disabled:opacity-50 transition-colors text-sm"
              >
                {submitting ? "Posting..." : "Post Reply"}
              </button>
              <button
                type="button"
                onClick={() => setReplyingTo(null)}
                className="!px-3 !py-1 border border-[#5800FF]/20 rounded hover:bg-[#5800FF]/10 text-sm"
              >
                Cancel
              </button>
            </div>
          </form>
        )}
        
        {replies.length > 0 && (
          <div className="!mt-3 !space-y-3">
            {replies.map(reply => renderComment(reply, level + 1))}
          </div>
        )}
      </div>
    );
  };
  
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
    <article className="rounded-lg bg-[#5800FF]/5 !p-6 md:!w-260 md:!max-w-260 xl:!w-320 xl:!max-w-320 !mb-10">
      <h2
        className="text-2xl font-bold flex justify-start !mb-10 cursor-pointer hover:underline"
        onClick={goToPostPage}
      >
        {post.title}
      </h2>
      
      {hasValidImageUrl && (
        <div className="!mb-20 !mt-40">
          <img
            src={post.image_url}
            alt={post.title}
            className="w-100 lg:w-200 h-auto rounded-lg cursor-pointer hover:opacity-80"
            onClick={goToPostPage}
            onError={(e) => {
              console.error('Image failed to load:', post.image_url);
              e.currentTarget.style.display = 'none';
            }}
          />
        </div>
      )}
      
      <div className="prose max-w-none opacity-90 !mb-10">{post.content}</div>
      <div className="text-sm text-gray-500 !mt-3 !pt-6 !space-y-1 border-t border-[#5800FF]/20">
        {post.created_at && (
          <div>
            Created: {new Date(post.created_at).toLocaleString()}
          </div>
        )}
        {post.updated_at && post.updated_at !== post.created_at && (
          <div className="italic">
            Updated: {new Date(post.updated_at).toLocaleString()}
          </div>
        )}
      </div>
      
      <div className="!mt-6 !pt-6 border-t border-[#5800FF]/20">
        <button
          onClick={() => setShowComments(!showComments)}
          className="text-sm opacity-70 hover:opacity-100 transition-opacity"
        >
          {showComments ? "Hide Comments" : `Show Comments (${comments.length})`}
        </button>
        
        {showComments && (
          <div className="!mt-4 !space-y-4">
            {comments.length > 0 ? (
              getTopLevelComments().map(comment => renderComment(comment))
            ) : (
              <p className="text-sm opacity-60 italic">No comments yet. Be the first!</p>
            )}
            
            {isSignedIn && replyingTo === null && (
              <form onSubmit={handleSubmitComment} className="!mt-6">
                <textarea
                  ref={mainCommentRef}
                  placeholder="Write a comment..."
                  className="w-full !p-2 rounded border border-[#5800FF]/20 focus:border-[#5800FF] focus:ring-1 focus:ring-[#5800FF] outline-none"
                  rows={3}
                  defaultValue=""
                />
                <button
                  type="submit"
                  disabled={submitting}
                  className="!mt-2 !px-4 !py-2 bg-[#5800FF] text-white rounded hover:bg-[#E900FF] disabled:opacity-50 transition-colors"
                >
                  {submitting ? "Posting..." : "Post Comment"}
                </button>
              </form>
            )}
            
            {!isSignedIn && (
              <p className="text-sm !mt-4">
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
