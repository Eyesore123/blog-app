import { useState } from 'react';

export function RssSubscribeLink() {
  const [copied, setCopied] = useState(false);
  const feedUrl = `${window.location.origin}/feed`;

  const copyToClipboard = () => {
    navigator.clipboard.writeText(feedUrl);
    setCopied(true);
    setTimeout(() => setCopied(false), 2000);
  };

  return (
    <div className="rounded-lg !mt-6 !pb-4">
          <h3 className="font-semibold !mb-2">RSS Feed</h3>
            <div className="!mt-4 flex flex-col gap-2">
            <a
                href="/feed"
                target="_blank"
                rel="noopener noreferrer"
                className="flex items-center text-[#5800FF] hover:text-[#E900FF] transition-colors"
            >
                <svg className="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 24 24">
                <path d="M4.26 17.52a2.74 2.74 0 105.48 0 2.74 2.74 0 00-5.48 0zm-2.5 5.5h2a20 20 0 0120-20v-2A22 22 0 001.76 23.02zm6.99 0h2a13 13 0 0113-13v-2a15 15 0 00-15 15z"/>
                </svg>
                &nbsp;Subscribe via RSS
            </a>

            <button
                onClick={copyToClipboard}
                className="flex items-center !w-50 md:!w-70 !px-3 !py-1 border border-[#5800FF] text-[#5800FF] hover:text-[#E900FF] transition-colors rounded hover:bg-[#5800FF]/10"
            >
             Copy RSS Link
            </button>

            {copied && <span className="text-sm text-green-500">Copied!</span>}
            </div>
    </div>
  );
}
