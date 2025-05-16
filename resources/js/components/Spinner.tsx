import React from "react";

export default function Spinner({ size = 48 }: { size?: number }) {
  return (
    <div
      className="flex items-center justify-center"
      style={{ width: size, height: size }}
    >
      <style>
        {`
          @keyframes spinner-rotate {
            0% { transform: rotate(0deg);}
            100% { transform: rotate(360deg);}
          }
          .spinner-arc {
            transform-origin: 50% 50%;
            animation: spinner-rotate 1.6s linear infinite;
            will-change: transform;
          }
        `}
      </style>
      <svg
        width={size}
        height={size}
        viewBox="0 0 50 50"
        style={{ filter: "drop-shadow(0 0 8px #E900FF88)" }}
      >
        {/* Outer glowing ring */}
        <circle
          cx="25"
          cy="25"
          r="22"
          fill="none"
          stroke="#E900FF"
          strokeWidth="2"
          opacity="0.3"
        />
        {/* Main purple arc */}
        <circle
          cx="25"
          cy="25"
          r="20"
          fill="none"
          stroke="#5800FF"
          strokeWidth="6"
          strokeDasharray="31.4 31.4"
          strokeLinecap="round"
          className="spinner-arc"
        />
        {/* Pink arc with pulse animation */}
        <circle
          cx="25"
          cy="25"
          r="14"
          fill="none"
          stroke="#E900FF"
          strokeWidth="4"
          strokeDasharray="22 22"
          strokeDashoffset="11"
          strokeLinecap="round"
          opacity="0.8"
          className="animate-pulse"
        />
        {/* Center dot */}
        <circle
          cx="25"
          cy="25"
          r="3"
          fill="#E900FF"
          opacity="0.8"
        />
      </svg>
    </div>
  );
}