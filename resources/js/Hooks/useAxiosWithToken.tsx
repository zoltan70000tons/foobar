import { useEffect } from 'react';
import axios from 'axios';

const useAxiosWithToken = () => {
  useEffect(() => {
    const token = localStorage.getItem('token');
    if (token) {
      axios.defaults.headers.common['Authorization'] = `Bearer ${token}`;
    }
    
    return () => {
      delete axios.defaults.headers.common['Authorization'];
    };
  }, []);
};

export default useAxiosWithToken;