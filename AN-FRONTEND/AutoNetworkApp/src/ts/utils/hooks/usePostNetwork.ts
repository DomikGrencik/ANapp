import { useMutation, useQueryClient } from '@tanstack/react-query';

import { YourFormData } from '../../types/core-types';
import { API_ROUTE_BASE } from '../variables';

const usePostNetwork = () => {
  const queryClient = useQueryClient();

  useMutation({
    mutationFn: (values: YourFormData) => {
      return fetch(`${API_ROUTE_BASE}devices_in_networks`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
        },
        body: JSON.stringify(values),
      });
    },
    onSuccess: () => {
      console.log('Form submitted successfully!');
      queryClient.invalidateQueries({ queryKey: ['devices'] });
      queryClient.invalidateQueries({ queryKey: ['interfaces'] });
      queryClient.invalidateQueries({ queryKey: ['connections'] });
    },
    onError: (error) => {
      console.error('Form submission error:', error.message);
    },
  });
};

export default usePostNetwork;
