import React from 'react';
import { Link } from '@inertiajs/react';
import { Navbar } from '@/components/Navbar';
import { useTheme } from '@/context/ThemeContext';

interface ErrorProps {
  status: number;
  message?: string;
}

export default function Error({ status, message }: ErrorProps) {
  const { theme } = useTheme();
  
  const defaultMessages: Record<number, string> = {
    404: "The page you are looking for doesn't exist or has been moved.",
    403: "You don't have permission to access this resource.",
    500: "Something went wrong on our servers. We're working to fix the issue.",
    503: "The service is temporarily unavailable. Please try again later.",
  };

  const errorMessage = message || defaultMessages[status] || "An error occurred.";

  return (
    <div className={`min-h-160 ${theme}`}>
      <div className="min-h-160 bg-[var(--bg-primary)] text-[var(--text-primary)]">
        <Navbar />
        <div className="flex flex-col items-center justify-center !px-4 !py-16 !md:py-32 min-h-220">
          <h1 className="text-6xl font-bold text-[#5800FF] !mb-4">{status}</h1>
          <h2 className="text-2xl font-semibold !mb-6">
            {status === 404 ? 'Page Not Found' : 
             status === 403 ? 'Access Denied' : 
             status === 500 ? 'Server Error' : 
             status === 503 ? 'Service Unavailable' : 'Error'}
          </h2>
          <p className="text-center max-w-md !mb-8 opacity-80">
            {errorMessage}
          </p>
          <div className="!space-x-4">
            <Link
              href="/"
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