import React from 'react';
import { createPortal } from 'react-dom';

interface ConfirmDialogProps {
  isOpen: boolean;
  title: string;
  message: string;
  confirmText?: string;
  cancelText?: string;
  onConfirm: () => void;
  onCancel: () => void;
  type?: 'danger' | 'warning' | 'info';
}

const ConfirmDialog: React.FC<ConfirmDialogProps> = ({
  isOpen,
  title,
  message,
  confirmText = 'Confirm',
  cancelText = 'Cancel',
  onConfirm,
  onCancel,
  type = 'danger'
}) => {
  if (!isOpen) return null;

  // Color schemes based on your site's palette
  const colors = {
    danger: {
      button: 'bg-red-600 hover:bg-red-700',
      icon: '⚠️'
    },
    warning: {
      button: 'bg-[#E900FF] hover:bg-[#E900FF]/80',
      icon: '⚠️'
    },
    info: {
      button: 'bg-[#5800FF] hover:bg-[#5800FF]/80',
      icon: 'ℹ️'
    }
  };

  const colorScheme = colors[type];

  return createPortal(
    <div className="fixed inset-0 z-50 flex items-center justify-center bg-black/50 animate-fade-in">
      <div className="bg-[var(--bg-primary)] rounded-lg shadow-xl max-w-md w-full mx-4 overflow-hidden">
        <div className="!p-5 border-b border-[#5800FF]/20">
          <div className="flex items-center">
            <span className="text-2xl !mr-3">{colorScheme.icon}</span>
            <h3 className="text-lg font-medium text-[var(--text-primary)]">{title}</h3>
          </div>
        </div>
        
        <div className="!p-5">
          <p className="text-[var(--text-primary)] opacity-80">{message}</p>
        </div>
        
        <div className="!p-4 bg-[#5800FF]/5 flex justify-end !space-x-3">
          <button
            onClick={onCancel}
            className="!px-4 !py-2 border border-[#5800FF]/20 rounded text-[var(--text-primary)] hover:bg-[#5800FF]/10 transition-colors"
          >
            {cancelText}
          </button>
          <button
            onClick={onConfirm}
            className={`!px-4 !py-2 rounded text-white ${colorScheme.button} transition-colors`}
          >
            {confirmText}
          </button>
        </div>
      </div>
    </div>,
    document.body
  );
};

export default ConfirmDialog;
