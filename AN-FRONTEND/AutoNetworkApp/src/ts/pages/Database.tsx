import { FC, useState } from 'react';
import { useMutation, useQuery } from '@tanstack/react-query';
import { FormikHelpers } from 'formik';
import { z } from 'zod';

import MyForm from '../components/form/MyForm';
import MyButton from '../components/MyButton';
import MyModal from '../components/MyModal';
import MyTable from '../components/MyTable';
import MyTopology from '../components/topology/MyTopology';
import { YourFormData } from '../types/core-types';
import useFetchDevices from '../utils/hooks/useFetchDevices';
import usePostNetwork from '../utils/hooks/usePostNetwork';
import { API_ROUTE_BASE } from '../utils/variables';

import 'reactflow/dist/style.css';

export const dataSchemaDevices = z.array(
  z.object({
    id: z.number().int(),
    name: z.string(),
    type: z.string(),
    device_id: z.number().int(),
  })
);

export const dataSchemaInterface = z.array(
  z.object({
    interface_id: z.number().int(),
    name: z.string(),
    IP_address: z.string().nullable(),
    connector: z.string(),
    AN: z.string().nullable(),
    speed: z.string(),
    interface_id2: z.number().int().nullable(),
    id: z.number().int(),
    type: z.string(),
  })
);

export const dataSchemaConnections = z.array(
  z.object({
    connection_id: z.number().int(),
    interface_id1: z.number().int(),
    interface_id2: z.number().int(),
    device_id1: z.number().int(),
    device_id2: z.number().int(),
    name1: z.string(),
    name2: z.string(),
  })
);

const Database: FC = () => {
  const [success, setSuccess] = useState(false);
  const [open, setOpen] = useState(false);

  const postNetworkData = usePostNetwork();

  const {
    data: dataDevices,
    isLoading: isLoadingDevices,
    error: errorDevices,
  } = useFetchDevices();

  // This function handles the submission of the form data to the server
  const { mutateAsync: postNetwork } = useMutation({
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
      setSuccess(!success);
    },
    onError: (error) => {
      console.error('Form submission error:', error.message);
    },
  });

  const deleteDevices = async () => {
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
  };

  const deleteInterfaces = async () => {
    const response = await fetch(
      `${API_ROUTE_BASE}interface_of_devices/delete`,
      {
        method: 'DELETE',
      }
    );

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

  const { mutateAsync: deleteDevicesData } = useMutation({
    mutationFn: () => {
      const devices = deleteDevices();
      deleteInterfaces();
      deleteConnections();
      return devices;
    },
    onSuccess: () => {
      console.log('Deleted data');
      setSuccess(!success);
    },
    onError: (error) => {
      console.error('error:', error.message);
    },
  });

  const handleDelete = () => {
    console.log('delete');
    deleteDevicesData();
  };

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
  /* const fetchInterfaces = async () => {
    const response = await fetch(`${API_ROUTE_BASE}interface_of_devices`, {
      method: 'GET',
    });
    const json = await response.json();

    return dataSchemaInterface.parse(json);
  }; */

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

  /* const {
    isLoading: isLoadingDevices,
    error: errorDevices,
    data: dataDevices,
  } = useQuery({
    queryKey: ['devices', success],
    queryFn: fetchDevices,
  }); */

  /* const {
    isLoading: isLoadingInterfaces,
    error: errorInterfaces,
    data: dataInterfaces,
  } = useQuery({
    queryKey: ['interfaces', success],
    queryFn: fetchInterfaces,
  }); */

  const {
    isLoading: isLoadingConnections,
    error: errorConnections,
    data: dataConnections,
  } = useQuery({
    queryKey: ['connections', success],
    queryFn: fetchConnections,
  });

  if (errorDevices) {
    console.error(errorDevices.message);
    return null;
  }

  /* if (errorInterfaces) {
    console.error(errorInterfaces.message);
    return null;
  } */

  if (errorConnections) {
    console.error(errorConnections.message);
    return null;
  }

  const handleSubmit = async (
    values: YourFormData,
    formikHelpers: FormikHelpers<YourFormData>
  ) => {
    try {
      await postNetworkData(values);
      formikHelpers.resetForm();
    } catch (error) {
      console.error('Error submitting form:', error);
    }
  };

  return (
    <main className="page flex--justify-space-between container--wide flex">
      <div className="flex--column flex">
        <MyForm onSubmit={handleSubmit} />
        <MyButton onClick={handleDelete}>Delete</MyButton>
        <MyButton
          onClick={() => {
            setOpen(true);
          }}
        >
          Modal
        </MyButton>
      </div>

      {open ? (
        <div>
          <MyModal isOpen={open} onClose={() => setOpen(false)}>
            Ja som modal
          </MyModal>
        </div>
      ) : null}

      <div
        style={{
          width: '100%',
          minWidth: '300px',
          border: '1px solid #e5e5e5',
          borderRadius: '5px',
          marginLeft: '20px',
          marginRight: '20px',
        }}
      >
        <MyTopology
          dataDevices={dataDevices ?? []}
          dataConnections={dataConnections ?? []}
        />
      </div>

      <div>
        {isLoadingDevices || isLoadingConnections ? (
          <div>loading</div>
        ) : (
          <MyTable data={dataDevices ?? []} />
        )}
      </div>
    </main>
  );
};

export default Database;
