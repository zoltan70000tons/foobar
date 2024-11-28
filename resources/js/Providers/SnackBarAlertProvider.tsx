import React, { createContext, useContext, useState, useCallback } from "react";
import SnackbarAlert from "@/Components/SnackbarAlert";

interface SnackbarContextProps {
  showSnackbar: (message: string, severity?: "success" | "error" | "warning" | "info") => void;
}

const SnackbarContext = createContext<SnackbarContextProps | undefined>(undefined);

export const SnackbarProvider: React.FC<{ children: React.ReactNode }> = ({ children }) => {
  const [snackbar, setSnackbar] = useState({
    open: false,
    message: "",
    severity: "success",
  });

  const showSnackbar = useCallback((message: string, severity: "success" | "error" | "warning" | "info" = "success") => {
    setSnackbar({ open: true, message, severity });
  }, []);

  const closeSnackbar = useCallback(() => {
    setSnackbar((prev) => ({ ...prev, open: false }));
  }, []);

  return (
    <SnackbarContext.Provider value={{ showSnackbar }}>
      {children}
      <SnackbarAlert
        message={snackbar.message}
        severity={snackbar.severity as "success" | "error" | "warning" | "info"}
        open={snackbar.open}
        onClose={closeSnackbar}
        vertical="top"
        horizontal="center"
      />
    </SnackbarContext.Provider>
  );
};

export const useSnackbar = () => {
  const context = useContext(SnackbarContext);
  if (!context) {
    throw new Error("useSnackbar must be used within a SnackbarProvider");
  }
  return context;
};
