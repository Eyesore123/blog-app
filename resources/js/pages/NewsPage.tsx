import React, { useEffect, useState } from "react";
import { useTheme } from "../context/ThemeContext";
import { Navbar } from "@/components/Navbar"; 
import axiosInstance from "../components/axiosInstance";

interface NewsItem {
  id?: number;
  title: string;
  content: string;
  created_at?: string;
}

export default function NewsPage() {
  const { theme } = useTheme();
  const [news, setNews] = useState<NewsItem[]>([]);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    window.scrollTo({ top: 0 });

    async function fetchNews() {
      try {
        const response = await axiosInstance.get("/api/news/latest?all=true");
        setNews(response.data); // all news from backend
      } catch (err) {
        console.error("Failed to fetch news:", err);
        setNews([]);
      } finally {
        setLoading(false);
      }
    }

    fetchNews();
  }, []);

  return (
    <div className={`min-h-screen ${theme}`}>
      <Navbar />
      <main className="!p-4 md:!p-8 flex flex-col items-center">
        <div className="w-full max-w-2xl bg-[#5800FF]/10 rounded-lg shadow !p-6 md:!p-10 !mt-8">
          <h1 className="text-3xl font-bold !mb-12 text-center text-[#5800FF]">
            All News
          </h1>

          {loading ? (
            <p className={`text-center opacity-70 ${theme === 'light' ? 'text-black' : 'text-white'}`}>
              Loading news...
            </p>
          ) : news.length === 0 ? (
            <p className={`text-center opacity-70 ${theme === 'light' ? 'text-black' : 'text-white'}`}>
              No news yet.
            </p>
          ) : (
            <ul className="!space-y-6">
              {news.map((item, index) => (
                <li key={item.id ?? index} className={`!mb-4 ${theme === 'light' ? 'text-black' : 'text-white'}`}>
                  <h3 className="font-semibold text-lg md:text-xl">{item.title}</h3>
                  <p className="text-sm md:text-base opacity-90">{item.content}</p>
                  <p className="text-xs opacity-70">{item.created_at}</p>
                </li>
              ))}
            </ul>
          )}
        </div>
      </main>
    </div>
  );
}
