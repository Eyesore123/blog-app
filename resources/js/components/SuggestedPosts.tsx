import React, { useEffect, useState } from "react";
import { Link } from "@inertiajs/react";
import { useTheme } from "@/context/ThemeContext";

type SuggestedPost = {
  id: number;
  title: string;
  slug: string;
  image_path: string;
};

export default function SuggestedPosts({ slug }: { slug: string }) {
  const [suggested, setSuggested] = useState<SuggestedPost[]>([]);
  const { theme } = useTheme();

  useEffect(() => {
    const fetchSuggested = async () => {
      try {
        const res = await fetch(`/posts/${slug}/suggested`);
        const data: SuggestedPost[] = await res.json();

        // Shuffle using Fisher–Yates
        const shuffled = [...data];
        for (let i = shuffled.length - 1; i > 0; i--) {
          const j = Math.floor(Math.random() * (i + 1));
          [shuffled[i], shuffled[j]] = [shuffled[j], shuffled[i]];
        }

        // Only take up to 3
        setSuggested(shuffled.slice(0, 3));
      } catch (error) {
        console.error("Error fetching suggested posts:", error);
      }
    };

    fetchSuggested();
  }, [slug]);

  const handleImgError = (e: React.SyntheticEvent<HTMLImageElement, Event>) => {
    e.currentTarget.onerror = null;
    e.currentTarget.src = "/fallbackimage.jpg";
  };

  if (!suggested.length) return null; // parent handles the fallback message

  return (
    <div className="w-full flex flex-col items-center md:items-start !mt-6 md:!mt-8 !pt-4 md:!pt-6 border-t border-[#5800FF]/20 2xl:!ml-32 xl:w-4/5">
      <h3
        className={`font-semibold !text-lg lg:!text-2xl xl:!text-3xl ${
          theme === "light" ? "text-black" : "text-white"
        } !mb-6 lg:!mb-14 sm:!pl-10`}
      >
        Suggested posts for you:
      </h3>
      <ul className="flex flex-col !gap-4 w-full">
        {suggested.map((post) => (
          <li
            key={post.id}
            className="flex items-center bg-[#5800FF]/10 rounded-lg shadow !p-3 !pl-10 hover:shadow-lg transition-shadow w-full"
          >
            <Link href={`/posts/${post.slug}`} className="flex items-center w-full">
              <img
                src={post.image_path ? `/storage/${post.image_path}` : "/fallbackimage.jpg"}
                alt={post.title}
                onError={handleImgError}
                className="!w-14 !h-14 md:!w-16 md:!h-16 object-cover rounded-full bg-white/20 !mr-4"
                loading="lazy"
              />
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
