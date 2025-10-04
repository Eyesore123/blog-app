import React, { useEffect, useState } from "react";
import { useTheme } from "../context/ThemeContext";
import { Navbar } from "@/components/Navbar"; 
import axiosInstance from "../components/axiosInstance";

interface TriviaItem {
  id?: number;
  label: string;
  value: string;
}

export default function TriviaPage() {
  const { theme } = useTheme();
  const [trivia, setTrivia] = useState<TriviaItem[]>([]);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    // Scroll to top when page loads
    window.scrollTo({ top: 0 });

        async function fetchTrivia() {
        try {
            const response = await axiosInstance.get("/api/trivia"); // use API route
            const data = Array.isArray(response.data)
            ? response.data
            : response.data.trivia || [];
            setTrivia(data); // ✅ set the correct array
        } catch (err) {
            console.error("Failed to fetch trivia:", err);
            setTrivia([]); // fallback
        } finally {
            setLoading(false);
        }
        }

        fetchTrivia();
    }, []);

  return (
  <div className={`min-h-screen ${theme}`}>
    <Navbar />
    <main className="!p-4 md:!p-8 flex flex-col items-center">
      <div className="w-full max-w-2xl bg-[#5800FF]/10 rounded-lg shadow !p-6 md:!p-10 !mt-8">
        <h1 className="text-3xl font-bold !mb-12 text-center text-[#5800FF]">
          Trivia About Me 🎲
        </h1>

        {loading ? (
          <p className={`text-center opacity-70 ${theme === 'light' ? 'text-black' : 'text-white'}`}>
            Loading trivia...
          </p>
        ) : trivia.length === 0 ? (
          <p className={`text-center opacity-70 ${theme === 'light' ? 'text-black' : 'text-white'}`}>
            No trivia added yet.
          </p>
        ) : (
          <ul className="list-disc !ml-6 !mb-6 text-lg md:text-xl !space-y-3">
            {trivia.map((item, index) => (
              <li key={item.id ?? index} className={`!mb-4 ${theme === 'light' ? 'text-black' : 'text-white'}`}>
                <span className="font-semibold">{item.label}:</span> {item.value}
              </li>
            ))}
          </ul>
        )}
      </div>
    </main>
  </div>
);
}
