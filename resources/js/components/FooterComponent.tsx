import React from "react";

export function FooterComponent() {
  return (
    <footer className="w-full bg-[#5800FF]/10 text-[var(--text-primary)] rounded-t-lg shadow-md shadow-[#5800FF]/10 !pb-6 !px-4">
      <div className="mx-auto w-full flex flex-col items-center justify-center !gap-4">
        <p className="text-xs opacity-70 !mb-2 !pt-6 text-center">
          &copy; 2025 Joni Putkinen. All rights reserved.
        </p>
        <div className="flex gap-3 justify-center">
          <a
            href="https://www.linkedin.com/in/joni-putkinen-6658682a0/"
            target="_blank"
            rel="noopener noreferrer"
            className="hover:text-[#E900FF] text-[#5800FF] text-lg"
            aria-label="LinkedIn"
          >
            <img src="/In-Blue-128.png" alt="LinkedIn" className="!w-6 !h-6" />
          </a>
          <a
            href="https://joniputkinen.com/"
            target="_blank"
            rel="noopener noreferrer"
            className="hover:text-[#E900FF] text-[#5800FF] text-lg"
            aria-label="PortfolioLinkImage"
          >
            <img src="/favicon.ico" alt="PortfolioLinkImage" className="!w-6 !h-6" />
          </a>
        </div>
      </div>
      <div className="w-full text-center !mt-6 text-xs opacity-60">
        <span>
          Made with <span className="text-[#E900FF]">♥</span> in Finland
        </span>
      </div>
    </footer>
  );
}