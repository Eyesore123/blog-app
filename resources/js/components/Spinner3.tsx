import React from "react";

export default function Spinner({ size = 72 }: { size?: number }) {
  return (
    <div
      className="flex items-center justify-center"
      style={{ width: size, height: size }}
    >
      <style>
        {`
          @keyframes spinner-gradient-rotate {
            0% { transform: rotate(0deg);}
            100% { transform: rotate(360deg);}
          }
          .spinner-gradient-arc {
            transform-origin: 50% 50%;
            animation: spinner-gradient-rotate 1.8s linear infinite;
            will-change: transform;
          }
        `}
      </style>
      <svg
        width={size}
        height={size}
        viewBox="0 0 50 50"
      >
        <defs>
          {/* Radial gradient for background */}
          <radialGradient id="spinner-bg" cx="50%" cy="50%" r="50%">
            <stop offset="0%" stopColor="#ffc600" stopOpacity="0.25" />
            <stop offset="70%" stopColor="#e900ff" stopOpacity="0.18" />
            <stop offset="100%" stopColor="#5800FF" stopOpacity="0.10" />
          </radialGradient>
          {/* Arc gradient */}
          <linearGradient id="spinner-gradient" x1="0%" y1="0%" x2="100%" y2="100%">
            <stop offset="0%" stopColor="#e900ff" />
            <stop offset="100%" stopColor="#ffc600" />
          </linearGradient>
        </defs>
        {/* Circular gradient background */}
        <circle
          cx="25"
          cy="25"
          r="20"
          fill="url(#spinner-bg)"
        />
        {/* Faint outline ring */}
        <circle
          cx="25"
          cy="25"
          r="20"
          fill="none"
          stroke="#5800FF"
          strokeWidth="3"
          opacity="0.13"
        />
        {/* Main gradient arc */}
        <circle
          cx="25"
          cy="25"
          r="20"
          fill="none"
          stroke="url(#spinner-gradient)"
          strokeWidth="6"
          strokeDasharray="80 50"
          strokeLinecap="round"
          className="spinner-gradient-arc"
        />
        {/* Center dot */}
        <circle
          cx="25"
          cy="25"
          r="4"
          fill="#ffc600"
          opacity="0.9"
        />
      </svg>
    </div>
  );
}