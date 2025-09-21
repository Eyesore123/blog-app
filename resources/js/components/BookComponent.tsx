import { useState } from 'react';
import Spinner from "./Spinner4";

export function BookComponent() {
  const [imageLoading, setImageLoading] = useState(true);
  const [imageError, setImageError] = useState(false);
  const [imageAttempted, setImageAttempted] = useState(false);

  const imageUrl = "/JoblessDiaries.jpg";
  const fallbackImageUrl = "/fallbackimage.jpg";
  const amazonUrl = "https://www.amazon.de/dp/B0DHHFFX4X";

  const handleImageClick = () => {
    window.open(amazonUrl, "_blank", "noopener,noreferrer");
  };

  const hasValidImageUrl = imageUrl && !imageUrl.includes("undefined");

  return (
    <div className="rounded-lg !mt-6 !pb-4">
      <h3 className="font-semibold !mb-2">My Book: Jobless Diaries</h3>
      <div className="text-[14px] !mb-6 !mt-6 opacity-80 lg:w-4/5">
        Discover my book <span className="font-semibold">Jobless Diaries</span> – a candid journey through unemployment, resilience, and finding new purpose. Available on Amazon!
      </div>

      {hasValidImageUrl && (
        <div
          className="relative w-full flex flex-row justify-center items-center md:justify-start md:items-start !mt-4"
          style={{ width: '100%', maxWidth: '40rem', minHeight: '14rem' }}
        >
          {imageLoading && !imageError && (
            <div className="absolute left-1/2 top-1/2 -translate-x-1/2 -translate-y-1/2 z-10">
              <Spinner size={64} />
            </div>
          )}

          {!imageError && (
            <img
              src={imageUrl}
              alt="Jobless Diaries book cover"
              className="w-50 md:w-50 lg:w-50 h-auto cursor-pointer"
              style={{ display: imageLoading ? 'none' : 'block' }}
              onClick={handleImageClick}
              onLoad={() => {
                setImageLoading(false);
                setImageAttempted(true);
              }}
              onError={() => {
                if (!imageAttempted) {
                  setImageLoading(false);
                  setImageError(true);
                  setImageAttempted(true);
                  console.error('Image failed to load:', imageUrl);
                }
              }}
            />
          )}

          {imageError && (
            <div className="w-full flex flex-col items-center md:items-start">
              <img
                src={fallbackImageUrl}
                alt="Fallback placeholder"
                className="w-full md:w-100 lg:w-150 h-auto object-contain cursor-pointer"
                onClick={handleImageClick}
              />
            </div>
          )}
        </div>
      )}

      <div className='w-full inline-flex !gap-2 justify-center md:justify-start !mt-8 !mb-4'>
        <a
          href={amazonUrl}
          target="_blank"
          rel="noopener noreferrer"
          className="scale-115 md:scale-100 text-[#E900FF] hover:text-[#E900FF] transition-colors font-semibold"
        >
          → Buy on Amazon
        </a>
      </div>
      <div className="text-sm !mt-2">
      (Link points to Amazon.de; also available in other regions)
      </div>
      <div className="text-sm !mt-4">
          <span className="font-semibold">Or read the free PDF version of the book!</span>
          <div className="text-sm !mt-2">
          <button
            onClick={() => window.open('/JoblessDiaries.pdf', '_blank', 'noopener,noreferrer')}
            className="bg-pink-500 hover:bg-pink-600 text-white font-bold text-[16px] !py-2 !px-4 rounded-full !mt-6 !mb-2 relative overflow-visible">Download
          </button>
          </div>
      </div>
    </div>
  );
}