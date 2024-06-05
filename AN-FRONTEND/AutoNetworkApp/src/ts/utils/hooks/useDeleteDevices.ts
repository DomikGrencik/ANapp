import { useMutation, useQueryClient } from '@tanstack/react-query';

import { API_ROUTE_BASE } from '../variables';

const useDeleteDevices = () => {
  const queryClient = useQueryClient();

  const { mutateAsync: deleteDevices, isPending } = useMutation({
    mutationFn: async () => {
      const response = await fetch(
        `${API_ROUTE_BASE}devices_in_networks/delete`,
        {
          method: 'DELETE',
        }
      );
      if (!response.ok) {
        throw new Error('Failed to delete Devices');
      }
      return response.json();
    },
    onSuccess: () => {
      console.log('Deleted data devices!');
      queryClient.invalidateQueries({ queryKey: ['devices'] });
      /* queryClient.invalidateQueries({ queryKey: ['interfaces'] });
      queryClient.invalidateQueries({ queryKey: ['connections'] }); */
    },
    onError: (error) => {
      console.error('error:', error.message);
    },
  });

  return {deleteDevices, isPending};
};

export default useDeleteDevices;
