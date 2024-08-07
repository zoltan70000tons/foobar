import React, { createContext, useContext, useState, useEffect } from 'react';
import axios from 'axios';
import apiRoutes from '@/Helpers/ApiRoutes';
import useAxiosWithToken from '@/Hooks/useAxiosWithToken';

const PermissionsContext = createContext();

export const PermissionsProvider = ({ children, user }) => {
  const [permissions, setPermissions] = useState([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);

  useAxiosWithToken();

  useEffect(() => {
    if (user) {
      axios.get(`${apiRoutes.getPermissions}?id=${user.id}`)
        .then(response => {
          setPermissions(response.data.data.permissions || []);
          console.log(response.data.data.permissions);
          setLoading(false);
        })
        .catch(err => {
          setError(err);
          setLoading(false);
        });
    }
  }, [user]);

  const hasPermission = (permission) => {
    return permissions.includes(permission);
  };

  return (
    <PermissionsContext.Provider value={{ permissions, hasPermission, loading, error }}>
      {children}
    </PermissionsContext.Provider>
  );
};

export const usePermissions = () => useContext(PermissionsContext);
