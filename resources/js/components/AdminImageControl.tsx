import React, { useState, useEffect } from 'react'
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

    // Fetch images
    async function fetchImages() {
        setLoading(true);
        setError(null);
        try {
            const res = await axiosInstance.get(`/admin/images?per_page=${PAGE_SIZE}`, {
                withCredentials: true
            });
            setImages(res.data?.data || []);
        } catch (err: any) {
            setError(err?.response?.data?.message || err.message || "Unknown error");
        } finally {
            setLoading(false);
        }
    }

    useEffect(() => {
        fetchImages();
    }, []);

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

    return (
        <div>
            <h2 className='text-xl font-bold w-full text-center !mb-16 !mt-4'>Image Control</h2>
            <AdminImageUpload onUploadSuccess={fetchImages} />

            {error && (
                <div className="bg-red-100 text-red-700 !px-4 !py-2 rounded !mb-4 flex items-center !gap-4">
                    <span>Failed to fetch images: {error}</span>
                    <button
                        onClick={fetchImages}
                        className="!px-2 !py-1 bg-blue-500 hover:opacity-80 text-white rounded text-xs"
                    >
                        Retry
                    </button>
                </div>
            )}

            {images.length === 0 && !loading && !error && (
                <div className="text-sm italic text-gray-500 !mb-4">No images found.</div>
            )}

            <button
                onClick={fetchImages}
                className="!mb-4 !px-2 !py-1 bg-blue-500 hover:opacity-80 text-white rounded text-xs"
            >
                Refresh
            </button>

            <div className="grid grid-cols-2 md:grid-cols-4 !gap-4 !mb-10">
                {images.map(img => (
                    <div key={img.name} className="border rounded !p-2 flex flex-col items-center">
                        <img
                            src={`${img.url}?v=${Date.now()}`} // cache-busting to replace the image immediately when imageupload component makes a change
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

            {/* Fullscreen image overlay */}
            {selectedImage && (
                <div
                    className='fixed inset-0 bg-black bg-opacity-90 flex items-center justify-center z-50 cursor-pointer'
                    onClick={() => setSelectedImage(null)}
                >
                    <img
                    src={`${selectedImage.url}?v=${Date.now()}`}
                    alt={selectedImage.name}
                    className="max-w-full max-h-full object-contain"
                    />
                </div>
            )}
        </div>
    );
}
