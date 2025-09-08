import React, { useState, useEffect } from 'react';
import { createPortal } from 'react-dom';
import { useTheme } from '../context/ThemeContext';

interface AlertProps {
  message: string;
  type: 'success' | 'error' | 'info' | 'warning';
  duration?: number;
  onClose?: () => void;
}

const CustomAlert: React.FC<AlertProps> = ({
  message,
  type,
  duration = 4000,
  onClose
}) => {
  const [isVisible, setIsVisible] = useState(true);
  const { theme } = useTheme();

  const colors = {
    success: {
      bg: 'bg-[#5800FF]/20 backdrop-blur-sm',
      border: 'border-[#5800FF]',
      text: 'text-[#5800FF]',
      bar: 'bg-[#5800FF]',
      icon: '✓'
    },
    error: {
      bg: 'bg-[#E900FF]/20 backdrop-blur-sm',
      border: 'border-[#E900FF]',
      text: 'text-[#E900FF]',
      bar: 'bg-[#E900FF]',
      icon: '✕'
    },
    info: {
      bg: 'bg-[#5800FF]/20 backdrop-blur-sm',
      border: 'border-[#5800FF]',
      text: 'text-[#5800FF]',
      bar: 'bg-[#5800FF]',
      icon: 'ℹ'
    },
    warning: {
      bg: 'bg-[#E900FF]/20 backdrop-blur-sm',
      border: 'border-[#E900FF]',
      text: 'text-[#E900FF]',
      bar: 'bg-[#E900FF]',
      icon: '⚠'
    }
  };

  useEffect(() => {
    const timer = setTimeout(() => {
      setIsVisible(false);
      if (onClose) onClose();
    }, duration);

    return () => clearTimeout(timer);
  }, [duration, onClose]);

  if (!isVisible) return null;

  const color = colors[type];

  return createPortal(
    <div className="fixed bottom-6 left-1/2 transform -translate-x-1/2 z-50 max-w-md animate-slide-up">
      <div className={`rounded-lg ${color.bg} border ${color.border} shadow-lg !p-4 flex items-start`}>
        <div className={`${color.text} font-bold !mr-3 text-xl`}>{color.icon}</div>
        <div className="flex-1">
          <p className={`font-medium ${theme === "dark" ? "text-white" : "text-green-800"}`}>
            {message}
          </p>

          <div className="w-full h-1 bg-gray-200 rounded-full !mt-2 overflow-hidden">
            <div
              className={`h-full ${color.bar}`}
              style={{
                transformOrigin: 'left',
                animation: `shrink ${duration}ms linear forwards`
              }}
            />
          </div>
        </div>
        {/* Close button, in case you want to use it */}
        {/* <button
          onClick={() => {
            setIsVisible(false);
            if (onClose) onClose();
          }}
          className={`!ml-3 ${color.text} hover:opacity-70 transition-opacity`}
        >
          ✕
        </button> */}
      </div>
    </div>,
    document.body
  );
};

export default CustomAlert;
