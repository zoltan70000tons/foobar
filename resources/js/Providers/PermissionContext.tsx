import React, { createContext, useContext } from 'react';

const PermissionsContext = createContext();

export const PermissionsProvider = ({ children, auth }) => {
  const { permissions, roles } = auth || { permissions: [], roles: [] };

  const hasPermission = (permission) => {
    return permissions.includes(permission);
  };

  const hasRole = (role) => {
    return roles.includes(role);
  };

  return (
    <PermissionsContext.Provider value={{ permissions, roles, hasPermission, hasRole }}>
      {children}
    </PermissionsContext.Provider>
  );
};

export const usePermissions = () => useContext(PermissionsContext);
