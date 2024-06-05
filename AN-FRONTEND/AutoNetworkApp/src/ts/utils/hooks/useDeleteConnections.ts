import { useMutation, useQueryClient } from '@tanstack/react-query';

import { API_ROUTE_BASE } from '../variables';

const useDeleteConnections = () => {
  const queryClient = useQueryClient();

  const { mutateAsync: deleteConnections, isPending } = useMutation({
    mutationFn: async () => {
      const response = await fetch(`${API_ROUTE_BASE}connections/delete`, {
        method: 'DELETE',
      });
      if (!response.ok) {
        throw new Error('Failed to delete Connections');
      }
      return response.json();
    },
    onSuccess: () => {
      console.log('Deleted data connections!');
      queryClient.invalidateQueries({ queryKey: ['connections'] });
      /* queryClient.invalidateQueries({ queryKey: ['devices'] });
      queryClient.invalidateQueries({ queryKey: ['interfaces'] }); */
    },
    onError: (error) => {
      console.error('error:', error.message);
    },
  });

  return {deleteConnections, isPending};
};

export default useDeleteConnections;
