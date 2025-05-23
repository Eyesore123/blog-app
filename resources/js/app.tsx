
// Note: Error boundary is currently not actively used in the error handling flow,
// but is kept for potential future implementation of client-side error catching. Error handling is done with routes using Inertia.


import '../css/app.css';
import { createRoot } from 'react-dom/client';
import { createInertiaApp } from '@inertiajs/react';
import { ThemeProvider } from './context/ThemeContext';
import { AlertProvider } from './context/AlertContext';
import { ConfirmProvider } from './context/ConfirmationContext';
import { HelmetProvider } from 'react-helmet-async';
// import { ErrorBoundary } from './pages/errors/ErrorBoundary';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import  { router } from '@inertiajs/react';

router.on('error', (error) => {
  console.error('Inertia Error:', error);
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
        {/* <ErrorBoundary> */}
          <App {...props} />
        {/* </ErrorBoundary> */}
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
