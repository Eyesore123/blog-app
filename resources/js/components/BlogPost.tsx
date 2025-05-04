import { useState } from "react";
import '../../css/app.css';

export function BlogPost({ post }: { post: { title: string; content: string; topic: string; _id: string } }) {
  const [showComments, setShowComments] = useState(false);
  const [comments, setComments] = useState<{ _id: string; authorName: string; content: string }[]>([]);
  const [newComment, setNewComment] = useState("");
  const [authorName, setAuthorName] = useState("");

  async function handleSubmitComment(e: React.FormEvent) {
    e.preventDefault();
    if (!newComment || !authorName) return;

    const newCommentData = {
      _id: Date.now().toString(), // Generates a unique ID using the current timestamp
      postId: post._id,
      content: newComment,
      authorName,
    };

    // Simulating a new comment submission (Replace this with actual API call)
    setComments([...comments, newCommentData]);

    setNewComment("");
    setAuthorName("");
  }

  return (
    <article className="rounded-lg bg-[#5800FF]/5 !p-6 lg:max-w-300">
      <h2 className="text-2xl font-bold flex justify-start !mb-10">{post.title}</h2>
      <div className="prose max-w-none opacity-90">{post.content}</div>

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
              comments.map((comment) => (
                <div key={comment._id} className="bg-[#5800FF]/10 rounded !p-3">
                  <p className="font-medium text-sm">{comment.authorName}</p>
                  <p className="opacity-80">{comment.content}</p>
                </div>
              ))
            ) : (
              <p className="text-sm opacity-60 italic">No comments yet. Be the first!</p>
            )}

            <form onSubmit={handleSubmitComment} className="!mt-6">
              <input
                type="text"
                placeholder="Your name"
                value={authorName}
                onChange={(e) => setAuthorName(e.target.value)}
                className="w-full !p-2 rounded border border-[#5800FF]/20 bg-[var(--bg-primary)] !mb-2"
              />
              <textarea
                placeholder="Write a comment..."
                value={newComment}
                onChange={(e) => setNewComment(e.target.value)}
                className="w-full !p-2 rounded border border-[#5800FF]/20 bg-[var(--bg-primary)]"
              />
              <button
                type="submit"
                disabled={!newComment || !authorName}
                className="!mt-2 !px-4 !py-2 bg-[#5800FF] text-white rounded hover:bg-[#E900FF] disabled:opacity-50 transition-colors"
              >
                Post Comment
              </button>
            </form>
          </div>
        )}
      </div>
    </article>
  );
}
