import { useEffect, useState, useMemo } from "react";
import axiosInstance from "../components/axiosInstance";
import { getCsrfToken } from "../components/auth";
import SimpleMDE from "react-simplemde-editor";
import "easymde/dist/easymde.min.css";
import { useAlert } from '../context/AlertContext';
import SketchForm from "./SketchForm";
import SketchList from "./SketchList";

type CreatePostProps = {
  onPreviewChange?: (preview: {
    title: string;
    content: string;
    image_url: string;
  }) => void;
};

export function CreatePost({ onPreviewChange }: CreatePostProps) {
  const [title, setTitle] = useState("");
  const [topic, setTopic] = useState("");
  const [editorContent, setEditorContent] = useState("");
  const [published, setPublished] = useState(true);
  const [image, setImage] = useState<File | string | null>(null);
  const [imageUrl, setImageUrl] = useState<string>("");
  const [tagInput, setTagInput] = useState("");
  const [tags, setTags] = useState<string[]>([]);
  const [allTags, setAllTags] = useState<string[]>([]);
  const [loading, setLoading] = useState(true);
  const [submitting, setSubmitting] = useState(false);
  const { showAlert } = useAlert();

  // Fetch all tags
  useEffect(() => {
    const fetchTags = async () => {
      const response = await fetch('/api/tags');
      const tags = await response.json();
      setAllTags(tags);
      setLoading(false);
    };
    fetchTags();
  }, []);

  // Load a sketch into the post form
  function handleLoadSketch(sketch: {
    title: string;
    content: string;
    topic?: string;
    tags?: string[];
    image?: string;
    published?: true | boolean;
  }) {
    setTitle(sketch.title);
    setEditorContent(sketch.content);
    setTopic(sketch.topic || "");
    setTags(sketch.tags || []);
    setImage(sketch.image || "");
    setPublished(sketch.published ?? true);
    setImageUrl(sketch.image || "");
  }

  // Editor options
  const editorOptions = useMemo(
    () => ({
      spellChecker: false,
      placeholder: "Write your post content here...",
    }),
    []
  );

  // Editor content change
  function handleEditorChange(value: string) {
    setEditorContent(value);
    onPreviewChange?.({
      title,
      content: value,
      image_url: imageUrl,
    });
  }

  // Handle image input
  const handleImageChange = (e: React.ChangeEvent<HTMLInputElement>) => {
    if (e.target.files && e.target.files[0]) {
      setImage(e.target.files[0]);
      setImageUrl(""); // preview will update in useEffect
    }
  };

  // Show image preview
  useEffect(() => {
    if (image instanceof File) {
      const reader = new FileReader();
      reader.onload = () => {
        const result = reader.result as string;
        setImageUrl(result);
        onPreviewChange?.({
          title,
          content: editorContent,
          image_url: result,
        });
      };
      reader.readAsDataURL(image);
    } else if (typeof image === "string" && image) {
      setImageUrl(image);
      onPreviewChange?.({
        title,
        content: editorContent,
        image_url: image,
      });
    } else {
      setImageUrl('');
      onPreviewChange?.({
        title,
        content: editorContent,
        image_url: '',
      });
    }
    // eslint-disable-next-line
  }, [image]);

  // Tag handling
  function handleAddTag() {
    const name = tagInput.trim();
    if (name && !tags.includes(name)) {
      setTags([...tags, name]);
    }
    setTagInput("");
  }

  function handleRemoveTag(tag: string) {
    setTags(tags.filter((t) => t !== tag));
  }

  function handleTagKeyDown(e: React.KeyboardEvent<HTMLInputElement>) {
    if (e.key === "Enter") {
      e.preventDefault();
      handleAddTag();
    }
  }

  async function handleSubmit(e: React.FormEvent) {
    e.preventDefault();

    if (!title || !editorContent || !topic) {
      showAlert("Missing required fields", "error");
      return;
    }
    if (image && image instanceof File && !image.type.startsWith("image/")) {
      showAlert("Image must be an image file", "error");
      return;
    }

    const formData = new FormData();
    formData.append("title", title);
    formData.append("content", editorContent);
    formData.append("topic", topic);
    formData.append("published", published ? "1" : "0");
    if (image instanceof File) {
      formData.append("image", image);
    }
    tags.forEach((tag, i) => formData.append(`tags[${i}]`, tag));

    try {
      setSubmitting(true);

      // important for Laravel Sanctum/CSRF
      await getCsrfToken();

      await axiosInstance.post("/posts", formData, {
        headers: { "Content-Type": "multipart/form-data" },
      });

      // Reset form
      setTitle("");
      setEditorContent("");
      setTopic("");
      setTags([]);
      setImage(null);
      setImageUrl("");
      setTagInput("");

      showAlert("Post created successfully!", "success");
    } catch (error: any) {
      console.error("Error creating post:", error.response?.data || error);
      showAlert("Error creating post", "error");
    } finally {
      setSubmitting(false); // always returns UI to normal
    }
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
          value={title}
          onChange={(e) => setTitle(e.target.value)}
          className="!w-full !p-2 !rounded !border !border-[#5800FF] !bg-[var(--bg-primary)]"
        />

        {/* Topic */}
        <input
          type="text"
          placeholder="Topic (e.g., Web Development)"
          value={topic}
          onChange={(e) => setTopic(e.target.value)}
          className="!w-full !p-2 !rounded !border !border-[#5800FF] !bg-[var(--bg-primary)]"
        />

        {/* Content */}
        <SimpleMDE
          value={editorContent}
          onChange={handleEditorChange}
          options={editorOptions}
          className="!w-full !p-2 !rounded !border !border-[#5800FF] !bg-[var(--bg-primary)]"
        />

        {/* Image Upload */}
        <input
          type="file"
          accept="image/*"
          onChange={handleImageChange}
          className="!w-full !p-2 !rounded !border !border-[#5800FF] !bg-[var(--bg-primary)]"
        />
        {imageUrl && (
          <img src={imageUrl} alt="Preview" className="!max-w-xs !my-2 !rounded" />
        )}

        {/* Tags */}
        <div className="!w-full">
          <label className="!block !mb-1 !font-medium">Tags</label>
          {loading ? (
            <p className="!mb-2">Loading tags...</p>
          ) : (
            allTags && allTags.length > 0 && (
              <div className="!mb-2 !flex !flex-wrap !gap-2">
                {allTags
                  .filter(tag => !tags.includes(tag))
                  .map(tag => (
                    <button
                      key={tag}
                      type="button"
                      className="!bg-gray-200 !text-[#5800FF] !rounded !px-3 !py-1 !text-sm hover:!bg-[#5800FF] hover:!text-white transition"
                      onClick={() => setTags([...tags, tag])}
                    >
                      {tag}
                    </button>
                  ))}
              </div>
            )
          )}

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

          <div className="!mt-2 !flex !flex-wrap !gap-2">
            {tags.map((tag) => (
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
          disabled={!title || !editorContent || !topic || submitting}
          className="!w-full !px-4 !py-2 !mb-10 !bg-[#5800FF] !text-white !rounded !hover:bg-[#E900FF] !disabled:opacity-50"
        >
          {submitting ? "Creating..." : "Create Post"}
        </button>
      </form>
      <SketchForm />
      <SketchList onLoadSketch={handleLoadSketch} />
    </>
  );
}
