import { useQuery } from '@tanstack/react-query';

import { dataSchemaConnections } from '../../types/data-types';
import { API_ROUTE_BASE } from '../variables';

/**
 * Fetches devices from the server.
 */
const fetchConnections = async () => {
  const response = await fetch(`${API_ROUTE_BASE}connections`, {
    method: 'GET',
  });
  const json = await response.json();

  return dataSchemaConnections.parse(json);
};

const useFetchConnections = () => {
  const { data, isLoading, error } = useQuery({
    queryKey: ['connections'],
    queryFn: fetchConnections,
  });

  return { data, isLoading, error };
};

export default useFetchConnections;
