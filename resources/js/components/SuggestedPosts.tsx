import React, { useEffect, useState } from "react";
import { Link } from "@inertiajs/react";
import { useTheme } from "@/context/ThemeContext";
import Spinner from "./Spinner4";

type SuggestedPost = {
  id: number;
  title: string;
  slug: string;
  image_path: string;
};

export default function SuggestedPosts({ slug }: { slug: string }) {
  const [suggested, setSuggested] = useState<SuggestedPost[]>([]);
  const [loadingImages, setLoadingImages] = useState<Record<number, boolean>>({});
  const [loading, setLoading] = useState(true);
  const { theme } = useTheme();

  useEffect(() => {
    const fetchSuggested = async () => {
      try {
        const res = await fetch(`/posts/${slug}/suggested`);
        const data: SuggestedPost[] = await res.json();

        // Shuffle + remove duplicates
        const shuffled = [...data];
        for (let i = shuffled.length - 1; i > 0; i--) {
          const j = Math.floor(Math.random() * (i + 1));
          [shuffled[i], shuffled[j]] = [shuffled[j], shuffled[i]];
        }

        const unique = Array.from(new Map(shuffled.map(p => [p.id, p])).values());
        const finalPosts = unique.slice(0, 3);

        // Initialize image loading states
        const initial: Record<number, boolean> = {};
        finalPosts.forEach(p => (initial[p.id] = true));
        setLoadingImages(initial);

        setSuggested(finalPosts);
      } catch (error) {
        console.error("Error fetching suggested posts:", error);
      } finally {
        setTimeout(() => setLoading(false), 0);
      }
    };

    fetchSuggested();
  }, [slug]);

  const handleImgLoad = (id: number) => {
    setTimeout(() => {
      setLoadingImages(prev => ({ ...prev, [id]: false }));
    }, 300);
  };

  const handleImgError = (e: React.SyntheticEvent<HTMLImageElement, Event>, id: number) => {
    e.currentTarget.onerror = null;
    e.currentTarget.src = "/favicon.png"; // fallback image
    setLoadingImages(prev => ({ ...prev, [id]: false }));
  };

  if (loading) {
    return (
      <div className="flex justify-center items-center w-full !py-8">
        <Spinner size={48} />
      </div>
    );
  }

  if (!suggested.length) return null;

  return (
    <div className="w-full flex flex-col items-center md:items-start !mt-6 md:!mt-8 !pt-4 md:!pt-6 border-t border-[#5800FF]/20 2xl:!ml-32 xl:!w-4/5">
      <h3
        className={`font-semibold text-lg lg:text-2xl xl:text-3xl ${
          theme === "light" ? "text-black" : "text-white"
        } !mb-6 lg:!mb-14 sm:!pl-10`}
      >
        Suggested posts for you:
      </h3>

      <ul className="flex flex-col gap-4 w-full">
        {suggested.map(post => (
          <li
            key={post.id}
            className="bg-[#5800FF]/10 rounded-lg shadow hover:shadow-lg transition-shadow w-full"
          >
            <Link
              href={`/posts/${post.slug}`}
              onClick={() => window.scrollTo({ top: 0, behavior: "smooth" })}
              className="flex items-center w-full !p-3 !pl-6 md:!pl-10 min-h-[72px] md:min-h-[90px] group"
            >
              {/* Image container */}
              <div className="relative w-[56px] h-[56px] md:w-[64px] md:h-[64px] flex-shrink-0 flex items-center justify-center !mr-4">
                {loadingImages[post.id] && (
                  <div className="absolute inset-0 flex items-center justify-center bg-black/10 rounded-full z-10">
                    <Spinner size={22} />
                  </div>
                )}

                <img
                  src={post.image_path ? `/storage/${post.image_path}` : "/favicon.png"}
                  alt={post.title}
                  onLoad={() => handleImgLoad(post.id)}
                  onError={e => handleImgError(e, post.id)}
                  className={`rounded-full object-cover w-full h-full transition-opacity duration-700 ${
                    loadingImages[post.id] ? "opacity-0" : "opacity-100"
                  } group-hover:scale-105 transition-transform duration-500`}
                />
              </div>

              {/* Title */}
              <span
                className={`${
                  theme === "light" ? "text-black" : "text-white"
                } font-medium hover:underline text-sm md:text-base line-clamp-2 !ml-6`}
              >
                {post.title}
              </span>
            </Link>
          </li>
        ))}
      </ul>
    </div>
  );
}
