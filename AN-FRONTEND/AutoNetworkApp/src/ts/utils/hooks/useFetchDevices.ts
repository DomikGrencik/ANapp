import { useQuery } from '@tanstack/react-query';

import { dataSchemaDevices } from '../../types/data-types';
import { API_ROUTE_BASE } from '../variables';

/**
 * Fetches devices from the server.
 */
const fetchDevices = async () => {
  const response = await fetch(`${API_ROUTE_BASE}devices_in_networks`, {
    method: 'GET',
  });
  const json = await response.json();

  return dataSchemaDevices.parse(json);
};

const useFetchDevices = () => {
  const { data, isLoading, error } = useQuery({
    queryKey: ['devices'],
    queryFn: fetchDevices,
  });

  return { data, isLoading, error };
};

export default useFetchDevices;
