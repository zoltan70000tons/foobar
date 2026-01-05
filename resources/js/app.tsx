import "./bootstrap";
import "../css/app.scss";

import { createRoot } from "react-dom/client";
import { createInertiaApp } from "@inertiajs/react";
import { resolvePageComponent } from "laravel-vite-plugin/inertia-helpers";
import "@fontsource/roboto";

import { Provider as ReduxProvider } from "react-redux";
import { store } from "@/store";

import { PermissionsProvider } from "../js/Providers/PermissionContext";
import { SnackbarProvider } from "./Providers/SnackBarAlertProvider";
import EnvironmentBar from "./Components/EnvironmentBar";
const appName = import.meta.env.VITE_APP_NAME || "Laravel";
const appEnv = import.meta.env.VITE_APP_ENV || "prod";

createInertiaApp({
  title: (title) => `${title} - ${appName}`,
  resolve: (name) => resolvePageComponent(`./Pages/${name}.tsx`, import.meta.glob("./Pages/**/*.tsx")),
  setup({ el, App, props }) {
    const root = createRoot(el);
    const auth: any = props.initialPage.props.auth;

    root.render(
      <ReduxProvider store={store}>
        <SnackbarProvider>
          {/* <EnvironmentBar environment={appEnv} /> */}
          <PermissionsProvider auth={auth}>
            <App {...props} />
          </PermissionsProvider>
        </SnackbarProvider>
      </ReduxProvider>,
    );
  },
  progress: {
    color: "#ff0090",
    showSpinner: false,
  },
});
