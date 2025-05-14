import { useRef, useEffect, useState, useMemo } from "react";
import { useForm } from "@inertiajs/react";
import SimpleMDE from "react-simplemde-editor";
import "easymde/dist/easymde.min.css";

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
  const [editorContent, setEditorContent] = useState(data.content); // Use state for editor content

  // Memoize SimpleMDE options to prevent reinitialization
  const editorOptions = useMemo(
    () => ({
      spellChecker: false,
      placeholder: "Write your post content here...",
    }),
    []
  );

  function handleEditorChange(value: string) {
    setEditorContent(value); // Update state
    setData("content", value); // Sync with form data

    if (onPreviewChange) {
      onPreviewChange({
        title: data.title,
        content: value,
        image_url: imageUrl,
      });
    }
  }

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
      content: editorContent,
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
  }, [data.title, editorContent, imageUrl, onPreviewChange]);

  function handleImageChange(e: React.ChangeEvent<HTMLInputElement>) {
    if (e.target.files?.[0]) {
      const file = e.target.files[0];

      // Validate that the file is an image
      if (!file.type.startsWith("image/")) {
        console.error("The selected file is not an image.");
        return;
      }

      setData("image", file);
      setImageUrl(URL.createObjectURL(file));
    }
  }

  function handleSubmit(e: React.FormEvent) {
    e.preventDefault();

    // Sync the editor content to the form data
    setData("content", editorContent);

    // Validate the input data
    if (!data.title || !editorContent || !data.topic) {
      console.error("Validation failed: Missing required fields.");
      return;
    }

    if (data.image && !data.image.type.startsWith("image/")) {
      console.error("Validation failed: Image field must be an image.");
      return;
    }

    // Send the request to the server
    post("/posts", {
      onSuccess: () => {
        reset("title", "content", "topic", "image");
        setEditorContent(""); // Reset the editor content
      },
      onError: (error: any) => {
        console.error("Error creating post:", error.response?.data || error);
      },
    });
  }

  return (
    <>
      <h3>Create New Post:</h3>
      <form
        onSubmit={handleSubmit}
        encType="multipart/form-data"
        className="!space-y-4 min-h-180 w-200 flex justify-center items-center flex-col"
      >
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
        <SimpleMDE
          value={editorContent} // Use state for value
          onChange={handleEditorChange} // Update state and form data on change
          options={editorOptions} // Memoized options
          className="w-full !p-2 rounded border border-[#5800FF] bg-[var(--bg-primary)]"
        />
        <input
          type="file"
          accept="image/*"
          onChange={handleImageChange}
          className="w-full !p-2 rounded border border-[#5800FF] bg-[var(--bg-primary)]"
        />
        <button
          type="submit"
          disabled={!data.title || !editorContent || !data.topic || processing}
          className="!px-4 !py-2 bg-[#5800FF] text-white rounded hover:bg-[#E900FF] disabled:opacity-50 transition-colors"
        >
          {processing ? "Creating..." : "Create Post"}
        </button>
      </form>
    </>
  );
}