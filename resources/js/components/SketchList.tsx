import React, { useEffect, useState } from "react";
import axiosInstance from "./axiosInstance";

type SketchListProps = {
  onLoadSketch?: (sketch: { title: string; content: string }) => void;
};

export default function SketchList({ onLoadSketch }: SketchListProps) {
  const [sketches, setSketches] = useState<any[]>([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);

  const fetchSketches = async () => {
    setLoading(true);
    setError(null);
    try {
      const res = await axiosInstance.get("/admin/sketches");
      setSketches(res.data);
    } catch (err) {
      setError("Failed to load sketches.");
    }
    setLoading(false);
  };

  const handleDelete = async (id: number) => {
    if (!window.confirm("Delete this sketch?")) return;
    try {
      await axiosInstance.delete(`/sketches/${id}`);
      setSketches(sketches => sketches.filter(sketch => sketch.id !== id));
    } catch {
      alert("Failed to delete sketch.");
    }
  };

  useEffect(() => {
    fetchSketches();
  }, []);

  if (loading) return <div className="!mb-6 !pb-10">Loading sketches...</div>;
  if (error) return <div className="text-red-600 !mb-6 !pb-10">{error}</div>;

  return (
    <div className="flex flex-col items-center justify-center w-full">
      <h3 className="font-semibold !mb-4">All Sketches</h3>
      <button onClick={fetchSketches} className="!w-full !px-4 !py-2 !mb-10 !bg-[#5800FF] max-w-[540px] !text-white !rounded !hover:bg-[#E900FF]">Refresh</button>
      {sketches.length === 0 ? (
        <div className="!mb-6 !pb-10">No sketches found.</div>
      ) : (
        <ul className="!space-y-2">
          {sketches.map(sketch => (
            <li key={sketch.id} className="border rounded !p-2 !m-10 min-w-[540px]">
              <div className="font-bold !m-4">{sketch.title}</div>
              <div className="text-sm opacity-80 !m-4">{sketch.content}</div>
              <div className="text-xs text-gray-400 !m-4">By: {sketch.user?.name}</div>
              <button
                className="!px-2 !py-1 !m-4 bg-blue-500 text-white rounded text-xs hover:bg-blue-700"
                onClick={() => onLoadSketch?.({ title: sketch.title, content: sketch.content })}
              >
                Load to Post
              </button>
              <button
                className="self-end !px-2 !m-4 !py-1 bg-red-500 text-white rounded text-xs hover:bg-red-700"
                onClick={() => handleDelete(sketch.id)}
              >
                Delete
              </button>
            </li>
          ))}
        </ul>
      )}
    </div>
  );
}