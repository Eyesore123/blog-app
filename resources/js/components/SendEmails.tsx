import React, { useState } from 'react';
import axiosInstance from './axiosInstance';

export default function SendEmails() {
  const [group, setGroup] = useState('me');
  const [subject, setSubject] = useState('');
  const [message, setMessage] = useState('');
  const [status, setStatus] = useState('');

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    try {
      await axiosInstance.post('/admin/send-emails', { group, subject, message });
      setStatus('✅ Emails queued successfully!');
      setSubject('');
      setMessage('');
    } catch (err) {
      setStatus('❌ Failed to send emails.');
    }
  };

  return (
    <div className="max-w-lg w-full !p-4 bg-gray-800 rounded">
      <h2 className="text-xl font-bold !mb-4">Send Email to Group</h2>
      <form onSubmit={handleSubmit} className="!space-y-4">
        <select value={group} onChange={(e) => setGroup(e.target.value)} className="w-full !p-2 rounded bg-gray-700 text-white">
          <option value="admins">Admins</option>
          <option value="subs">Subscribers</option>
          <option value="all">Everyone</option>
        </select>
        <input
          type="text"
          placeholder="Subject"
          value={subject}
          onChange={(e) => setSubject(e.target.value)}
          className="w-full !p-2 rounded bg-gray-700 text-white"
        />
        <textarea
          placeholder="Message"
          value={message}
          onChange={(e) => setMessage(e.target.value)}
          className="w-full !p-2 rounded bg-gray-700 text-white h-40"
        />
        <button
          type="submit"
          className="bg-amber-400 text-black !px-4 !py-2 rounded hover:bg-amber-500"
        >
          Send
        </button>
      </form>
      {status && <p className="!mt-4">{status}</p>}
    </div>
  );
}
