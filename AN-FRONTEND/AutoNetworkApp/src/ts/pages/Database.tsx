import { FC, FormEvent, useState } from 'react';
import { Button, TextField } from '@mui/material';
import { useMutation, useQuery } from '@tanstack/react-query';
import { z } from 'zod';

type YourFormData = {
  users: string;
  vlans: string;
  IPaddr: string;
  userConnection: string;
};

const dataSchema = z.array(
  z.object({
    id: z.number().int(),
    name: z.string(),
  })
);

const Database: FC = () => {
  //console.log('database');

  const [networkData, setNetworkData] = useState({
    users: '',
    vlans: '',
    IPaddr: '',
    userConnection: '',
  });

  const submitForm = async (networkData: YourFormData) => {
    const response = await fetch(
      'http://127.0.0.1:80/api/devices_in_networks',
      {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
        },
        body: JSON.stringify(networkData),
      }
    );

    if (!response.ok) {
      throw new Error('Failed to submit form');
    }

    return response.json();
  };

  const { mutateAsync } = useMutation({
    mutationFn: submitForm,
    onSuccess: () => {
      console.log('Form submitted successfully!');
    },
    onError: (error) => {
      console.error('Form submission error:', error.message);
    },
  });

  const handleSubmit = (event: FormEvent<HTMLFormElement>) => {
    console.log('submit');
    event.preventDefault();
    return mutateAsync(networkData);
  };

  /* const { mutateAsync } = useMutation({
    mutationFn: () =>
      fetch('http://127.0.0.1:80/api/devices_in_networks', {
        method: 'POST',
        body: JSON.stringify(networkData),
      }),
  }); */

  const { isLoading, error, data } = useQuery({
    queryKey: ['devices'],
    queryFn: async () => {
      const res = await fetch('http://127.0.0.1:80/api/devices_in_networks', {
        method: 'GET',
      });

      const json = res.json();

      return dataSchema.parse(json);
    },
  });

  if (isLoading) {
    console.log('loading');
  }

  if (error) {
    console.error(error.message);
    return null;
  }

  //console.log(data);

  return (
    <main className="page flex--grow container--default flex">
      <form
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
          <Button type="submit" variant="contained">
            Post
          </Button>
        </div>
      </form>

      <div>
        <h1>Data</h1>
        <div>
          <h2>Devices in network</h2>
          {isLoading ? (
            <div>loading</div>
          ) : (
            <div>
              <ul>
                {data?.map((device) => (
                  <li key={device.id}>{[device.name]}</li>
                ))}
              </ul>
            </div>
          )}
        </div>
      </div>
    </main>
  );
};

export default Database;
