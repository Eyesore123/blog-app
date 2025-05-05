import { useState } from "react";

export function CreatePost() {
  const [title, setTitle] = useState("");
  const [content, setContent] = useState("");
  const [topic, setTopic] = useState("");

  async function handleSubmit(e: React.FormEvent) {
    e.preventDefault();
    if (!title || !content || !topic) return;

    const newPost = {
      title,
      content,
      published: true,
      topic,
    };

    // Simulate post creation here (Replace with actual API call)
    console.log("Creating post:", newPost);

    // Reset the form after submission
    setTitle("");
    setContent("");
    setTopic("");
  }

  return (
    <form onSubmit={handleSubmit} className="!space-y-4 !mt-8 w-200 flex justify-center items-center flex-col">
      <input
        type="text"
        placeholder="Post title"
        value={title}
        onChange={(e) => setTitle(e.target.value)}
        className="w-full !p-2 rounded border border-[#5800FF] bg-[var(--bg-primary)]"
      />
      <input
        type="text"
        placeholder="Topic (e.g., Web Development, Design, Programming)"
        value={topic}
        onChange={(e) => setTopic(e.target.value)}
        className="w-full !p-2 rounded border border-[#5800FF] bg-[var(--bg-primary)]"
      />
      <textarea
        placeholder="Write your post..."
        value={content}
        onChange={(e) => setContent(e.target.value)}
        className="w-full h-100 !p-2 rounded border border-[#5800FF] bg-[var(--bg-primary)]"
      />
      <button
        type="submit"
        disabled={!title || !content || !topic}
        className="!px-4 !py-2 bg-[#5800FF] text-white rounded hover:bg-[#E900FF] disabled:opacity-50 transition-colors"
      >
        Create Post
      </button>
    </form>
  );
}
