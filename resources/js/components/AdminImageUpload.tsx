import React, { useRef, useState } from 'react';
import axiosInstance from './axiosInstance';

interface AdminImageUploadProps {
  onUploadSuccess: () => void | Promise<void>;
}

export default function AdminImageUpload({ onUploadSuccess }: AdminImageUploadProps) {
  const [file, setFile] = useState<File | null>(null);
  const [name, setName] = useState('');
  const [error, setError] = useState<string | null>(null);
  const [success, setSuccess] = useState<string | null>(null);
  const [loading, setLoading] = useState(false);

  const fileInputRef = useRef<HTMLInputElement | null>(null);

  const handleFileUpload = async () => {
    if (!file || !name) {
      setError('Please provide both a file and a name.');
      return;
    }

    setError(null);
    setSuccess(null);
    setLoading(true);

    const formData = new FormData();
    formData.append('image', file);
    formData.append('name', name);

    try {
      const res = await axiosInstance.post('/admin/images/upload', formData, {
        withCredentials: true,
        headers: { 'Content-Type': 'multipart/form-data' },
      });

      setSuccess(`Image uploaded: ${res.data.name}`);
      setName('');
      setFile(null);

      // reset the file input element properly
      if (fileInputRef.current) {
        fileInputRef.current.value = '';
      }

      await onUploadSuccess();
    } catch (err: any) {
      setError(err?.response?.data?.message || err.message || 'Upload failed due to an unknown error.');
    } finally {
      setLoading(false);
    }
  };

  return (
    <div className="!p-4 border rounded max-w-md !mx-auto !mt-6 !mb-6 !w-full">
      <h3 className="text-lg font-semibold !mb-4">Upload / Replace Image</h3>

      {error && <div className="text-red-600 !mb-4">{error}</div>}
      {success && <div className="text-green-600 !mb-4">{success}</div>}

      <input
        type="text"
        placeholder="Image filename (e.g., example.jpg)"
        value={name}
        onChange={(e) => setName(e.target.value)}
        className="border !px-2 !py-1 !mb-4 rounded w-full"
      />

      <input
        ref={fileInputRef}
        type="file"
        accept="image/*"
        onChange={(e) => {
        const selectedFile = e.target.files ? e.target.files[0] : null;
        setFile(selectedFile);
        if (selectedFile) {
          const lowercasedName = selectedFile.name
            .replace(/\s+/g, '_')   // replace spaces with underscores
            .toLowerCase();         // force lowercase
          setName(lowercasedName);
        }
      }}

        className="!mb-4 w-full"
      />

      <button
        onClick={handleFileUpload}
        disabled={loading}
        className={`!px-3 !py-1 rounded text-white ${
          loading ? 'bg-gray-400 cursor-not-allowed' : 'bg-blue-500 hover:opacity-80'
        }`}
      >
        Upload / Replace
      </button>

      {loading && <div className="text-blue-500 text-sm !mt-3">Uploading image, please wait...</div>}
    </div>
  );
}
