import { usePage, Link } from '@inertiajs/react';
import SignOutButton from '@/components/SignOutButton';
import { useTheme } from '../context/ThemeContext';

interface PagePropsWithAuth {
  auth: {
    user: {
      is_admin: number | boolean; // Accept either number or boolean
      name: string;
    } | null;
  };
}

export function Navbar() {
  const props = usePage().props;
  const { theme, toggleTheme } = useTheme();

  const hasAuthProp = (props: any): props is PagePropsWithAuth => {
    return 'auth' in props;
  };

  const renderLinks = () => {
    if (hasAuthProp(props)) {
      const user = props.auth?.user;
      const isAdmin = user ? Boolean(user.is_admin) : false;
      
      return (
        <>
          <div className="flex items-center gap-4">
            {user && (
              <span className="text-[#FFC600] font-semibold">
                Welcome, {user.name}!
              </span>
            )}

            {isAdmin && (
              <>
                <Link href="/admin" className="hover:text-purple-400">
                  Admin Dashboard
                </Link>
                <Link href="/" className="hover:text-purple-400">
                  Main Page
                </Link>
              </>
            )}
          </div>
          <div className="flex items-center md:gap-14">
            <button
              onClick={toggleTheme}
              className="p-2 hover:text-[#FFC600] transition-colors"
            >
              {theme === 'dark' ? '🌞' : '🌙'}
            </button>
            {user ? (
              <SignOutButton />
            ) : (
              <Link
                href={route('login')}
                className="!px-4 !py-2 rounded bg-[#5800FF] text-white hover:bg-[#E900FF] transition-colors"
              >
                Sign In
              </Link>
            )}
          </div>
        </>
      );
    }
    // fallback (no auth prop at all — rare)
    return (
      <>
        <div className="flex items-center gap-4">
          <Link href="/" className="hover:text-purple-400">
            Home
          </Link>
        </div>
        <div className="flex items-center gap-14">
          <button
            onClick={toggleTheme}
            className="p-2 hover:text-[#FFC600] transition-colors"
          >
            {theme === 'dark' ? '🌞' : '🌙'}
          </button>
          <Link
            href={route('login')}
            className="!px-4 !py-2 rounded bg-[#5800FF] text-white hover:bg-[#E900FF] transition-colors"
          >
            Sign In
          </Link>
        </div>
      </>
    );
  };

  return (
    <nav className="sticky top-0 z-10 bg-[var(--nav-bg)] text-[var(--nav-text)] !p-4 flex justify-between items-center">
      {renderLinks()}
    </nav>
  );
}
