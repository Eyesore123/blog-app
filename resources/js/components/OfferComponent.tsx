import React, { useRef, useState, useEffect } from 'react';
import { useTheme } from '../context/ThemeContext';
import { Play, Pause, VolumeX, Volume2 } from 'lucide-react';

export function OfferComponent() {
  const contactUrl = "https://jonis-portfolio.netlify.app/contact";
  const videoRef = useRef<HTMLVideoElement>(null);

  const { theme } = useTheme();

  const [muted, setMuted] = useState(true);
  const [playing, setPlaying] = useState(true);

  // set base volume once after mount
  useEffect(() => {
    if (videoRef.current) {
      videoRef.current.volume = 0.5; // 50% base volume
    }
  }, []);

  const toggleMute = () => {
    if (videoRef.current) {
      videoRef.current.muted = !muted;
      setMuted(!muted);
    }
  };

  const togglePlay = () => {
    if (videoRef.current) {
      if (playing) {
        videoRef.current.pause();
      } else {
        videoRef.current.play();
      }
      setPlaying(!playing);
    }
  };

  const textColor = theme === 'dark' ? 'text-white' : 'text-black';

  return (
    <div className="relative rounded-lg !mt-6 !mb-10 !pb-4 overflow-hidden border border-[#E900FF] shadow-md">
      {/* Background video */}
      <video
        ref={videoRef}
        src="/Heroedit.mp4"
        preload="none"
        autoPlay
        muted={muted}
        loop
        playsInline
        className="absolute top-0 left-0 w-full h-full object-cover opacity-30"
      />

      {/* Overlay content */}
      <div className={`relative z-10 !p-4 ${textColor}`}>
        <h3 className="font-semibold !mb-2 text-[#E900FF] underline">
          Special Offer!
        </h3>
        <p className="text-sm !mb-4 opacity-90">
          Need help with your website? Or perhaps want to set up your own customized blog?
          I'll provide support for free for a limited time!
          <a
            href={contactUrl}
            target="_blank"
            rel="noopener noreferrer"
            className="text-[#E900FF] hover:text-[#5800FF] !ml-1"
          >
            Send me a message
          </a> and I'll see if I can do something to help.
        </p>

        {/* Controls */}
        <div className="flex !gap-3 !mt-2">
          <button
            onClick={togglePlay}
            className="!p-2 rounded-full bg-white/70 hover:bg-white text-black shadow transition-colors"
            aria-label={playing ? 'Pause Video' : 'Play Video'}
          >
            {playing ? <Pause size={18} /> : <Play size={18} />}
          </button>
          <button
            onClick={toggleMute}
            className="!p-2 rounded-full bg-white/70 hover:bg-white text-black shadow transition-colors"
            aria-label={muted ? 'Unmute Audio' : 'Mute Audio'}
          >
            {muted ? <VolumeX size={18} /> : <Volume2 size={18} />}
          </button>
        </div>
      </div>
    </div>
  );
}
