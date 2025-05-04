import '../css/app.css';

import { createRoot } from 'react-dom/client';
import { createInertiaApp } from '@inertiajs/react';

createInertiaApp({
  resolve: (name) => import(`./Pages/${name}`).then((module) => module.default), // Use dynamic import
  setup({ el, App, props }) {
    createRoot(el).render(<App {...props} />);
  },
});
