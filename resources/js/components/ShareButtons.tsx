interface ShareButtonsProps {
  postUrl: string;
  postTitle: string;
}

export default function ShareButtons({ postUrl, postTitle }: ShareButtonsProps) {
  const encodedUrl = encodeURIComponent(postUrl);
  const encodedTitle = encodeURIComponent(postTitle);

  return (
    <div className="gap-2 md:gap-2 !mt-4 flex flex-row justify-center items-center  md:justify-start md:items-start">
      <a
        href={`https://twitter.com/intent/tweet?url=${encodedUrl}&text=${encodedTitle}`}
        target="_blank"
        rel="noopener noreferrer"
        className="!px-2 !py-1 bg-black border-1 border-white text-white rounded hover:opacity-80"
      >
        Share on X
      </a>
      <a
        href={`https://www.facebook.com/sharer/sharer.php?u=${encodedUrl}`}
        target="_blank"
        rel="noopener noreferrer"
        className="!px-2 !py-1 bg-blue-700 text-white rounded hover:opacity-80"
      >
        Share on Facebook
      </a>
      <a
        href={`https://www.linkedin.com/shareArticle?url=${encodedUrl}&title=${encodedTitle}`}
        target="_blank"
        rel="noopener noreferrer"
        className="!px-2 !py-1 bg-[#0A66C2] text-white rounded hover:opacity-80"
      >
        Share on LinkedIn
      </a>
      <a
        href={`https://www.reddit.com/submit?url=${encodedUrl}&title=${encodedTitle}`}
        target="_blank"
        rel="noopener noreferrer"
        className="!px-2 !py-1 bg-[#FF4500] text-white rounded hover:opacity-80"
      >
        Share on Reddit
      </a>
    </div>
  );
}
