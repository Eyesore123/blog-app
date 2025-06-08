import React, { useState } from "react";
import axiosInstance from "./axiosInstance";

export default function SketchForm({ onSaved }: { onSaved?: () => void }) {
  const [title, setTitle] = useState("");
  const [content, setContent] = useState("");
  const [saving, setSaving] = useState(false);
  const [success, setSuccess] = useState(false);

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    setSaving(true);
    try {
      await axiosInstance.post("/sketches", { title, content });
      setTitle("");
      setContent("");
      setSuccess(true);
      onSaved?.();
    } catch (err) {
      alert("Failed to save sketch.");
    }
    setSaving(false);
  };

  return (
    <div className="flex flex-col items-center justify-center w-full">
    <form onSubmit={handleSubmit} className="!mb-6 !space-y-2">
      <h3 className="font-semibold w-full text-center">Save a Sketch</h3>
      <input
        className="border !p-2 w-full rounded"
        placeholder="Sketch title"
        value={title}
        onChange={e => setTitle(e.target.value)}
        required
      />
      <textarea
        className="border !p-2 w-full rounded"
        placeholder="Sketch content"
        value={content}
        onChange={e => setContent(e.target.value)}
        rows={4}
      />
      <button
        className="!w-full !px-4 !py-2 !mb-10 !bg-[#5800FF] !text-white !rounded !hover:bg-[#E900FF]"
        type="submit"
        disabled={saving}
      >
        {saving ? "Saving..." : "Save Sketch"}
      </button>
      {success && <div className="text-green-600">Sketch saved!</div>}
    </form>
    </div>
  );
}