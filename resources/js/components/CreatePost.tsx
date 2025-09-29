import { useEffect, useState, useMemo } from "react";
import SimpleMDE from "react-simplemde-editor";
import "easymde/dist/easymde.min.css";
import { useAlert } from '../context/AlertContext';
import SketchForm from "./SketchForm";
import SketchList from "./SketchList";
import axiosInstance from "../components/axiosInstance";
import { getCsrfToken } from "../components/auth";

type CreatePostProps = {
  onPreviewChange?: (preview: {
    title: string;
    content: string;
    image_url: string;
  }) => void;
};

export function CreatePost({ onPreviewChange }: CreatePostProps) {
  const { showAlert } = useAlert();

  // State
  const [title, setTitle] = useState("");
  const [topic, setTopic] = useState("");
  const [editorContent, setEditorContent] = useState("");
  const [published, setPublished] = useState(true);
  const [imageFile, setImageFile] = useState<File | null>(null);
  const [imageUrl, setImageUrl] = useState<string>("");
  const [tags, setTags] = useState<string[]>([]);
  const [tagInput, setTagInput] = useState("");
  const [allTags, setAllTags] = useState<string[]>([]);
  const [loading, setLoading] = useState(true);
  const [submitting, setSubmitting] = useState(false);
  const [styleFeedback, setStyleFeedback] = useState('');

  const handleStyleCheck = async () => {
  try {
    const response = await fetch('/posts/style-check', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': (document.querySelector('meta[name="csrf-token"]') as HTMLMetaElement)?.content as string
      },
      body: JSON.stringify({
        draft: editorContent,
      }),
    });

    const data = await response.json();
    setStyleFeedback(data.analysis || data.error);
  } catch (err) {
    setStyleFeedback('Error contacting AI.');
  }
};

  // Fetch all tags
  useEffect(() => {
    const fetchTags = async () => {
      const response = await fetch("/api/tags");
      const tags = await response.json();
      setAllTags(tags);
      setLoading(false);
    };
    fetchTags();
  }, []);

  // Handle loading a sketch into the form
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
    setPublished(sketch.published ?? true);
    setImageUrl(sketch.image || "");
    setImageFile(null); // reset file input
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

  // Image input change
  const handleImageChange = (e: React.ChangeEvent<HTMLInputElement>) => {
    if (e.target.files && e.target.files[0]) {
      setImageFile(e.target.files[0]);
      setImageUrl(""); // will be set in useEffect below
    }
  };

  // Show image preview
  useEffect(() => {
    if (imageFile) {
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
      reader.readAsDataURL(imageFile);
    } else if (imageUrl) {
      onPreviewChange?.({
        title,
        content: editorContent,
        image_url: imageUrl,
      });
    } else {
      onPreviewChange?.({
        title,
        content: editorContent,
        image_url: "",
      });
    }
  }, [imageFile]);

  // Tag handling
  const handleAddTag = () => {
    const trimmed = tagInput.trim();
    if (trimmed && !tags.includes(trimmed)) {
      setTags([...tags, trimmed]);
    }
    setTagInput("");
  };

  const handleRemoveTag = (tag: string) => {
    setTags(tags.filter((t) => t !== tag));
  };

  const handleTagKeyDown = (e: React.KeyboardEvent<HTMLInputElement>) => {
    if (e.key === "Enter") {
      e.preventDefault();
      handleAddTag();
    }
  };

  // Handle form submission
  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();

    if (!title || !editorContent || !topic) {
      showAlert("Missing required fields", "error");
      return;
    }

    if (imageFile && !imageFile.type.startsWith("image/")) {
      showAlert("Image must be an image file", "error");
      return;
    }

    setSubmitting(true);

    try {
      await getCsrfToken();

      const formData = new FormData();
      formData.append("title", title);
      formData.append("content", editorContent);
      formData.append("topic", topic);
      formData.append("published", published ? "1" : "0");

      if (imageFile) {
        formData.append("image", imageFile);
      }

      tags.forEach((tag, i) => formData.append(`tags[${i}]`, tag));

      const response = await axiosInstance.post("/posts", formData, { timeout: 40000 });

      if (response.data?.success) {
        // Reset form
        setTitle("");
        setTopic("");
        setEditorContent("");
        setTags([]);
        setTagInput("");
        setImageFile(null);
        setImageUrl("");

        showAlert(response.data.message, "success");
      } else {
        showAlert("Unexpected response from server", "error");
      }
    } catch (error: any) {
      console.error("Error creating post:", error.response?.data || error);
      showAlert("Error creating post", "error");
    } finally {
      setSubmitting(false);
    }
  };

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

        {/* Style check */}

        <div className="!mt-4 !flex !flex-col !items-start !gap-3">
        <button
          type="button"
          onClick={handleStyleCheck}
          className="!inline-flex !items-center !gap-2 !px-4 !py-2 !bg-green-500 !text-white !rounded-lg hover:!bg-green-600 !transition-colors focus:!ring-2 focus:!ring-green-400"
        >
          ✨ Check Style
        </button>
        {styleFeedback && (
          <div className="!w-full !p-4 !border !border-green-200 !rounded-lg !bg-green-50">
            <h4 className="!mb-2 !font-semibold !text-green-700">AI Feedback:</h4>
            <pre className="!whitespace-pre-wrap !text-sm !text-green-800">
              {styleFeedback}
            </pre>
          </div>
        )}
      </div>

        {/* Image URL */}
        <input
          type="text"
          placeholder="Or paste an image URL"
          value={imageUrl}
          onChange={(e) => {
            setImageUrl(e.target.value);
            setImageFile(null); // reset file if URL is typed
            onPreviewChange?.({
              title,
              content: editorContent,
              image_url: e.target.value,
            });
          }}
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
          <img
            src={imageUrl}
            alt="Preview"
            className="!max-w-xs !my-2 !rounded"
            onError={(e) => {
              e.currentTarget.src = "https://placehold.co/400x300?text=Image+not+found\nSorry!";
            }}
          />
        )}

        {/* Tags */}
        <div className="!w-full">
          <label className="!block !mb-1 !font-medium">Tags</label>
          {loading ? (
            <p className="!mb-2">Loading tags...</p>
          ) : (
            allTags.length > 0 && (
              <div className="!mb-2 !flex !flex-wrap !gap-2">
                {allTags
                  .filter((tag) => !tags.includes(tag))
                  .map((tag) => (
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