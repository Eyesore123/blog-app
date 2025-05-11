
// Note: Error boundary is currently not actively used in the error handling flow,
// but is kept for potential future implementation of client-side error catching. Error handling is done with routes using Inertia.


import '../css/app.css';
import { createRoot } from 'react-dom/client';
import { createInertiaApp } from '@inertiajs/react';
import { ThemeProvider } from './context/ThemeContext';
import { AlertProvider } from './context/AlertContext';
import { ConfirmProvider } from './context/ConfirmationContext';
// import { ErrorBoundary } from './pages/errors/ErrorBoundary';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import  { router } from '@inertiajs/react';

router.on('error', (error) => {
  console.error('Inertia Error:', error);
});

createInertiaApp({
  title: (title) => `${title} - My App`,
  resolve: (name) =>
    resolvePageComponent(`./pages/${name}.tsx`, import.meta.glob('./pages/**/*.tsx')),

  setup({ el, App, props }) {
    const root = createRoot(el);
    root.render(
      <ThemeProvider>
        <AlertProvider>
          <ConfirmProvider>
        {/* <ErrorBoundary> */}
          <App {...props} />
        {/* </ErrorBoundary> */}
        </ConfirmProvider>
        </AlertProvider>
      </ThemeProvider>
    );
  },

  progress: {
    color: '#5800FF',
  },
});
