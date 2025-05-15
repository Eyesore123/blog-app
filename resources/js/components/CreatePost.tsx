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
    tags: [] as string[],
  });

  const [imageUrl, setImageUrl] = useState<string>("");
  const [editorContent, setEditorContent] = useState(data.content);
  const [tagInput, setTagInput] = useState(""); // Individual tag being typed

  const editorOptions = useMemo(
    () => ({
      spellChecker: false,
      placeholder: "Write your post content here...",
    }),
    []
  );

  function handleEditorChange(value: string) {
    setEditorContent(value);
    setData('content', value);
    onPreviewChange?.({
      title: data.title,
      content: value,
      image_url: imageUrl,
    });
  }

  useEffect(() => {
    if (data.image) {
      const reader = new FileReader();
      reader.onload = () => {
        const result = reader.result as string;
        setImageUrl(result); // Set the data URL
        onPreviewChange?.({
          title: data.title,
          content: editorContent,
          image_url: result, // Trigger preview update immediately
        });
      };
      reader.readAsDataURL(data.image); // Read the file as a data URL
    } else {
      setImageUrl('');
      onPreviewChange?.({
        title: data.title,
        content: editorContent,
        image_url: '', // Clear the preview image
      });
    }
  }, [data.image]);

  function handleImageChange(e: React.ChangeEvent<HTMLInputElement>) {
    const file = e.target.files?.[0];
    if (!file) return setData('image', null);
    if (!file.type.startsWith('image/')) {
      console.error('Selected file is not an image');
      return;
    }
    setData('image', file);
  }

  function handleAddTag() {
    const name = tagInput.trim();
    if (name && !data.tags.includes(name)) {
      setData("tags", [...data.tags, name]);
    }
    setTagInput("");
  }

  function handleRemoveTag(tag: string) {
    setData("tags", data.tags.filter((t) => t !== tag));
  }

  function handleTagKeyDown(e: React.KeyboardEvent<HTMLInputElement>) {
    if (e.key === "Enter") {
      e.preventDefault();
      handleAddTag();
    }
  }

  function handleSubmit(e: React.FormEvent) {
    e.preventDefault();
    setData("content", editorContent);

    if (!data.title || !editorContent || !data.topic) {
      console.error("Missing required fields");
      return;
    }
    if (data.image && !data.image.type.startsWith("image/")) {
      console.error("Image must be an image file");
      return;
    }

    post("/posts", {
      onSuccess: () => {
        reset("title", "content", "topic", "image", "tags");
        setEditorContent("");
        setTagInput("");
        setImageUrl("");
      },
      onError: (error: any) => {
        console.error("Error creating post:", error.response?.data || error);
      },
    });
  }

  return (
    <>
      <h3 className="!mb-6">Create New Post:</h3>
      <form
        onSubmit={handleSubmit}
        encType="multipart/form-data"
        className="!space-y-4 !w-full !max-w-lg !mx-auto !flex !flex-col"
      >
        {/* Title */}
        <input
          type="text"
          placeholder="Post title"
          value={data.title}
          onChange={(e) => setData("title", e.target.value)}
          className="!w-full !p-2 !rounded !border !border-[#5800FF] !bg-[var(--bg-primary)]"
        />
        {errors.title && (
          <p className="!text-red-600 !text-sm">{errors.title}</p>
        )}

        {/* Topic */}
        <input
          type="text"
          placeholder="Topic (e.g., Web Development)"
          value={data.topic}
          onChange={(e) => setData("topic", e.target.value)}
          className="!w-full !p-2 !rounded !border !border-[#5800FF] !bg-[var(--bg-primary)]"
        />
        {errors.topic && (
          <p className="!text-red-600 !text-sm">{errors.topic}</p>
        )}

        {/* Content */}
        <SimpleMDE
          value={editorContent}
          onChange={handleEditorChange}
          options={editorOptions}
          className="!w-full !p-2 !rounded !border !border-[#5800FF] !bg-[var(--bg-primary)]"
        />
        {errors.content && (
          <p className="!text-red-600 !text-sm">{errors.content}</p>
        )}

        {/* Image Upload */}
        <input
          type="file"
          accept="image/*"
          onChange={handleImageChange}
          className="!w-full !p-2 !rounded !border !border-[#5800FF] !bg-[var(--bg-primary)]"
        />
        {errors.image && (
          <p className="!text-red-600 !text-sm">{errors.image}</p>
        )}

        {/* Tags */}
        <div className="!w-full">
          <label className="!block !mb-1 !font-medium">Tags</label>
          <div className="!flex !gap-2">
            <input
              type="text"
              placeholder="Add a tag and press Enter"
              value={tagInput}
              onChange={(e) => setTagInput(e.target.value)}
              onKeyDown={handleTagKeyDown}
              className="!flex-grow !p-2 !rounded !border !border-[#5800FF] !bg-[var(--bg-primary)]"
            />
            <button
              type="button"
              onClick={handleAddTag}
              className="!px-4 !py-2 !bg-[#5800FF] !text-white !rounded !hover:bg-[#E900FF] !transition-colors"
            >
              Add
            </button>
          </div>
          {errors.tags && (
            <p className="!text-red-600 !text-sm">{errors.tags}</p>
          )}
          <div className="!mt-2 !flex !flex-wrap !gap-2">
            {data.tags.map((tag) => (
              <div
                key={tag}
                className="!flex !items-center !bg-[#5800FF] !text-white !rounded !px-3 !py-1 !text-sm"
              >
                {tag}
                <button
                  type="button"
                  onClick={() => handleRemoveTag(tag)}
                  className="!ml-2 !font-bold !hover:text-gray-300"
                  aria-label={`Remove ${tag}`}
                >
                  ×
                </button>
              </div>
            ))}
          </div>
        </div>

        {/* Submit */}
        <button
          type="submit"
          disabled={!data.title || !editorContent || !data.topic || processing}
          className="!w-full !px-4 !py-2 !bg-[#5800FF] !text-white !rounded !hover:bg-[#E900FF] !disabled:opacity-50"
        >
          {processing ? "Creating..." : "Create Post"}
        </button>
      </form>
    </>
  );
}
