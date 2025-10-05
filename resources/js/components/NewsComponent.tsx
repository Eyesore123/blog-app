import React, { useEffect, useState } from "react";
import { useTheme } from "../context/ThemeContext";
import { router } from '@inertiajs/react';
import axiosInstance from "../components/axiosInstance";
import { on } from "events";

interface NewsItem {
  id?: number;
  title: string;
}

export default function NewsComponent() {
  const { theme } = useTheme();
  const [news, setNews] = useState<NewsItem[]>([]);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    async function fetchNews() {
      try {
        const response = await axiosInstance.get("/api/news/latest");
        setNews(response.data); //limited to 2 in backend
      } catch (err) {
        console.error("Failed to fetch news:", err);
        setNews([]);
      } finally {
        setLoading(false);
      }
    }
    fetchNews();
  }, []);

  const goToNews = () => {
    router.visit('/news', {
      preserveScroll: false,
      onSuccess: () => window.scrollTo({ top: 0 }),
    });
  };


  return (
    <div className={`rounded-lg !pt-2 !mb-10 text-left relative overflow-visible ${theme}`}>
      <h3 className='font-semibold !mt-2 !mb-2'>News</h3>
      <div className="text-[14px] !mb-6 !mt-4 opacity-100">📰 Latest Updates</div>

      {loading ? (
        <p className={`text-[13px] opacity-70 ${theme === 'light' ? 'text-black' : 'text-white'}`}>
          Loading news...
        </p>
      ) : news.length === 0 ? (
        <p className={`text-[13px] opacity-70 ${theme === 'light' ? 'text-black' : 'text-white'}`}>
          No news yet.
        </p>
      ) : (
        <ul className="list-disc !ml-6 !mb-6 text-sm md:text-base !space-y-2">
          {news.map((item, index) => (
            <li key={item.id ?? index} className={`!mb-2 cursor-pointer ${theme === 'light' ? 'text-black' : 'text-white'}`} onClick={() => goToNews()}>
                {item.title}
            </li>
          ))}
        </ul>
      )}

      <button
        onClick={goToNews}
        className="text-[#E900FF] hover:text-[#5800FF] font-semibold !mt-4 text-sm md:text-base"
      >
        → Check out all news
      </button>
    </div>
  );
}
