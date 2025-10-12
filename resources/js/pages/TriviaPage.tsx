import React, { useEffect, useState } from "react";
import { useTheme } from "../context/ThemeContext";
import { Navbar } from "@/components/Navbar";
import axiosInstance from "../components/axiosInstance";
import Spinner from "../components/Spinner4"; // ✅ Spinner4 loader

interface TriviaItem {
  id?: number;
  label: string;
  value: string;
}

export default function TriviaPage() {
  const { theme } = useTheme();
  const [trivia, setTrivia] = useState<TriviaItem[]>([]);
  const [loading, setLoading] = useState(true);

  // Image states
  const [imageLoading, setImageLoading] = useState(true);
  const [imageError, setImageError] = useState(false);
  const [imageAttempted, setImageAttempted] = useState(false);

  const imageUrl = "/omakuva_compressed.jpg";
  const fallbackImageUrl = "/Heroimage.png";

  useEffect(() => {
    window.scrollTo({ top: 0 });

    async function fetchTrivia() {
      try {
        const response = await axiosInstance.get("/api/trivia");
        const data = Array.isArray(response.data)
          ? response.data
          : response.data.trivia || [];
        setTrivia(data);
      } catch (err) {
        console.error("Failed to fetch trivia:", err);
        setTrivia([]);
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
        <div className="w-full max-w-4xl bg-[#5800FF]/10 rounded-lg shadow !p-6 md:!p-10 lg:!mt-8">
          <h1 className="!text-3xl md:!text-5xl font-bold !mb-12 text-center text-[#5800FF]">
            Trivia About Me
          </h1>

          {/* ✅ Image section */}
          <div
            className={`relative w-full flex justify-center items-center !my-10 transition-all duration-300`}
            style={{
              minHeight: imageLoading ? "200px" : "auto", // keeps spinner area spaced
            }}
          >
            {imageLoading && !imageError && (
              <div className="flex justify-center items-center p-4">
                <Spinner size={64} />
              </div>
            )}

            {!imageError && (
              <img
                src={imageUrl}
                alt="Joni's portrait"
                className="!rounded-sm shadow-md w-full md:w-3/4 lg:w-2/5 h-auto object-contain !mb-4 lg:!mt-8 lg:!mb-10"
                style={{ display: imageLoading ? "none" : "block" }}
                onLoad={() => {
                  setImageLoading(false);
                  setImageAttempted(true);
                }}
                onError={() => {
                  if (!imageAttempted) {
                    setImageLoading(false);
                    setImageError(true);
                    setImageAttempted(true);
                    console.error("Image failed to load:", imageUrl);
                  }
                }}
              />
            )}

            {imageError && (
              <img
                src={fallbackImageUrl}
                alt="Fallback placeholder"
                className="!rounded-sm shadow-md w-full md:w-3/4 lg:w-2/5 h-auto object-contain !mb-4"
              />
            )}
          </div>

          {/* ✅ Trivia section */}
          {loading ? (
            <p
              className={`text-center opacity-70 ${
                theme === "light" ? "text-black" : "text-white"
              }`}
            >
              Loading trivia...
            </p>
          ) : trivia.length === 0 ? (
            <p
              className={`text-center opacity-70 ${
                theme === "light" ? "text-black" : "text-white"
              }`}
            >
              No trivia added yet.
            </p>
          ) : (
            <ul
              className="!ist-disc !ist-inside !x-auto text-center !mb-6 !text-md lg:!text-lg !pace-y-4 flex flex-col items-center"
            >
              {trivia.map((item, index) => (
                <li
                  key={item.id ?? index}
                  className={`w-full md:w-3/4 lg:w-2/3 text-left leading-relaxed break-words ${
                    theme === "light" ? "text-black" : "text-white"
                  }`}
                >
                  <span className="font-semibold">{item.label}:</span>{" "}
                  {item.value}
                </li>
              ))}
            </ul>
          )}

          {/* ✅ Go back button */}
          <div className="flex w-full justify-center items-center">
            <button
              onClick={() => window.history.back()}
              className={`!px-6 !py-2 border !mt-4 lg:!mt-8 border-[#E900FF] text-[#E900FF] rounded-lg hover:bg-[#5800FF]/10 transition-colors sm:block`}
            >
              Go Back
            </button>
          </div>
        </div>
      </main>
    </div>
  );
}
