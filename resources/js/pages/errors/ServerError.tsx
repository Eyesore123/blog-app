import React from 'react';
import { Link } from '@inertiajs/react';
import { Navbar } from '@/components/Navbar';
import { useTheme } from '@/context/ThemeContext';

export default function ServerError() {
  const { theme } = useTheme();

  return (
    <div className={`min-h-160 ${theme}`}>
      <div className="min-h-160 bg-[var(--bg-primary)] text-[var(--text-primary)]">
        <Navbar />
        <div className="flex flex-col items-center justify-center px-4 py-16 md:py-32">
          <h1 className="text-6xl font-bold text-[#5800FF] mb-4">500</h1>
          <h2 className="text-2xl font-semibold mb-6">Server Error</h2>
          <p className="text-center max-w-md mb-8 opacity-80">
            Something went wrong on our servers. We're working to fix the issue.
          </p>
          <div className="space-x-4">
            <Link
              href="/"
              className="px-6 py-2 bg-[#5800FF] text-white rounded-lg hover:bg-[#E900FF] transition-colors"
            >
              Go Home
            </Link>
            <button
              onClick={() => window.location.reload()}
              className="px-6 py-2 border border-[#5800FF] text-[#5800FF] rounded-lg hover:bg-[#5800FF]/10 transition-colors"
            >
              Try Again
            </button>
          </div>
        </div>
      </div>
    </div>
  );
}
