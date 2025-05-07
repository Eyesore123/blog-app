import { useState } from "react";
import { useForm } from "@inertiajs/react";

export function CreatePost() {
  const { data, setData, post, processing, errors, reset } = useForm({
    title: "",
    content: "",
    topic: "",
    published: true,
    image: null as File | null,
  });

  function handleImageChange(e: React.ChangeEvent<HTMLInputElement>) {
    if (e.target.files?.[0]) {

      setData("image", e.target.files[0]);
    }
  }

  function handleSubmit(e: React.FormEvent) {
    e.preventDefault();
    if (!data.title || !data.content || !data.topic) return;

    post("/posts", {
      onSuccess: () => {
        reset("title", "content", "topic", "image");
      },
      onError: (error) => {
        console.error("Error creating post:", error);
      },
    });
  }

  return (
    <form onSubmit={handleSubmit} encType="multipart/form-data" className="!space-y-4 !mt-8 w-200 flex justify-center items-center flex-col">
      <input
        type="text"
        placeholder="Post title"
        value={data.title}
        onChange={(e) => setData("title", e.target.value)}
        className="w-full !p-2 rounded border border-[#5800FF] bg-[var(--bg-primary)]"
      />
      <input
        type="text"
        placeholder="Topic (e.g., Web Development, Design, Programming)"
        value={data.topic}
        onChange={(e) => setData("topic", e.target.value)}
        className="w-full !p-2 rounded border border-[#5800FF] bg-[var(--bg-primary)]"
      />
      <textarea
        placeholder="Write your post..."
        value={data.content}
        onChange={(e) => setData("content", e.target.value)}
        className="w-full h-100 !p-2 rounded border border-[#5800FF] bg-[var(--bg-primary)]"
      />
      <input
        type="file"
        accept="image/*"
        onChange={handleImageChange}
        className="w-full !p-2 rounded border border-[#5800FF] bg-[var(--bg-primary)]"
      />
      <button
        type="submit"
        disabled={!data.title || !data.content || !data.topic || processing}
        className="!px-4 !py-2 bg-[#5800FF] text-white rounded hover:bg-[#E900FF] disabled:opacity-50 transition-colors"
      >
        {processing ? "Creating..." : "Create Post"}
      </button>
    </form>
  );
}
