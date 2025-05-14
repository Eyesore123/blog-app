import { useRef, useEffect, useState } from "react";
import { useForm } from "@inertiajs/react";

type CreatePostProps = {
  onPreviewChange?: (preview: {
    title: string;
    content: string;
    image_url: string;
  }) => void;
};

export function CreatePost({ onPreviewChange }: CreatePostProps) {
  const { data, setData, post, processing, errors, reset } = useForm({
    title: "",
    content: "",
    topic: "",
    published: true,
    image: null as File | null,
  });

  const [imageUrl, setImageUrl] = useState<string>("");

  // Only update imageUrl if the image changes
  useEffect(() => {
    if (data.image) {
      const newUrl = URL.createObjectURL(data.image);
      setImageUrl(newUrl);

      return () => {
        URL.revokeObjectURL(newUrl);
      };
    } else {
      setImageUrl("");
    }
  }, [data.image]);

  const prevPreview = useRef<{ title: string; content: string; image_url: string }>({
    title: "",
    content: "",
    image_url: "",
  });

  useEffect(() => {
    if (!onPreviewChange) return;

    const nextPreview = {
      title: data.title,
      content: data.content,
      image_url: imageUrl,
    };

    const hasChanged =
      nextPreview.title !== prevPreview.current.title ||
      nextPreview.content !== prevPreview.current.content ||
      nextPreview.image_url !== prevPreview.current.image_url;

    if (hasChanged) {
      prevPreview.current = nextPreview;
      onPreviewChange(nextPreview);
    }
  }, [data.title, data.content, imageUrl, onPreviewChange]);

  function handleImageChange(e: React.ChangeEvent<HTMLInputElement>) {
  if (e.target.files?.[0]) {
    setData("image", e.target.files[0]);
    setImageUrl(URL.createObjectURL(e.target.files[0]));
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
    <>
    <h3>Create New Post:</h3>
    <form onSubmit={handleSubmit} encType="multipart/form-data" className="!space-y-4 min-h-180 w-200 flex justify-center items-center flex-col">
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
    </>
  );
}
