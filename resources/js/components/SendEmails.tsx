import React, { useState } from 'react';
import axiosInstance from './axiosInstance';

export default function SendEmails() {
  // Regular send-to-group form
  const [group, setGroup] = useState('admins');
  const [subject, setSubject] = useState('');
  const [message, setMessage] = useState('');
  const [status, setStatus] = useState('');
  // Test post notification form
  const [testEmail, setTestEmail] = useState('');
  const [testStatus, setTestStatus] = useState('');

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    setStatus(''); // clear previous status
    try {
      const res = await axiosInstance.post(
        '/admin/send-emails',
        { group, subject, message },
        { headers: { 'Content-Type': 'application/json' } }
      );

      if (res.data.success) {
        setStatus('✅ Emails queued successfully!');
        setSubject('');
        setMessage('');
      } else {
        setStatus(`❌ Failed: ${res.data.message || 'Unknown error'}`);
      }
    } catch (err: any) {
      if (err.response?.data?.message) {
        setStatus(`❌ Failed: ${err.response.data.message}`);
      } else if (err.response?.data?.errors) {
        // Laravel validation errors
        const errors = Object.values(err.response.data.errors)
          .flat()
          .join(' ');
        setStatus(`❌ Validation failed: ${errors}`);
      } else {
        setStatus('❌ Failed to send emails.');
      }
    }
  };

  const handleTestSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    setTestStatus('');
    try {
      const res = await axiosInstance.post('/admin/test-post-notification', { email: testEmail });
      setTestStatus(`✅ ${res.data.message}`);
      setTestEmail('');
    } catch (err: any) {
      if (err.response?.data?.message) {
        setTestStatus(`❌ Failed: ${err.response.data.message}`);
      } else {
        setTestStatus('❌ Failed to send test post notification.');
      }
    }
  };

  return (
    <div className="max-w-lg w-full !p-4 bg-gray-800 rounded !space-y-8">
      {/* Send Email to Group Form */}
      <div>
        <h2 className="text-xl font-bold !mb-4">Send Email to Group</h2>
        <form onSubmit={handleSubmit} className="!space-y-4">
          <select
            value={group}
            onChange={(e) => setGroup(e.target.value)}
            className="w-full !p-2 rounded bg-gray-700 text-white"
          >
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

      {/* Test Post Notification Form */}
      <div>
        <h2 className="text-xl font-bold !mb-4">Test Post Send via Email</h2>
        <form onSubmit={handleTestSubmit} className="!space-y-4">
          <input
            type="email"
            placeholder="Recipient Email"
            value={testEmail}
            onChange={(e) => setTestEmail(e.target.value)}
            className="w-full !p-2 rounded bg-gray-700 text-white"
            required
          />
          <button
            type="submit"
            className="bg-green-400 text-black !px-4 !py-2 rounded hover:bg-green-500"
          >
            Send Test Post Notification
          </button>
        </form>
        {testStatus && <p className="!mt-4">{testStatus}</p>}
      </div>
    </div>
  );
}
