import React from 'react';

export function OfferComponent() {
  const contactUrl = "https://jonis-portfolio.netlify.app/contact";

  return (
    <div className="rounded-lg !mt-6 !pb-4 bg-[var(--bg-secondary)] p-4 shadow-md">
      <h3 className="font-semibold !mb-2">Special Offer</h3>
      <p className="text-sm !mb-4 opacity-80">
        Need help with your website? Or perhaps want to set up your own customized blog? 
        I'll provide support for free for a limited time! 
        <a 
          href={contactUrl} 
          target="_blank" 
          rel="noopener noreferrer" 
          className="text-[#E900FF] hover:text-[#5800FF] ml-1 underline"
        >
          Send me a message
        </a> and we'll see what I can do to help.
      </p>
    </div>
  );
}
