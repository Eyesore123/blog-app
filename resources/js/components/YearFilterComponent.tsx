import React, { useState } from 'react';
import { router } from '@inertiajs/react';

interface Post {
  id: number;
  title: string;
  topic: string;
  created_at: string;
}

interface YearFilterComponentProps {
  posts: Post[];
}

const YearFilterComponent: React.FC<YearFilterComponentProps> = ({ posts = [] }) => {
  const [selectedYear, setSelectedYear] = useState<string | null>(null);
  const [selectedMonth, setSelectedMonth] = useState<string | null>(null);

  const groupedByYearAndMonth = posts.reduce((acc: Record<string, Record<string, Post[]>>, post: Post) => {
    const date = new Date(post.created_at);
    const year = date.getFullYear().toString();
    const month = date.toLocaleString('default', { month: 'long' });

    if (!acc[year]) acc[year] = {};
    if (!acc[year][month]) acc[year][month] = [];
    acc[year][month].push(post);
    return acc;
  }, {});

  const years = Object.keys(groupedByYearAndMonth).sort((a, b) => Number(b) - Number(a));

  return (
    <div className="rounded-lg !pb-4 !mt-8">
      <h3 className="font-semibold !mb-2">All Posts</h3>

      <ul className="!space-y-1">
        {years.map((year) => (
          <li key={year}>
            <button
              onClick={() => setSelectedYear(selectedYear === year ? null : year)}
              className={`w-full text-left !px-2 !py-1 rounded hover:bg-[#5800FF]/20 ${
                selectedYear === year ? 'bg-[#5800FF] font-semibold text-white' : ''
              }`}
            >
              <span className="font-medium">{year}</span>
              <span className="text-xs opacity-60 !ml-2">
                ({Object.values(groupedByYearAndMonth[year]).flat().length})
              </span>
            </button>

            {selectedYear === year && (
              <ul className="!ml-4 !mt-2 !space-y-1">
                {Object.keys(groupedByYearAndMonth[year]).map((month) => (
                  <li key={month}>
                    <button
                      onClick={() => setSelectedMonth(selectedMonth === month ? null : month)}
                      className={`w-full text-left !px-2 !py-1 rounded hover:bg-[#5800FF]/20 ${
                        selectedMonth === month ? 'bg-[#5800FF] font-semibold text-white' : ''
                      }`}
                    >
                      <span className="font-medium">{month}</span>
                      <span className="text-xs opacity-60 !ml-2">
                        ({groupedByYearAndMonth[year][month].length})
                      </span>
                    </button>

                    {selectedMonth === month && (
                      <ul className="!ml-4 !mt-2 space-y-1">
                        {groupedByYearAndMonth[year][month].map((post) => (
                          <li key={post.id}>
                            <button
                              onClick={() => router.visit(`/post/${post.id}`)}
                              className="w-full text-left !px-2 !py-1 rounded hover:bg-[#5800FF]/20"
                            >
                              <span className="font-medium">{post.title}</span>
                              <span className="text-xs opacity-60 block">{post.topic}</span>
                            </button>
                          </li>
                        ))}
                      </ul>
                    )}
                  </li>
                ))}
              </ul>
            )}
          </li>
        ))}
      </ul>
    </div>
  );
};

export default YearFilterComponent;
