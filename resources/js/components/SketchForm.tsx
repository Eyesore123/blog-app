import React, { useState } from "react";
import axiosInstance from "./axiosInstance";

export default function SketchForm({ onSaved }: { onSaved?: () => void }) {
  const [title, setTitle] = useState("");
  const [content, setContent] = useState("");
  const [topic, setTopic] = useState("");
  const [published, setPublished] = useState(true);
  const [image, setImage] = useState<string>("");
  const [imageFile, setImageFile] = useState<File | null>(null);
  const [imagePreview, setImagePreview] = useState<string>("");
  const [tags, setTags] = useState<string[]>([]);
  const [tagInput, setTagInput] = useState("");
  const [saving, setSaving] = useState(false);
  const [success, setSuccess] = useState(false);

  const handleAddTag = () => {
    const tag = tagInput.trim();
    if (tag && !tags.includes(tag)) {
      setTags([...tags, tag]);
    }
    setTagInput("");
  };

  const handleRemoveTag = (tag: string) => {
    setTags(tags.filter(t => t !== tag));
  };

  const handleImageChange = (e: React.ChangeEvent<HTMLInputElement>) => {
    if (e.target.files && e.target.files[0]) {
      const file = e.target.files[0];
      setImageFile(file);
      setImage(""); // Clear URL input if file is selected
      const reader = new FileReader();
      reader.onload = () => setImagePreview(reader.result as string);
      reader.readAsDataURL(file);
    }
  };

  const handleImageUrlChange = (e: React.ChangeEvent<HTMLInputElement>) => {
    setImage(e.target.value);
    setImageFile(null); // Clear file if URL is entered
    setImagePreview(e.target.value);
  };

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    setSaving(true);
    let imageUrl = image;

    try {
      // If a file is selected, upload it first
      if (imageFile) {
        const formData = new FormData();
        formData.append("image", imageFile);
        const res = await axiosInstance.post("/upload", formData, {
          headers: { "Content-Type": "multipart/form-data" },
        });
        imageUrl = res.data.url;
      }

      await axiosInstance.post("/sketches", {
        title,
        content,
        topic,
        published,
        image: imageUrl,
        tags,
      });
      setTitle("");
      setContent("");
      setTopic("");
      setPublished(true);
      setImage("");
      setImageFile(null);
      setImagePreview("");
      setTags([]);
      setSuccess(true);
      onSaved?.();
    } catch (err) {
      alert("Failed to save sketch.");
    }
    setSaving(false);
  };

  return (
    <div className="flex flex-col items-center justify-center w-full max-w-[540px]">
      <form onSubmit={handleSubmit} className="!mb-6 !space-y-2">
        <h3 className="font-semibold w-full text-center">Save a Sketch</h3>
        <input
          className="border !p-2 w-full rounded"
          placeholder="Sketch title"
          value={title}
          onChange={e => setTitle(e.target.value)}
          required
        />
        <input
          className="border !p-2 w-full rounded"
          placeholder="Topic"
          value={topic}
          onChange={e => setTopic(e.target.value)}
        />
        <textarea
          className="border !p-2 w-full rounded"
          placeholder="Sketch content"
          value={content}
          onChange={e => setContent(e.target.value)}
          rows={4}
        />
        {/* Image file input */}
        <input
          type="file"
          accept="image/*"
          onChange={handleImageChange}
          className="border !p-2 w-full rounded"
        />
        {/* Or image URL input */}
        {/* <input
          className="border !p-2 w-full rounded"
          placeholder="Image URL"
          value={image}
          onChange={handleImageUrlChange}
        /> */}
        {/* Image preview */}
        {imagePreview && (
          <img src={imagePreview} alt="Preview" className="!max-w-xs !my-2 !rounded" />
        )}
        <label className="flex items-center space-x-2">
          <input
            type="checkbox"
            checked={published}
            onChange={e => setPublished(e.target.checked)}
          />
          <span>Published</span>
        </label>
        <div className="flex gap-2">
          <input
            className="border !p-2 rounded flex-grow"
            placeholder="Add tag"
            value={tagInput}
            onChange={e => setTagInput(e.target.value)}
            onKeyDown={e => {
              if (e.key === "Enter") {
                e.preventDefault();
                handleAddTag();
              }
            }}
          />
          <button type="button" onClick={handleAddTag} className="!px-2 !py-1 !bg-[#5800FF] max-w-[540px] !text-white !rounded !hover:bg-[#E900FF]">
            Add Tag
          </button>
        </div>
        <div className="flex flex-wrap gap-2">
          {tags.map(tag => (
            <span key={tag} className="bg-blue-200 !px-2 !py-1 rounded flex items-center">
              {tag}
              <button type="button" className="!ml-1 text-red-600" onClick={() => handleRemoveTag(tag)}>×</button>
            </span>
          ))}
        </div>
        <button
          className="!w-full !px-4 !py-2 !mb-10 !bg-[#5800FF] !text-white !rounded !hover:bg-[#E900FF]"
          type="submit"
          disabled={saving}
        >
          {saving ? "Saving..." : "Save Sketch"}
        </button>
        {success && <div className="text-green-600 text-center">Sketch saved!</div>}
      </form>
    </div>
  );
}