import { useState } from "react";
import { Inertia } from "@inertiajs/inertia"; // Import Inertia.js

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

    // Send the post request to the backend using Inertia.js
    Inertia.post('/posts', newPost, {
      onSuccess: () => {
        // Optionally reset form after successful submission
        setTitle("");
        setContent("");
        setTopic("");
      },
      onError: (error) => {
        console.error("Error creating post:", error);
      }
    });
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
