import React from "react";
import { router } from '@inertiajs/react';
import { useTheme } from '../context/ThemeContext';

export default function TriviaComponent() {
  const { theme } = useTheme();

  const goToTrivia = () => {
    router.visit('/trivia', {
      preserveScroll: false, // disable automatic scroll restoration
      onSuccess: () => {
        // scroll after the page renders
        window.scrollTo({ top: 0 });
      },
    });
  };

  return (
    <div className={`rounded-lg !mt-4 !pb-4 text-left relative overflow-visible ${theme}`}>
      <h3 className='font-semibold !mb-2'>Trivia</h3>
      <div className="text-[14px] !mb-6 !mt-6 opacity-80">🎲 Trivia Corner</div>

      <button
        onClick={goToTrivia}
        className="text-[#E900FF] hover:text-[#5800FF] font-semibold text-sm md:text-base"
      >
       → Check out my trivia!
      </button>
    </div>
  );
}
