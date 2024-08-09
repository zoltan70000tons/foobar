import { useEffect } from 'react';
import axios from 'axios';

const useAxiosWithToken = () => {
  useEffect(() => {
    // const fetchToken = async () => {
    //   try {
    //     // Verifica si el token ya está en el almacenamiento local
    //     let token = localStorage.getItem('token');
        
    //     // Si no hay token, solicita uno nuevo
    //     if (!token) {
    //       const response = await axios.post('/api/auth/token'); // Cambia esta URL a la de tu API para obtener el token
    //       token = response.data.token;
    //       localStorage.setItem('token', token);
    //     }

    //     // Configura el token en los encabezados de Axios
    //     axios.defaults.headers.common['Authorization'] = `Bearer ${token}`;
    //   } catch (error) {
    //     console.error('Error fetching or setting token:', error);
    //   }
    // };

    // fetchToken();

    // return () => {
    //   // Elimina el token de los encabezados de Axios cuando el componente se desmonte
    //   delete axios.defaults.headers.common['Authorization'];
    // };
  }, []);
};

export default useAxiosWithToken;
