import { useEffect, useState, useMemo } from "react";
import { useForm } from "@inertiajs/react";
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
  const { data, setData, post, processing, errors, reset } = useForm({
    title: "",
    content: "",
    topic: "",
    published: true,
    image: null as File | string | null,
    tags: [] as string[],
  });

  const [imageUrl, setImageUrl] = useState<string>("");
  const [editorContent, setEditorContent] = useState(data.content);
  const [tagInput, setTagInput] = useState("");
  const [allTags, setAllTags] = useState<string[]>([]);
  const [loading, setLoading] = useState<boolean>(true);
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

  // Handle loading a sketch into the post form
  function handleLoadSketch(sketch: {
    title: string;
    content: string;
    topic?: string;
    tags?: string[];
    image?: string;
    published?: true | boolean;
  }) {
    setData("title", sketch.title);
    setEditorContent(sketch.content);
    setData("content", sketch.content);
    setData("topic", sketch.topic || "");
    setData("tags", sketch.tags || []);
    setData("image", sketch.image || "");
    setData("published", sketch.published || true);
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

  // Handle editor content change
  function handleEditorChange(value: string) {
    setEditorContent(value);
    setData('content', value);
    onPreviewChange?.({
      title: data.title,
      content: value,
      image_url: imageUrl,
    });
  }

  // Handle image input change
  const handleImageChange = (e: React.ChangeEvent<HTMLInputElement>) => {
    if (e.target.files && e.target.files[0]) {
      setData("image", e.target.files[0]);
      setImageUrl(""); // Will be set in useEffect below
    }
  };

  // Show image preview for both file and URL
  useEffect(() => {
    if (data.image instanceof File) {
      const reader = new FileReader();
      reader.onload = () => {
        const result = reader.result as string;
        setImageUrl(result);
        onPreviewChange?.({
          title: data.title,
          content: editorContent,
          image_url: result,
        });
      };
      reader.readAsDataURL(data.image);
    } else if (typeof data.image === "string" && data.image) {
      setImageUrl(data.image);
      onPreviewChange?.({
        title: data.title,
        content: editorContent,
        image_url: data.image,
      });
    } else {
      setImageUrl('');
      onPreviewChange?.({
        title: data.title,
        content: editorContent,
        image_url: '',
      });
    }
    // eslint-disable-next-line
  }, [data.image]);

  // Tag handling
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
  // Handle form submit, original version with onSuccess and onError, without Axios

  function handleSubmit(e: React.FormEvent) {
    e.preventDefault();
    setData("content", editorContent);

    if (!data.title || !editorContent || !data.topic) {
      showAlert("Missing required fields", "error");
      return;
    }
    if (data.image && data.image instanceof File && !data.image.type.startsWith("image/")) {
      showAlert("Image must be an image file", "error");
      return;
    }

    post("/posts", {
      onSuccess: () => {
        reset("title", "content", "topic", "image", "tags");
        setEditorContent("");
        setTagInput("");
        setImageUrl("");
        showAlert("Post created successfully!", "success");
      },
      onError: (error: any) => {
        showAlert("Error creating post", "error");
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
        {imageUrl && (
          <img src={imageUrl} alt="Preview" className="!max-w-xs !my-2 !rounded" />
        )}
        {errors.image && (
          <p className="!text-red-600 !text-sm">{errors.image}</p>
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
                  .filter(tag => !data.tags.includes(tag))
                  .map(tag => (
                    <button
                      key={tag}
                      type="button"
                      className="!bg-gray-200 !text-[#5800FF] !rounded !px-3 !py-1 !text-sm hover:!bg-[#5800FF] hover:!text-white transition"
                      onClick={() => setData("tags", [...data.tags, tag])}
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
          className="!w-full !px-4 !py-2 !mb-10 !bg-[#5800FF] !text-white !rounded !hover:bg-[#E900FF] !disabled:opacity-50"
        >
          {processing ? "Creating..." : "Create Post"}
        </button>
      </form>
      <SketchForm />
      <SketchList onLoadSketch={handleLoadSketch} />
    </>
  );
}