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

    // Group posts by year
    const groupedByYear = posts.reduce((acc: Record<string, Post[]>, post: Post) => {
        const year = new Date(post.created_at).getFullYear().toString();
        if (!acc[year]) {
            acc[year] = [];
        }
        acc[year].push(post);
        return acc;
    }, {});

    // Year in desc order
    const years = Object.keys(groupedByYear).sort((a, b) => Number(b) - Number(a));

    return (
        <div className="rounded-lg !pb-4 !mt-8">
          <h3 className="font-semibold !mb-2">Posts by Year</h3>
    
          <ul className="!space-y-1">
            {years.map((year) => (
              <li key={year}>
                <button
                  onClick={() => setSelectedYear(selectedYear === year ? null : year)}
                  className={`w-full text-left !px-2 !py-1 rounded hover:bg-[#5800FF]/20 ${
                    selectedYear === year ? 'bg-[#5800FF] font-semibold' : ''
                  }`}
                >
                  <span className="font-medium">{year}</span>
                  <span className="text-xs opacity-60 !ml-2">({groupedByYear[year].length})</span>
                </button>
                {selectedYear === year && (
                  <ul className="ml-4 mt-2 space-y-1">
                    {groupedByYear[year].map((post) => (
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
        </div>
      );
    };
    
    export default YearFilterComponent;