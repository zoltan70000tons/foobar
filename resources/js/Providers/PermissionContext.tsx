import React, { createContext, useContext, ReactNode } from 'react';

// Define the types for permissions and roles
type Permission = string; 
type Role = string; 

// Define the type for the auth object
interface Auth {
  permissions: Permission[];
  roles: Role[];
}

// Define the context value type
interface PermissionsContextType {
  permissions: Permission[];
  roles: Role[];
  hasPermission: (permission: Permission) => boolean;
  hasRole: (role: Role) => boolean;
}

// Initialize the context with a default value of undefined
const PermissionsContext = createContext<PermissionsContextType | undefined>(undefined);

// Define props for the provider
interface PermissionsProviderProps {
  children: ReactNode;
  auth?: Auth; // Optional auth prop
}

export const PermissionsProvider: React.FC<PermissionsProviderProps> = ({ children, auth }) => {
  const { permissions, roles } = auth || { permissions: [], roles: [] };

  const hasPermission = (permission: Permission): boolean => {
    return permissions.includes(permission);
  };

  const hasRole = (role: Role): boolean => {
    return roles.includes(role);
  };

  return (
    <PermissionsContext.Provider value={{ permissions, roles, hasPermission, hasRole }}>
      {children}
    </PermissionsContext.Provider>
  );
};

// Custom hook to use the permissions context
export const usePermissions = (): PermissionsContextType => {
  const context = useContext(PermissionsContext);
  if (!context) {
    throw new Error('usePermissions must be used within a PermissionsProvider');
  }
  return context;
};
