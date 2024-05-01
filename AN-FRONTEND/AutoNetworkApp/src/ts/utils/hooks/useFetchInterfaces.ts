import { useQuery } from '@tanstack/react-query';

import { dataSchemaInterface } from '../../types/data-types';
import { API_ROUTE_BASE } from '../variables';

/**
 * Fetches interfaces from the server.
 */
const fetchInterfaces = async () => {
  const response = await fetch(`${API_ROUTE_BASE}interface_of_devices`, {
    method: 'GET',
  });
  const json = await response.json();

  return dataSchemaInterface.parse(json);
};

const useFetchInterfaces = () => {
  const { data, isLoading, error } = useQuery({
    queryKey: ['interface'],
    queryFn: fetchInterfaces,
  });

  return { data, isLoading, error };
};

export default useFetchInterfaces;
