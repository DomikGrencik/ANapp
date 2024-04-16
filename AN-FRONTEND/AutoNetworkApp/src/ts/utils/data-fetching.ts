import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query';

import { YourFormData } from '../types/core-types';
import {
  dataSchemaConnections,
  dataSchemaDevices,
  dataSchemaInterface,
} from '../types/data-types';

import { API_ROUTE_BASE } from './variables';

const queryClient = useQueryClient();

export const { mutateAsync: postNetwork } = useMutation({
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

const deleteDevices = async () => {
  const response = await fetch(`${API_ROUTE_BASE}devices_in_networks/delete`, {
    method: 'DELETE',
  });

  if (!response.ok) {
    throw new Error('Failed to delete Devices');
  }

  return response.json();
};

const deleteInterfaces = async () => {
  const response = await fetch(`${API_ROUTE_BASE}interface_of_devices/delete`, {
    method: 'DELETE',
  });

  if (!response.ok) {
    throw new Error('Failed to delete Devices');
  }

  return response.json();
};

const deleteConnections = async () => {
  const response = await fetch(`${API_ROUTE_BASE}connections/delete`, {
    method: 'DELETE',
  });

  if (!response.ok) {
    throw new Error('Failed to delete Devices');
  }

  return response.json();
};

export const { mutateAsync: deleteDevicesData } = useMutation({
  mutationFn: () => {
    const devices = deleteDevices();
    deleteInterfaces();
    deleteConnections();
    return devices;
  },
  onSuccess: () => {
    console.log('Deleted data');
    //setSuccess(!success);
  },
  onError: (error) => {
    console.error('error:', error.message);
  },
});

/**
 * Fetches devices from the server.
 * @returns {Promise<DataSchemaDevices>} A promise that resolves to the parsed devices data.
 */
const fetchDevices = async () => {
  const response = await fetch(`${API_ROUTE_BASE}devices_in_networks`, {
    method: 'GET',
  });
  const json = await response.json();

  return dataSchemaDevices.parse(json);
};

/**
 * Fetches interfaces from the server.
 * @returns {Promise<DataSchemaInterface>} A promise that resolves to the parsed interfaces data.
 */
const fetchInterfaces = async () => {
  const response = await fetch(`${API_ROUTE_BASE}interface_of_devices`, {
    method: 'GET',
  });
  const json = await response.json();

  return dataSchemaInterface.parse(json);
};

/**
 * Fetches connections from the server.
 * @returns {Promise<DataSchemaConnections>} A promise that resolves to the parsed interfaces data.
 */
const fetchConnections = async () => {
  const response = await fetch(`${API_ROUTE_BASE}connections`, {
    method: 'GET',
  });
  const json = await response.json();

  return dataSchemaConnections.parse(json);
};

export const {
  isLoading: isLoadingDevices,
  error: errorDevices,
  data: dataDevices,
} = useQuery({
  queryKey: ['devices'],
  queryFn: fetchDevices,
});

export const {
  isLoading: isLoadingInterfaces,
  error: errorInterfaces,
  data: dataInterfaces,
} = useQuery({
  queryKey: ['interfaces'],
  queryFn: fetchInterfaces,
});

export const {
  isLoading: isLoadingConnections,
  error: errorConnections,
  data: dataConnections,
} = useQuery({
  queryKey: ['connections'],
  queryFn: fetchConnections,
});
