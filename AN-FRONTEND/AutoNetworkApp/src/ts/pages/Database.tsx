import { FC, FormEvent, useState } from 'react';
import {
  Paper,
  Table,
  TableBody,
  TableCell,
  TableContainer,
  TableHead,
  TableRow,
} from '@mui/material';
import { useMutation, useQuery } from '@tanstack/react-query';
import { z } from 'zod';

import MyForm from '../components/form/MyForm';
import MyButton from '../components/MyButton';
import { API_ROUTE_BASE } from '../utils/variables';

type YourFormData = {
  users: string;
  vlans: string;
  IPaddr: string;
  userConnection: string;
};

const dataSchemaDevices = z.array(
  z.object({
    id: z.number().int(),
    name: z.string(),
    type: z.string(),
    device_id: z.number().int(),
  })
);

const dataSchemaInterface = z.array(
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
  const [networkData, setNetworkData] = useState({
    users: '',
    vlans: '1',
    IPaddr: '',
    userConnection: '',
  });

  const [success, setSuccess] = useState(false);

  const submitForm = async (networkData: YourFormData) => {
    const response = await fetch(`${API_ROUTE_BASE}devices_in_networks`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
      },
      body: JSON.stringify(networkData),
    });

    if (!response.ok) {
      throw new Error('Failed to submit form');
    }

    return response.json();
  };

  const { mutateAsync: postNetwork } = useMutation({
    mutationFn: submitForm,
    onSuccess: () => {
      console.log('Form submitted successfully!');
      setSuccess(!success);
    },
    onError: (error) => {
      console.error('Form submission error:', error.message);
    },
  });

  const handleSubmit = (event: FormEvent<HTMLFormElement>) => {
    console.log('submit');
    console.log(JSON.stringify(networkData));
    event.preventDefault();
    return postNetwork(networkData);
  };

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

  const fetchDevices = async () => {
    const response = await fetch(`${API_ROUTE_BASE}devices_in_networks`, {
      method: 'GET',
    });
    const json = await response.json();

    return dataSchemaDevices.parse(json);
  };

  const fetchInterfaces = async () => {
    const response = await fetch(`${API_ROUTE_BASE}interface_of_devices`, {
      method: 'GET',
    });
    const json = await response.json();

    return dataSchemaInterface.parse(json);
  };

  const {
    isLoading: isLoadingDevices,
    error: errorDevices,
    data: dataDevices,
  } = useQuery({
    queryKey: ['devices', success],
    queryFn: fetchDevices,
  });

  const {
    isLoading: isLoadingInterfaces,
    error: errorInterfaces,
    data: dataInterfaces,
  } = useQuery({
    queryKey: ['interfaces', success],
    queryFn: fetchInterfaces,
  });

  if (isLoadingDevices) {
    console.log('loading devices');
  }
  if (isLoadingInterfaces) {
    console.log('loading interfaces');
  }

  if (errorDevices) {
    console.error(errorDevices.message);
    return null;
  }

  if (errorInterfaces) {
    console.error(errorInterfaces.message);
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
      </div>
      {/* <form
        className="page__form flex--grow flex--column flex"
        onSubmit={handleSubmit}
      >
        <h1>Form</h1>
        <div>
          <TextField
            onChange={(event) =>
              setNetworkData({ ...networkData, users: event.target.value })
            }
            required
            label="Users"
            variant="outlined"
            autoComplete="off"
          />
        </div>
        <div>
          <TextField
            onChange={(event) =>
              setNetworkData({ ...networkData, vlans: event.target.value })
            }
            required
            label="Vlans"
            variant="outlined"
            autoComplete="off"
          />
        </div>
        <div>
          <TextField
            onChange={(event) =>
              setNetworkData({ ...networkData, IPaddr: event.target.value })
            }
            required
            label="IP address"
            variant="outlined"
            autoComplete="off"
          />
        </div>
        <div>
          <TextField
            onChange={(event) =>
              setNetworkData({
                ...networkData,
                userConnection: event.target.value,
              })
            }
            required
            label="Connection"
            variant="outlined"
            autoComplete="off"
          />
        </div>
        <div>
          <Button type="submit" variant="contained" sx={{ top: 10 }}>
            Post
          </Button>
        </div>
        <div>
          <Button
            onClick={() => {
              handleDelete();
            }}
            variant="contained"
            color="error"
            sx={{ top: 20 }}
          >
            Delete
          </Button>
        </div>
        <div>
          <MyButton type="submit">MyPostButton</MyButton>
        </div>
        <div>
          <MyButton onClick={() => console.log('clicked')}>MyButton</MyButton>
        </div>
      </form> */}

      <div>
        <h2>Devices in network</h2>
        {isLoadingDevices ? (
          <div>loading</div>
        ) : (
          <div>
            <TableContainer component={Paper}>
              <Table sx={{ minWidth: 250 }} aria-label="simple table">
                <TableHead>
                  <TableRow>
                    <TableCell>ID</TableCell>
                    <TableCell align="right">name</TableCell>
                    <TableCell align="right">type</TableCell>
                    <TableCell align="right">device_id</TableCell>
                  </TableRow>
                </TableHead>
                <TableBody>
                  {dataDevices?.map(({ id, name, type, device_id }) => (
                    <TableRow
                      onClick={() => console.log('clicked')}
                      hover
                      key={id}
                      sx={{
                        '&:last-child td, &:last-child th': { border: 0 },
                        cursor: 'pointer',
                      }}
                    >
                      <TableCell component="th" scope="row">
                        {id}
                      </TableCell>
                      <TableCell align="right">{name}</TableCell>
                      <TableCell align="right">{type}</TableCell>
                      <TableCell align="right">{device_id}</TableCell>
                    </TableRow>
                  ))}
                </TableBody>
              </Table>
            </TableContainer>
            {/* <ul>
                {data?.map((device) => (
                  <li key={device.id}>{[device.name]}</li>
                ))}
              </ul> */}
          </div>
        )}
      </div>

      <div>
        <h2>Interface of device</h2>
        {isLoadingInterfaces ? (
          <div>loading</div>
        ) : (
          <div>
            <TableContainer component={Paper}>
              <Table sx={{ minWidth: 350 }} aria-label="simple table">
                <TableHead>
                  <TableRow>
                    <TableCell>interface_id</TableCell>
                    <TableCell align="right">name</TableCell>
                    <TableCell align="right">IP address</TableCell>
                    <TableCell align="right">interface_id2</TableCell>
                    <TableCell align="right">id</TableCell>
                    <TableCell align="right">type</TableCell>
                  </TableRow>
                </TableHead>
                <TableBody>
                  {dataInterfaces?.map(
                    ({
                      interface_id,
                      name,
                      IP_address,
                      interface_id2,
                      id,
                      type,
                    }) => (
                      <TableRow
                        // eslint-disable-next-line react/no-array-index-key
                        key={interface_id}
                        sx={{
                          '&:last-child td, &:last-child th': { border: 0 },
                        }}
                      >
                        <TableCell component="th" scope="row">
                          {interface_id}
                        </TableCell>
                        <TableCell align="right">{name}</TableCell>
                        <TableCell align="right">{IP_address}</TableCell>
                        <TableCell align="right">{interface_id2}</TableCell>
                        <TableCell align="right">{id}</TableCell>
                        <TableCell align="right">{type}</TableCell>
                      </TableRow>
                    )
                  )}
                </TableBody>
              </Table>
            </TableContainer>
            {/* <ul>
                {data?.map((device) => (
                  <li key={device.id}>{[device.name]}</li>
                ))}
              </ul> */}
          </div>
        )}
      </div>
    </main>
  );
};

export default Database;
