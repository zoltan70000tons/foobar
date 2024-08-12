import './bootstrap';
import '../css/app.scss';

import { createRoot } from 'react-dom/client';
import { createInertiaApp } from '@inertiajs/react';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import '@fontsource/roboto'; 

import { PermissionsProvider } from '../js/Providers/PermissionContext'
const appName = import.meta.env.VITE_APP_NAME || 'Laravel';

createInertiaApp({
  title: (title) => `${title} - ${appName}`,
  resolve: (name) => resolvePageComponent(`./Pages/${name}.tsx`, import.meta.glob('./Pages/**/*.tsx')),
  setup({ el, App, props }) {
    const root = createRoot(el);
    const { auth } = props.initialPage.props;
    root.render(
      <PermissionsProvider auth={auth}>
        <App {...props} />
      </PermissionsProvider>
    );
  },
  progress: {
    color: '#4B5563',
  },
});
