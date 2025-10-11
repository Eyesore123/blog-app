import { useEffect, useState } from 'react';
import Cookies from 'js-cookie';
import { useTheme } from '../context/ThemeContext';

interface CookieSettings {
  necessary: boolean;
  optional: boolean;
}

export default function CookieConsent() {
  const [visible, setVisible] = useState(false);
  const [settings, setSettings] = useState<CookieSettings>({
    necessary: true,
    optional: false,
  });
  const { theme } = useTheme();

  // Load consent on mount
  useEffect(() => {
    const cookieConsent = Cookies.get('cookie_consent');
    if (!cookieConsent) {
      setVisible(true);
    } else {
      try {
        const parsed = JSON.parse(cookieConsent) as CookieSettings;
        setSettings(parsed);
      } catch {
        setVisible(true);
      }
    }
  }, []);

  const saveConsent = (consent: CookieSettings) => {
    Cookies.set('cookie_consent', JSON.stringify(consent), { expires: 365 });
    setVisible(false);
  };

  return (
    <>
      {/* Floating Cookie Settings Button (always visible) */}
      <button
        onClick={() => setVisible(true)}
        className="fixed !bottom-4 !left-4 !z-40 bg-[#5800FF] text-white 
                   !px-4 !py-2 md:!py-3 rounded-lg shadow-md border-none
                   hover:opacity-80 transition flex items-center gap-2"
      >
        🍪 <span className="hidden sm:inline">Settings</span>
      </button>

      {/* Banner */}
      {visible && (
        <div
          className={`fixed bottom-0 left-0 right-0 !p-6 flex flex-col md:flex-row items-start 
                      md:items-center justify-between !space-y-4 md:!space-y-0 md:!space-x-4 
                      !z-50 shadow-lg border-t border-gray-700
                      bg-[var(--nav-bg)] text-[var(--nav-text)]`}
        >
          <div className="flex-1 xl:!ml-12 2xl:!ml-34">
            <p className="!mb-3">
              I use cookies for essential functionality (login, theme) and optional features.  
              I do <strong>not</strong> use marketing cookies.
            </p>

            <div className="space-y-2">
              <label className="flex items-center !space-x-2">
                <input type="checkbox" name='necessary' checked disabled className="cursor-not-allowed" />
                <span>Necessary cookies (required)</span>
              </label>

              <label className="flex items-center !space-x-2">
                <input
                  type="checkbox"
                  name='optional'
                  checked={settings.optional}
                  onChange={(e) =>
                    setSettings({ ...settings, optional: e.target.checked })
                  }
                />
                <span>Optional cookies</span>
              </label>
            </div>
          </div>

          <div className="flex flex-col md:flex-row !space-y-2 md:!space-y-0 md:!space-x-3">
            <button
              onClick={() => saveConsent(settings)}
              className="bg-[#5800FF] hover:bg-[#E900FF] hover:cursor-pointer text-white !px-4 !py-2 rounded shadow-md transition-colors"
            >
              Accept
            </button>
            <button
              onClick={() => saveConsent({ necessary: true, optional: false })}
              className="bg-[#E900FF] hover:bg-[#5800FF] hover:cursor-pointer text-white !px-4 !py-2 rounded shadow-md transition-colors"
            >
              Decline optional
            </button>
          </div>
        </div>
      )}
    </>
  );
}
