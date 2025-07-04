import { useState } from 'react';
import { usePage, Link } from '@inertiajs/react';
import SignOutButton from '@/components/SignOutButton';
import { useTheme } from '../context/ThemeContext';

interface PagePropsWithAuth {
  auth: {
    user: {
      is_admin: number | boolean;
      name: string;
      is_anonymous?: boolean;
      profile_photo_path: string | null;
    } | null;
  };
}

export function Navbar() {
  const props = usePage().props;
  const { theme, toggleTheme } = useTheme();
  const [isMenuOpen, setIsMenuOpen] = useState(false);

  const hasAuthProp = (props: any): props is PagePropsWithAuth => {
    return 'auth' in props;
  };

  const renderLeftLinks = () => {
    if (hasAuthProp(props)) {
      const user = props.auth?.user;
      const isAdmin = user ? Boolean(user.is_admin) : false;

      const profileUrl = user?.profile_photo_path
        ? `/uploads/${user.profile_photo_path}?w=40&h=40`
        : user?.is_anonymous
        ? '/anon-icon.svg'
        : '/default-user-icon.svg';

const isDefaultIcon = profileUrl === '/default-user-icon.svg';

    return (
    <div className="flex flex-col md:flex-row items-center gap-4 md:!gap-6 2xl:!pl-40 custompadding">
      {user && (
        <div className="flex items-center !gap-2">
          <span className="text-[#FFC600] font-semibold min-w-[150px]">
            Welcome, {user.name}!
          </span>
          <img
            src={profileUrl}
            alt="Profile"
            className={`w-10 h-10 !ml-4 !mr-10 rounded-full object-cover ${
              isDefaultIcon && theme === 'dark' ? 'filter invert' : ''
            }`}
          />
        </div>
      )}

          <Link href="/privacy-policy" className="hover:text-purple-400 min-w-[100px]">
            Privacy Policy
          </Link>
          <Link href="/" className="hover:text-purple-400 min-w-[100px]">
            Main Page
          </Link>

          {isAdmin && (
            <Link href="/admin" className="hover:text-purple-400 min-w-[100px]">
              Admin Dashboard
            </Link>
          )}
        </div>
      );
    }

    return null;
  };

  const renderRightLinks = () => {
    if (hasAuthProp(props)) {
      const user = props.auth?.user;

      return (
        <div className="flex flex-col md:flex-row items-center !gap-4 md:!gap-6">
          <button
            onClick={toggleTheme}
            className="!p-2 !mt-2 sm:!mt-0 hover:text-[#FFC600] transition-colors scale-125"
          >
            {theme === 'dark' ? '🌞' : '🌙'}
          </button>

          {user ? (
            <>
              {!user.is_anonymous ? (
                <Link
                  href="/account"
                  className="!px-4 !py-2 rounded bg-[#5800FF] text-white hover:bg-[#E900FF] transition-colors"
                >
                  My Account
                </Link>
              ) : (
                <span className="text-gray-500">Anonymous User</span>
              )}
              <SignOutButton />
            </>
          ) : (
            <Link
              href={route('login')}
              className="!px-4 !py-2 rounded bg-[#5800FF] text-white hover:bg-[#E900FF] transition-colors !mb-4 md:!mb-0"
            >
              Sign In
            </Link>
          )}
        </div>
      );
    }

    return (
      <div className="flex flex-col md:flex-row items-center gap-4 md:gap-6">
        <button
          onClick={toggleTheme}
          className="!p-2 hover:text-[#FFC600] transition-colors"
        >
          {theme === 'dark' ? '🌞' : '🌙'}
        </button>
        <Link
          href={route('login')}
          className="!px-4 !py-2 rounded bg-[#5800FF] text-white hover:bg-[#E900FF] transition-colors !mb-4 md:!mb-0"
        >
          Sign In
        </Link>
      </div>
    );
  };

  return (
    <nav className="sticky top-0 z-10 bg-[var(--nav-bg)] text-[var(--nav-text)] !p-4 flex items-center">
      {/* Left Links */}
      <div className="hidden md:flex flex-1 justify-start items-center">
        {renderLeftLinks()}
      </div>

      {/* Center Spacer */}
      <div className="flex-1"></div>

      {/* Menu Button for Mobile */}
      <div className="flex items-center">
        <button
          className="md:hidden !p-2 text-[#FFC600] hover:text-[#E900FF] transition-colors scale-160"
          onClick={() => setIsMenuOpen(!isMenuOpen)}
        >
          {isMenuOpen ? '✖' : '☰'}
        </button>
      </div>

      {/* Links Container */}
      <div
        className={`${
          isMenuOpen ? 'block' : 'hidden'
        } md:flex flex-col md:flex-row items-center gap-4 md:gap-6 absolute md:static top-16 left-0 w-full md:w-auto bg-[var(--nav-bg)] md:bg-transparent !p-4 md:!p-0 shadow-sm shadow-black md:shadow-none`}
      >
        <div className="md:hidden">{renderLeftLinks()}</div>
        {renderRightLinks()}
      </div>
    </nav>
  );
}
