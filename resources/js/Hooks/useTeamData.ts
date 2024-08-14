import { useState, useEffect } from 'react';
import axios from 'axios';
import apiRoutes from '@/Helpers/ApiRoutes';

interface User {
  id: number;
  name: string;
  email: string;
  status: string;
  roles: string[];
}

export const useTeamData = () => {
  const [rows, setRows] = useState<User[]>([]);
  const [loading, setLoading] = useState(false);

  const fetchData = async () => {
    setLoading(true);
    try {
      const response = await axios.get<User[]>(apiRoutes.getTeamUrl, { params: { org_id: 1 } });
      setRows(response.data);
    } catch (error) {
      console.error('Error fetching data:', error);
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    fetchData();
  }, []);

  return { rows, fetchData, loading };
};