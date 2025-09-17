import React from 'react';
import { Link } from '@inertiajs/react';
import { Navbar } from '@/components/Navbar';
import { useTheme } from '@/context/ThemeContext';

interface ErrorProps {
  status: number;
  message?: string;
  redirect?: string; // optional custom redirect
}

export default function Error({ status, message, redirect = '/' }: ErrorProps) {
  const { theme } = useTheme();

  const defaultMessages: Record<number, string> = {
    403: "You don't have permission to access this resource.",
    404: "The page you are looking for doesn't exist or has been moved.",
    500: "Something went wrong on our servers. We're working to fix the issue.",
    503: "The service is temporarily unavailable. Please try again later.",
  };

  const defaultTitles: Record<number, string> = {
    403: "Access Denied",
    404: "Page Not Found",
    500: "Server Error",
    503: "Service Unavailable",
  };

  const errorMessage = message || defaultMessages[status] || "An unexpected error occurred.";
  const errorTitle = defaultTitles[status] || `Error ${status}`;

  return (
    <div className={`min-h-screen ${theme}`}>
      <div className="min-h-screen bg-[var(--bg-primary)] text-[var(--text-primary)] flex flex-col">
        <Navbar />
        <div className="flex flex-col flex-1 items-center justify-center !px-4 !py-16 md:!py-32">
          <h1 className="text-7xl font-extrabold text-[#5800FF] !mb-4">{status}</h1>
          <h2 className="text-3xl font-semibold !mb-6">{errorTitle}</h2>
          <p className="text-center max-w-md !mb-8 opacity-80">{errorMessage}</p>

          <div className="flex !space-x-4">
            <Link
              href={redirect}
              className="!px-6 !py-2 bg-[#5800FF] text-white rounded-lg hover:bg-[#E900FF] transition-colors"
            >
              Go Home
            </Link>
            <button
              onClick={() => window.history.back()}
              className="!px-6 !py-2 border border-[#5800FF] text-[#5800FF] rounded-lg hover:bg-[#5800FF]/10 transition-colors"
            >
              Go Back
            </button>
          </div>
        </div>
      </div>
    </div>
  );
}
