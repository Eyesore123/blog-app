import React, { useState, useEffect, useRef } from 'react'
import axiosInstance from './axiosInstance';

export interface AdminImageInfo {
    name: string;
    url: string;
    size?: number;
    postTitle?: string | null;
}

const PAGE_SIZE = 20;

export default function AdminImageControl() {

    const [images, setImages] = useState<AdminImageInfo[]>([]);
    const [page, setPage] = useState(1);
    const [hasMore, setHasMore] = useState(true);
    const [loading, setLoading] = useState(false);
    const loader = useRef<HTMLDivElement>(null);

    useEffect(() => {
        fetchImages(page);
    }, [page]);

     // Lazy loading: fetch next page when scrolled to bottom
    useEffect(() => {
        if (!hasMore || loading) return;
        const observer = new IntersectionObserver(
        entries => {
            if (entries[0].isIntersecting) setPage(prev => prev + 1);
        },
        { threshold: 1 }
        );
        if (loader.current) observer.observe(loader.current);
        return () => {
        if (loader.current) observer.unobserve(loader.current);
        };
    }, [loader, hasMore, loading]);

    // Image fetch function
    async function fetchImages(pageNum: number) {
    setLoading(true);
    try {
      const res = await axiosInstance.get(`/admin/images?page=${pageNum}&per_page=${PAGE_SIZE}`);
      if (pageNum === 1) setImages(res.data.data);
      else setImages(prev => [...prev, ...res.data.data]);
      setHasMore(res.data.next_page_url !== null);
    } finally {
      setLoading(false);
    }
  }

    // Image delete function
    async function handleDelete(name: string) {
    if (!window.confirm("Delete this image?")) return;
    await axiosInstance.delete(`/admin/images/${encodeURIComponent(name)}`);
    setImages(prev => prev.filter(img => img.name !== name));
  }

  return (
    <div>
        <h2 className='text-xl font-bold mb-4'>Image Control</h2>
        <div className="grid grid-cols-2 md:grid-cols-4 gap-4">
            {images.map(img => (
                <div key={img.name} className="border rounded p-2 flex flex-col items-center">
                    <img
                        src={img.url}
                        alt={img.name}
                        className="w-full h-32 object-cover mb-2 rounded"
                        loading="lazy"
                        style={{ background: "#eee" }}
                    />
                    <div className="text-xs break-all">{img.name}</div>
                    <div className="text-xs text-gray-500">
                        {img.postTitle ? `Used in: ${img.postTitle}` : "Unused"}
                    </div>
                    <button
                        onClick={() => handleDelete(img.name)}
                        className="mt-2 px-2 py-1 bg-red-500 text-white rounded text-xs"
                    >
                        Delete
                    </button>
                </div>
            ))}
        </div>
        {loading && <div className="text-center my-4">Loading...</div>}
        <div ref={loader} />
        {!hasMore && !loading && (
            <div className="text-center text-xs text-gray-400 mt-4">No more images.</div>
        )}
    </div>
  );
}
