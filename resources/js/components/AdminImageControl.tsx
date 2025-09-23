import React, { useState, useEffect, useMemo } from 'react'
import axiosInstance from './axiosInstance';
import AdminImageUpload from './AdminImageUpload';

export interface AdminImageInfo {
    name: string;
    url: string;
    postTitle?: string | null;
}

const PAGE_SIZE = 20;

export default function AdminImageControl() {
    const [images, setImages] = useState<AdminImageInfo[]>([]);
    const [loading, setLoading] = useState(false);
    const [error, setError] = useState<string | null>(null);
    const [selectedImage, setSelectedImage] = useState<AdminImageInfo | null>(null);
    const [searchTerm, setSearchTerm] = useState("");

    // pagination
    const [page, setPage] = useState(1);
    const [totalPages, setTotalPages] = useState(1);

    // Fetcf img url

    function getImageUrl(url: string) {
        if (import.meta.env.MODE === "production") {
            return url.replace("/uploads/", "/storage/uploads/");
        }
        return url;
    }


    // Fetch images
    async function fetchImages(currentPage = 1) {
        setLoading(true);
        setError(null);
        try {
            const res = await axiosInstance.get(
                `/admin/images?page=${currentPage}&per_page=${PAGE_SIZE}`,
                { withCredentials: true }
            );

            setImages(res.data?.data || []);

            // If your API provides total count, use it:
            if (res.data?.last_page) {
            setTotalPages(res.data.last_page);
            } else if (res.data?.total) {
            setTotalPages(Math.ceil(res.data.total / PAGE_SIZE));
            } else {
            setTotalPages(1);
            }


        } catch (err: any) {
            setError(err?.response?.data?.message || err.message || "Unknown error");
        } finally {
            setLoading(false);
        }
    }

    useEffect(() => {
        fetchImages(page);
    }, [page]);

    // Go back to first page when search term changes
    useEffect(() => {
    setPage(1);
    }, [searchTerm]);


    // Delete image
    async function handleDelete(name: string) {
        if (!window.confirm("Delete this image?")) return;
        setError(null);
        try {
            await axiosInstance.delete(`/admin/images/${encodeURIComponent(name)}`);
            setImages(prev => prev.filter(img => img.name !== name));
        } catch (err: any) {
            setError(err?.response?.data?.message || err.message || "Unknown error");
        }
    }

    // Filter + sort images by search term
    const filteredImages = useMemo(() => {
        if (!searchTerm) return images;

        const lower = searchTerm.toLowerCase();

        return [...images].sort((a, b) => {
            const aMatch =
                a.name.toLowerCase().includes(lower) ||
                (a.postTitle?.toLowerCase().includes(lower) ?? false);
            const bMatch =
                b.name.toLowerCase().includes(lower) ||
                (b.postTitle?.toLowerCase().includes(lower) ?? false);

            if (aMatch && !bMatch) return -1;
            if (!aMatch && bMatch) return 1;
            return a.name.localeCompare(b.name);
        });
    }, [images, searchTerm]);

    return (
        <div>
            <h2 className='text-xl font-bold w-full text-center !mb-8 !mt-4'>Image Control</h2>
            <AdminImageUpload onUploadSuccess={() => fetchImages(page)} />

            {/* Search box */}
            <div className="flex justify-center !mb-6">
                <input
                    type="text"
                    value={searchTerm}
                    onChange={(e) => setSearchTerm(e.target.value)}
                    placeholder="Search images by name or post title..."
                    className="border rounded !px-3 !py-2 w-full max-w-md"
                />
            </div>

            {error && (
                <div className="bg-red-100 text-red-700 !px-4 !py-2 rounded !mb-4 flex items-center !gap-4">
                    <span>Failed to fetch images: {error}</span>
                    <button
                        onClick={() => fetchImages(page)}
                        className="!px-2 !py-1 bg-blue-500 hover:opacity-80 text-white rounded text-xs"
                    >
                        Retry
                    </button>
                </div>
            )}

            {filteredImages.length === 0 && !loading && !error && (
                <div className="text-sm italic text-gray-500 !mb-4">No images found.</div>
            )}

            <button
                onClick={() => fetchImages(page)}
                className="!mb-4 !px-2 !py-1 bg-blue-500 hover:opacity-80 text-white rounded text-xs"
            >
                Refresh
            </button>

            <div className="grid grid-cols-2 md:grid-cols-4 !gap-4 !mb-10">
                {filteredImages.map(img => (
                    <div key={img.name} className="border rounded !p-2 flex flex-col items-center">
                        <img
                            src={`${getImageUrl(img.url)}?v=${Date.now()}`}
                            alt={img.name}
                            className="w-full h-32 object-cover !mb-2 rounded hover:cursor-pointer"
                            loading="lazy"
                            style={{ background: "#eee" }}
                            onClick={() => setSelectedImage(img)}
                        />
                        <div className="text-xs break-all">{img.name}</div>
                        <div className="text-xs text-gray-500">
                            {img.postTitle ? `Used in: ${img.postTitle}` : "Unused"}
                        </div>
                        <button
                            onClick={() => handleDelete(img.name)}
                            className="!mt-2 !px-2 !py-1 bg-red-500 text-white rounded text-xs"
                        >
                            Delete
                        </button>
                    </div>
                ))}
            </div>

            {loading && <div className="text-center !my-4">Loading...</div>}

            {/* Pagination */}
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

            {/* Fullscreen image overlay */}
            {selectedImage && (
                <div
                    className='fixed inset-0 bg-black bg-opacity-90 flex items-center justify-center z-50 cursor-pointer'
                    onClick={() => setSelectedImage(null)}
                >
                    <img
                    src={`${getImageUrl(selectedImage.url)}?v=${Date.now()}`}
                    alt={selectedImage.name}
                    className="max-w-full max-h-full object-contain"
                    />
                </div>
            )}
        </div>
    );
}
