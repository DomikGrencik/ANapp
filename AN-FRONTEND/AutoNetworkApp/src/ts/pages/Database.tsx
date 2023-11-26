//import { useQuery } from '@tanstack/react-query';

import { useState } from 'react';
import { Button, TextField } from '@mui/material';
import { useMutation, useQuery } from '@tanstack/react-query';

const Database = () => {
  //console.log('database');
  const [networkData, setNetworkData] = useState({
    users: '',
    vlans: '',
    IPaddr: '',
    userConnection: '',
  });

  //console.log(JSON.stringify(networkData));

  const { mutateAsync } = useMutation({
    mutationFn: () =>
      fetch('http://127.0.0.1:80/api/devices_in_networks', {
        method: 'POST',
        body: JSON.stringify(networkData),
      }),
  });

  const { isPending, error, data } = useQuery({
    queryKey: ['devices'],
    queryFn: () =>
      fetch('http://127.0.0.1:80/api/devices_in_networks', {
        method: 'GET',
      }).then((res) => res.json()),
  });

  if (isPending) console.log('loading');

  if (error) console.error(error.message);

  //console.log(data);

  return (
    <main className="page flex--grow container--default flex">
      <form
        className="page__form flex--grow flex--column flex"
        onSubmit={async () => {
          try {
            console.log(JSON.stringify(networkData));
            await mutateAsync();
          } catch (error) {
            console.error(error);
          }
        }}
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
          {isPending ? (
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
