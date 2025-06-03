import { useState } from 'react';
import Spinner from "./Spinner4";

export function WebsiteAnalyzerLink(): React.ReactElement {
  const [imageLoading, setImageLoading] = useState<boolean>(true);
  const [imageError, setImageError] = useState<boolean>(false);
  const [imageAttempted, setImageAttempted] = useState<boolean>(false);

  const imageUrl: string = "/tool.png";
  const fallbackImageUrl: string = "/fallbackimage.jpg";
  const analyzerUrl: string = "https://adorable-crocodile-181.convex.app";

  const handleImageClick = (): void => {
    window.open(analyzerUrl, "_blank", "noopener,noreferrer");
  };

  const hasValidImageUrl: boolean = !!imageUrl && !imageUrl.includes("undefined");

  return (
    <div className="rounded-lg !mt-8 !pb-4">
      <h3 className="font-semibold !mb-2">Website Analyzer</h3>
      <div className="text-[14px] !mb-6 !mt-6 opacity-80 lg:w-4/5">
        Try my free Website Analyzer! Get insights about your website's performance and structure.
        My Website Analyzer dives deep into your site — from responsiveness and SEO to speed,
        security, and more. It scores 10 key areas to give you a clear, actionable snapshot of
        your website’s performance and improvements. Results can be downloaded as a PDF report or a JSON. Check the "how assessment is made" tab to see more details about assessment criteria.
      </div>

      {hasValidImageUrl && (
        <div
          className="!w-2/3 flex flex-row justify-center items-center lg:justify-start lg:items-start !mt-4"
          style={{ width: '100%', maxWidth: '40rem', minHeight: '11rem' }}
        >
          {imageLoading && !imageError && (
            <div className="absolute left-1/2 top-1/2 -translate-x-1/2 -translate-y-1/2 z-10">
              <Spinner size={64} />
            </div>
          )}

          {!imageError && (
            <img
              src={imageUrl}
              alt="Website Analyzer hero"
              className="w-full h-auto cursor-pointer"
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
            </div>
          )}
        </div>
      )}

      <div className='w-full inline-flex justify-center md:justify-start'>
        <a
          href={analyzerUrl}
          target="_blank"
          rel="noopener noreferrer"
          className="scale-115 md:scale-100 !pt-0 md:!mt-0 text-[#E900FF] md:text-[#5800FF] hover:text-[#E900FF] transition-colors"
        >
          → Analyze a Website
        </a>
      </div>
    </div>
  );
}
