import { useQuery } from '@tanstack/react-query';

import { dataSchemaDeviceDatabase } from '../../types/data-types';
import { API_ROUTE_BASE } from '../variables';

/**
 * Fetches devices from the server.
 */
const fetchDeviceDatabase = async () => {
  const response = await fetch(`${API_ROUTE_BASE}devices`, {
    method: 'GET',
  });
  const json = await response.json();

  return dataSchemaDeviceDatabase.parse(json);
};

const useFetchDeviceDatabase = () => {
  const { data, isLoading, error } = useQuery({
    queryKey: ['deviceDatabase'],
    queryFn: fetchDeviceDatabase,
  });

  return { data, isLoading, error };
};

export default useFetchDeviceDatabase;
