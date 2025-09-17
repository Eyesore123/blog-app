
// Note: ErrorBoundary currently only catches client-side Inertia navigation errors
// or network issues. Full-page loads (like typing a URL directly) are handled by
// server-side Inertia responses in App\Exceptions\Handler.



import '../css/app.css';
import { createRoot } from 'react-dom/client';
import { createInertiaApp } from '@inertiajs/react';
import { ThemeProvider } from './context/ThemeContext';
import { AlertProvider } from './context/AlertContext';
import { ConfirmProvider } from './context/ConfirmationContext';
import { HelmetProvider } from 'react-helmet-async';
import { ErrorBoundary } from './pages/errors/ErrorBoundary';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import  { router } from '@inertiajs/react';
import CookieConsent from './components/CookieConsent';
import BackToTopButton from './components/BackToTopButton';
import { FooterComponent } from './components/FooterComponent';

// router.on('error', (error) => {
//   console.error('Inertia Error:', error);
// });

// Catch client-side (network) errors
router.on('error', (error: any) => {
  console.error('Inertia client error caught:', error);

  if (!error?.response) {
    // Network/server completely unreachable
    router.visit('/error/503', { replace: true });
    return;
  }

  const status = error.response.status;

  // Redirect to proper error page for known HTTP errors
  if ([403, 404, 500, 503].includes(status)) {
    router.visit(`/error/${status}`, { replace: true });
  } else {
    router.visit('/error/500', { replace: true });
  }
});


createInertiaApp({
  title: (title) => `${title} Joni's Blog`,
  resolve: (name) =>
    resolvePageComponent(`./pages/${name}.tsx`, import.meta.glob('./pages/**/*.tsx')),

  setup({ el, App, props }) {
    const root = createRoot(el);
    root.render(
      <HelmetProvider>
      <ThemeProvider>
        <AlertProvider>
          <ConfirmProvider>
        <ErrorBoundary>
          <CookieConsent />
          <BackToTopButton />
            <App {...props} />
            <FooterComponent />
        </ErrorBoundary>
        </ConfirmProvider>
        </AlertProvider>
      </ThemeProvider>
      </HelmetProvider>
    );
  },

  progress: {
    color: '#5800FF',
  },
});
