import { useState } from 'react';
import Spinner from "./Spinner4";

export function PortfolioLink() {
  const [imageLoading, setImageLoading] = useState(true);
  const [imageError, setImageError] = useState(false);
  const [imageAttempted, setImageAttempted] = useState(false);

  const imageUrl = "/Heroimage.png";
  const fallbackImageUrl = "/fallbackimage.jpg";
  const portfolioUrl = "https://jonis-portfolio.netlify.app/";

  const handleImageClick = () => {
    window.open(portfolioUrl, "_blank", "noopener,noreferrer");
  };

  const hasValidImageUrl = imageUrl && !imageUrl.includes("undefined");

  return (
    <div className="rounded-lg !mt-6 !pb-4">
      <h3 className="font-semibold !mb-2">Portfolio</h3>
      <p className="text-sm !mb-6 !mt-6 opacity-80">
        Visit my portfolio site to see awesome web development projects and designs.
      </p>

      {hasValidImageUrl && (
        <div
          className="relative w-full flex flex-row justify-center items-center lg:justify-start lg:items-start !mt-4"
          style={{ width: '100%', maxWidth: '40rem', minHeight: '16rem' }}
        >
          {imageLoading && !imageError && (
            <div className="absolute left-1/2 top-1/2 -translate-x-1/2 -translate-y-1/2 z-10">
              <Spinner size={64} />
            </div>
          )}

          {!imageError && (
            <img
              src={imageUrl}
              alt="Portfolio hero"
              className="w-full md:w-50 lg:w-50 h-auto cursor-pointer"
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
            <div className="w-full flex flex-col items-center lg:items-start">
              <img
                src={fallbackImageUrl}
                alt="Fallback placeholder"
                className="w-full md:w-100 lg:w-150 h-auto object-contain cursor-pointer"
                onClick={handleImageClick}
              />
              {/* <p className="text-gray-500 text-sm !mt-10">Image unavailable</p> */}
            </div>
          )}
        </div>
      )}

      <a
        href={portfolioUrl}
        target="_blank"
        rel="noopener noreferrer"
        className="inline-block text-[#5800FF] hover:text-[#E900FF] transition-colors"
      >
        → Visit Portfolio
      </a>
    </div>
  );
}
