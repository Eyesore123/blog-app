import React, { useState, useEffect } from "react";

export function InfoBanner() {
  const [banner, setBanner] = useState<{ message: string; is_visible: boolean }>({
    message: '',
    is_visible: false, // hidden by default
  });
  const [visible, setVisible] = useState(true);

  useEffect(() => {
    fetch('/api/info-banner')
      .then(res => res.json())
      .then(data => {
        if (data?.is_visible) setBanner(data); // only set if backend says visible
      });
  }, []);

  if (!banner.is_visible || !visible) return null;

  return (
    <div
      className="w-full relative flex items-center justify-center !px-4 !py-2 bg-[#0074D9] text-white font-semibold shadow-md"
      style={{ minHeight: "48px" }}
    >
      <div className="flex items-center !gap-2 mx-auto">
        <svg width="24" height="24" fill="none" viewBox="0 0 24 24">
          <circle cx="12" cy="12" r="12" fill="#fff" opacity="0.2"/>
          <path d="M12 8.5a1 1 0 100-2 1 1 0 000 2zm1 2.5h-2v6h2v-6z" fill="#fff"/>
        </svg>
        <span>{banner.message}</span>
      </div>

      <button
        onClick={() => setVisible(false)}
        className="absolute !right-4 top-1/2 -translate-y-1/2 w-8 h-8 flex items-center justify-center rounded-full bg-white/10 hover:bg-white/40 text-white hover:text-[#0074D9] text-lg font-bold transition-colors focus:outline-none leading-none !p-0"
        aria-label="Close info banner"
      >
        ×
      </button>
    </div>
  );
}
