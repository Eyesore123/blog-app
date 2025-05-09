import React, { useEffect, useState } from 'react';
import { router } from '@inertiajs/react';
import axiosInstance from './axiosInstance'; // Ensure you have a configured Axios instance

const ArchivesComponent: React.FC = () => {
  const [years, setYears] = useState<number[]>([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);

  useEffect(() => {
    async function fetchYears() {
      try {
        const response = await axiosInstance.get('/api/archives/years'); // Ensure this matches your backend route
        if (response.data && Array.isArray(response.data.years)) {
          setYears(response.data.years);
        } else {
          throw new Error('Invalid response format');
        }
      } catch (err) {
        console.error('Failed to fetch years:', err);
        setError('Failed to load archives. Please try again later.');
      } finally {
        setLoading(false);
      }
    }

    fetchYears();
  }, []);

  return (
    <div className="rounded-lg !mt-6 !pb-4">
      <h3 className="font-semibold !mb-2">Archives</h3>
      {loading ? (
        <p className="text-sm opacity-60 italic">Loading...</p>
      ) : error ? (
        <p className="text-sm text-red-500 italic">{error}</p>
      ) : (
        <ul className="!space-y-1 max-h-48 overflow-y-auto">
          {years.length > 0 ? (
            years.map((year) => (
              <li key={year}>
                <button
                  onClick={() => router.visit(`/archives/${year}`)}
                  className="w-full text-left !px-2 !py-1 rounded hover:bg-[#5800FF]/20"
                >
                  <span className="font-medium">{year}</span>
                </button>
              </li>
            ))
          ) : (
            <li className="text-sm opacity-60 italic">No archives available</li>
          )}
        </ul>
      )}
    </div>
  );
};

export default ArchivesComponent;