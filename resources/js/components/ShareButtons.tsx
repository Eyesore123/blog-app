// resources/js/components/ShareButtons.tsx

export default function ShareButtons({ postUrl, postTitle }) {
  const encodedUrl = encodeURIComponent(postUrl);
  const encodedTitle = encodeURIComponent(postTitle);

  return (
    <div className="flex gap-2 !mt-4">
      <a
        href={`https://twitter.com/intent/tweet?url=${encodedUrl}&text=${encodedTitle}`}
        target="_blank"
        rel="noopener noreferrer"
        className="!px-2 !py-1 bg-blue-500 text-white rounded hover:bg-blue-600"
      >
        Share on X
      </a>
      <a
        href={`https://www.facebook.com/sharer/sharer.php?u=${encodedUrl}`}
        target="_blank"
        rel="noopener noreferrer"
        className="!px-2 !py-1 bg-blue-700 text-white rounded hover:bg-blue-800"
      >
        Share on Facebook
      </a>
    </div>
  );
}
