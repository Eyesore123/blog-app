import React, { useState, useEffect } from "react";

export function InfoBanner({
  message = "Currently there's some issues with post sending. Fix is on the way. Thank you for your patience!",
  duration = 99999999999
}) {
  const [visible, setVisible] = useState(true);

  useEffect(() => {
    const timer = setTimeout(() => setVisible(false), duration);
    return () => clearTimeout(timer);
  }, [duration]);

  if (!visible) return null;


return (
  <div className="w-full relative flex items-center justify-center !px-4 !py-2 bg-[#0074D9] text-white font-semibold shadow-md"
       style={{ minHeight: "48px" }}>
    <div className="flex items-center gap-2 mx-auto">
      {/* Info icon */}
      <svg width="24" height="24" fill="none" viewBox="0 0 24 24">
        <circle cx="12" cy="12" r="12" fill="#fff" opacity="0.2"/>
        <path d="M12 8.5a1 1 0 100-2 1 1 0 000 2zm1 2.5h-2v6h2v-6z" fill="#fff"/>
      </svg>
      <span>{message}</span>
    </div>
    {/* Close button */}
    <button
      onClick={() => setVisible(false)}
      className="absolute right-4 top-1/2 -translate-y-1/2 w-8 h-8 flex items-center justify-center rounded-full bg-white/10 hover:bg-white/40 text-white hover:text-[#0074D9] text-lg font-bold transition-colors focus:outline-none leading-none p-0"
      aria-label="Close info banner"
    >
      ×
    </button>
  </div>
);
}