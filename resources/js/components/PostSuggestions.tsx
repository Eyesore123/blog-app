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
  const { theme } = useTheme();

  useEffect(() => {
    const fetchSuggested = async () => {
      try {
        const res = await fetch(`/posts/${slug}/suggested`);
        const data: SuggestedPost[] = await res.json();

        const shuffled = [...data];
        for (let i = shuffled.length - 1; i > 0; i--) {
          const j = Math.floor(Math.random() * (i + 1));
          [shuffled[i], shuffled[j]] = [shuffled[j], shuffled[i]];
        }

        const unique = Array.from(new Map(shuffled.map(p => [p.id, p])).values());
        const finalPosts = unique.slice(0, 3);

        // Mark all as loading initially
        const initial: Record<number, boolean> = {};
        finalPosts.forEach(p => (initial[p.id] = true));
        setLoadingImages(initial);

        setSuggested(finalPosts);
      } catch (error) {
        console.error("Error fetching suggested posts:", error);
      }
    };

    fetchSuggested();
  }, [slug]);

  const handleImgLoad = (id: number) => {
    // Force spinner to show for at least 500ms
    const MIN_SPINNER_TIME = 500;
    const start = performance.now();

    const checkSpinner = () => {
      const elapsed = performance.now() - start;
      if (elapsed >= MIN_SPINNER_TIME) {
        setLoadingImages(prev => ({ ...prev, [id]: false }));
      } else {
        requestAnimationFrame(checkSpinner);
      }
    };

    requestAnimationFrame(checkSpinner);
  };

  const handleImgError = (e: React.SyntheticEvent<HTMLImageElement, Event>, id: number) => {
    e.currentTarget.onerror = null;
    e.currentTarget.src = "/fallbackimage.jpg";
    setLoadingImages(prev => ({ ...prev, [id]: false }));
  };

  if (!suggested.length) return null;

  return (
    <div className="w-full flex flex-col items-center md:items-start !mt-6 md:!mt-8 !pt-4 md:!pt-6 border-t border-[#5800FF]/20 2xl:!ml-32 xl:w-4/5">
      <h3 className={`font-semibold !text-lg lg:!text-2xl xl:!text-3xl ${theme === "light" ? "text-black" : "text-white"} !mb-6 lg:!mb-14 sm:!pl-10`}>
        Suggested posts for you:
      </h3>

      <ul className="flex flex-col !gap-4 w-full">
        {suggested.map(post => (
          <li key={post.id} className="flex items-center bg-[#5800FF]/10 rounded-lg shadow !p-3 !pl-10 hover:shadow-lg transition-shadow w-full">
            <Link href={`/posts/${post.slug}`} className="flex items-center w-full" onClick={() => window.scrollTo({ top: 0, behavior: "smooth" })}>
              <div className="relative w-14 h-14 md:w-16 md:h-16 mr-4 flex items-center justify-center">
                {/* Spinner always visible while loading */}
                {loadingImages[post.id] && (
                  <div className="absolute inset-0 flex items-center justify-center z-20 rounded-full bg-black/10">
                    <Spinner size={24} />
                  </div>
                )}

                {/* Only render image after load */}
                <img
                  src={post.image_path ? `/storage/${post.image_path}` : "/fallbackimage.jpg"}
                  alt={post.title}
                  style={{ display: loadingImages[post.id] ? 'none' : 'block' }}
                  onLoad={() => handleImgLoad(post.id)}
                  onError={(e) => handleImgError(e, post.id)}
                  className="object-cover rounded-full w-full h-full"
                />
              </div>
              <span className={`${theme === "light" ? "text-black" : "text-white"} font-medium hover:underline text-sm md:text-base line-clamp-2 !ml-6`}>
                {post.title}
              </span>
            </Link>
          </li>
        ))}
      </ul>
    </div>
  );
}
