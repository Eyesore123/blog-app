// app.tsx (entry point)

import '../css/app.css';
import { createRoot } from 'react-dom/client';
import { createInertiaApp } from '@inertiajs/react';
import { Inertia } from '@inertiajs/inertia';
import { ThemeProvider } from './context/ThemeContext';
import { AlertProvider } from './context/AlertContext';
import { ConfirmProvider } from './context/ConfirmationContext';
import { HelmetProvider } from 'react-helmet-async';
import { ErrorBoundary } from './pages/errors/ErrorBoundary';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import CookieConsent from './components/CookieConsent';
import BackToTopButton from './components/BackToTopButton';
import { FooterComponent } from './components/FooterComponent';
import Spinner from './components/Spinner';
import React, { useState, useEffect } from 'react';

// Catch client-side (network) errors
import { router } from '@inertiajs/react';

router.on('error', (error: any) => {
  console.error('Inertia client error caught:', error);

  if (!error?.response) {
    router.visit('/error/503', { replace: true });
    return;
  }

  const status = error.response.status;

  if ([403, 404, 500, 503].includes(status)) {
    router.visit(`/error/${status}`, { replace: true });
  } else {
    router.visit('/error/500', { replace: true });
  }
});

// Wrapper component to handle loading spinner
function AppWrapper({ App, props }: { App: any; props: any }) {
  const [loading, setLoading] = useState(false);

  useEffect(() => {
  const start = () => setLoading(true);
  const finish = () => setLoading(false);

  (Inertia as any).on('start', start);
  (Inertia as any).on('finish', finish);

  return () => {
    (Inertia as any).off('start', start);
    (Inertia as any).off('finish', finish);
  };
}, []);


  return (
    <>
      {loading && (
        <div className="fixed !top-4.5 !left-4 z-50">
          <Spinner size={36} />
        </div>
      )}
      <App {...props} />
    </>
  );
}

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
                <AppWrapper App={App} props={props} />
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
