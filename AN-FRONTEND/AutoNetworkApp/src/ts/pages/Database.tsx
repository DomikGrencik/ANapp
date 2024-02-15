import { FC, useState } from 'react';
import { useMutation, useQuery } from '@tanstack/react-query';
import { z } from 'zod';

import MyForm, { YourFormData } from '../components/form/MyForm';
import MyButton from '../components/MyButton';
import MyModal from '../components/MyModal';
import MyTable from '../components/MyTable';
import { API_ROUTE_BASE } from '../utils/variables';

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

const Database: FC = () => {
  const [success, setSuccess] = useState(false);
  const [open, setOpen] = useState(false);

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

    console.log(response);
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

  const { mutateAsync: deleteDevicesData } = useMutation({
    mutationFn: () => {
      const devices = deleteDevices();
      deleteInterfaces();
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

  const {
    isLoading: isLoadingDevices,
    error: errorDevices,
    data: dataDevices,
  } = useQuery({
    queryKey: ['devices', success],
    queryFn: fetchDevices,
  });

  if (errorDevices) {
    console.error(errorDevices.message);
    return null;
  }

  return (
    <main className="page flex--grow container--wide flex">
      <div className="flex--column flex">
        <MyForm
          onSubmit={async (values, formikHelpers) => {
            await postNetwork(values);
            formikHelpers.resetForm();
          }}
        />
        <MyButton onClick={handleDelete}>Delete</MyButton>
        <MyButton
          onClick={() => {
            console.log('clicked modal');
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

      <div>
        <h2>Devices in network</h2>
        {isLoadingDevices ? (
          <div>loading</div>
        ) : (
          <MyTable data={dataDevices ?? []} />
        )}
      </div>
    </main>
  );
};

export default Database;
