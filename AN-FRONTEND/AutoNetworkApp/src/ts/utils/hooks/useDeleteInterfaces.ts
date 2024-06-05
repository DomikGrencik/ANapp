import { useMutation, useQueryClient } from '@tanstack/react-query';

import { API_ROUTE_BASE } from '../variables';

const useDeleteInterfaces = () => {
  const queryClient = useQueryClient();

  const { mutateAsync: deleteInterfaces, isPending } = useMutation({
    mutationFn: async () => {
      const response = await fetch(
        `${API_ROUTE_BASE}interface_of_devices/delete`,
        {
          method: 'DELETE',
        }
      );
      if (!response.ok) {
        throw new Error('Failed to delete Interfaces');
      }
      return response.json();
    },
    onSuccess: () => {
      console.log('Deleted data interfaces!');
      queryClient.invalidateQueries({ queryKey: ['interfaces'] });
      /* queryClient.invalidateQueries({ queryKey: ['devices'] });
      queryClient.invalidateQueries({ queryKey: ['connections'] }); */
    },
    onError: (error) => {
      console.error('error:', error.message);
    },
  });

  return {deleteInterfaces, isPending};
};

export default useDeleteInterfaces;
