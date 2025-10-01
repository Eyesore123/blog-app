import React, { useState, useEffect, useMemo } from 'react';
import axiosInstance from './axiosInstance';
import AdminVideoUpload from './AdminVideoUpload';

export interface AdminVideoInfo {
  name: string;
  url: string;        // full URL returned by backend
  postTitle?: string | null;
}

const PAGE_SIZE = 10;

export default function AdminVideoControl() {
  const [videos, setVideos] = useState<AdminVideoInfo[]>([]);
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState<string | null>(null);
  const [selectedVideo, setSelectedVideo] = useState<AdminVideoInfo | null>(null);
  const [searchTerm, setSearchTerm] = useState("");
  const [page, setPage] = useState(1);
  const [totalPages, setTotalPages] = useState(1);

  const fetchVideos = async (currentPage = 1) => {
    setLoading(true);
    setError(null);
    try {
      const res = await axiosInstance.get(`/admin/videos?page=${currentPage}&per_page=${PAGE_SIZE}`);
      setVideos(res.data?.data || []);
      setTotalPages(res.data?.last_page || Math.ceil((res.data?.total || 0) / PAGE_SIZE));
    } catch (err: any) {
      setError(err?.response?.data?.message || err.message || "Unknown error");
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => { fetchVideos(page); }, [page]);
  useEffect(() => { setPage(1); }, [searchTerm]);

  const handleDelete = async (name: string) => {
    if (!window.confirm("Delete this video?")) return;
    setError(null);
    try {
      await axiosInstance.delete(`/admin/videos/${encodeURIComponent(name)}`);
      setVideos(prev => prev.filter(v => v.name !== name));
    } catch (err: any) {
      setError(err?.response?.data?.message || err.message || "Unknown error");
    }
  };

  const filteredVideos = useMemo(() => {
    if (!searchTerm) return videos;
    const lower = searchTerm.toLowerCase();
    return videos.filter(v =>
      v.name.toLowerCase().includes(lower) ||
      (v.postTitle?.toLowerCase().includes(lower) ?? false)
    );
  }, [videos, searchTerm]);

  return (
    <div>
      <h2 className="text-xl font-bold text-center !mb-8 !mt-4">Video Control</h2>
      <AdminVideoUpload onUploadSuccess={() => fetchVideos(page)} />

      <div className="flex justify-center !mb-6">
        <input
          type="text"
          value={searchTerm}
          onChange={(e) => setSearchTerm(e.target.value)}
          placeholder="Search videos by name or post title..."
          className="border rounded !px-3 !py-2 w-full max-w-md"
        />
      </div>

      {error && <div className="text-red-600 !mb-4">{error}</div>}

      <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 !gap-4 !mb-10">
        {filteredVideos.map(video => (
          <div key={video.name} className="border rounded !p-2 flex flex-col items-center">
            <video
              src={video.url}
              className="w-full h-48 object-cover rounded hover:cursor-pointer"
              onClick={() => setSelectedVideo(video)}
              controls
            />
            <div className="text-xs break-all !mt-2">{video.name}</div>
            <div className="text-xs text-gray-500">
              {video.postTitle ? `Used in: ${video.postTitle}` : "Unused"}
            </div>
            <button
              onClick={() => handleDelete(video.name)}
              className="!mt-2 !px-2 !py-1 bg-red-500 text-white rounded text-xs"
            >
              Delete
            </button>
          </div>
        ))}
      </div>

      {loading && <div className="text-center !my-4">Loading...</div>}

      <div className="flex justify-center items-center !gap-4 !mb-10">
        <button
          onClick={() => setPage(prev => Math.max(1, prev - 1))}
          disabled={page === 1}
          className="!px-3 !py-1 bg-[#5800FF] rounded disabled:opacity-50"
        >
          Previous
        </button>
        <span>Page {page} of {totalPages}</span>
        <button
          onClick={() => setPage(prev => Math.min(totalPages, prev + 1))}
          disabled={page === totalPages}
          className="!px-3 !py-1 bg-[#5800FF] rounded disabled:opacity-50"
        >
          Next
        </button>
      </div>

      {selectedVideo && (
        <div
          className="fixed inset-0 bg-black bg-opacity-90 flex items-center justify-center z-50 cursor-pointer"
          onClick={() => setSelectedVideo(null)}
        >
          <video
            src={selectedVideo.url}
            className="max-w-full max-h-full object-contain"
            controls
            autoPlay
          />
        </div>
      )}
    </div>
  );
}
