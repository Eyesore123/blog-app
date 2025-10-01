import React, { useRef, useState } from 'react';
import axiosInstance from './axiosInstance';

interface AdminVideoUploadProps {
  onUploadSuccess: () => void | Promise<void>;
}

export default function AdminVideoUpload({ onUploadSuccess }: AdminVideoUploadProps) {
  const [file, setFile] = useState<File | null>(null);
  const [name, setName] = useState('');
  const [error, setError] = useState<string | null>(null);
  const [success, setSuccess] = useState<string | null>(null);
  const [loading, setLoading] = useState(false);
  const fileInputRef = useRef<HTMLInputElement | null>(null);

  const handleUpload = async () => {
  if (!file || !name) {
    setError('Please provide both a file and a name.');
    return;
  }

  setError(null);
  setSuccess(null);
  setLoading(true);

  const formData = new FormData();
  formData.append('video', file);
  formData.append('name', name);

  try {
    const res = await axiosInstance.post('/admin/videos/upload', formData, {
      withCredentials: true,
      headers: { 'Content-Type': 'multipart/form-data' },
    });

    if (res.data?.success && res.data.video) {
      setSuccess(`Video uploaded: ${res.data.video.name}`);
      setName('');
      setFile(null);
      if (fileInputRef.current) fileInputRef.current.value = '';
      await onUploadSuccess();
    } else {
      setError('Upload failed: invalid response from server.');
    }
  } catch (err: any) {
    setError(err?.response?.data?.message || err.message || 'Upload failed.');
  } finally {
    setLoading(false);
  }
};

  return (
    <div className="!p-4 border rounded max-w-md !mx-auto !mt-6 !mb-6 !w-full">
      <h3 className="text-lg font-semibold !mb-4">Upload / Replace Video</h3>

      {error && <div className="text-red-600 !mb-4">{error}</div>}
      {success && <div className="text-green-600 !mb-4">{success}</div>}

      <input
        type="text"
        placeholder="Video filename (e.g., intro.mp4)"
        value={name}
        onChange={(e) => setName(e.target.value)}
        className="border !px-2 !py-1 !mb-4 rounded w-full"
      />

      <input
        ref={fileInputRef}
        type="file"
        accept="video/*"
        onChange={(e) => {
          const selectedFile = e.target.files ? e.target.files[0] : null;
          setFile(selectedFile);
          if (selectedFile) setName(selectedFile.name.replace(/\s+/g, '_').toLowerCase());
        }}
        className="!mb-4 w-full"
      />

      <button
        onClick={handleUpload}
        disabled={loading}
        className={`!px-3 !py-1 rounded text-white ${
          loading ? 'bg-gray-400 cursor-not-allowed' : 'bg-green-500 hover:opacity-80'
        }`}
      >
        Upload / Replace
      </button>

      {loading && <div className="text-blue-500 text-sm !mt-3">Uploading video, please wait...</div>}
    </div>
  );
}
