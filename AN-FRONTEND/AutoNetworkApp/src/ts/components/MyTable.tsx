import { FC, useState } from 'react';
import {
  CircularProgress,
  Paper,
  Table,
  TableBody,
  TableCell,
  TableContainer,
  TableHead,
  TableRow,
} from '@mui/material';
import { useQuery } from '@tanstack/react-query';
import { z } from 'zod';

import { dataSchemaDevices, dataSchemaInterface } from '../pages/Database';
import { API_ROUTE_BASE } from '../utils/variables';

import MyModal from './MyModal';

interface TableProps {
  //onClick: () => void;
  data: z.infer<typeof dataSchemaDevices>;
}

const MyTable: FC<TableProps> = ({ data }) => {
  const [open, setOpen] = useState(false);

  const [devData, setDevData] = useState({
    id: 0,
    name: '',
    type: '',
    device_id: 0,
  });

  const fetchInterfacesOfDevice = async () => {
    const response = await fetch(
      `${API_ROUTE_BASE}interface_of_devices/getInterfacesOfDevice/${devData.id}`,
      {
        method: 'GET',
      }
    );
    const json = await response.json();

    return dataSchemaInterface.parse(json);
  };

  const {
    isLoading: isLoadingInterfaces,
    error: errorInterfaces,
    data: dataInterfaces,
  } = useQuery({
    queryKey: ['interfaces', devData],
    queryFn: fetchInterfacesOfDevice,
  });

  if (errorInterfaces) {
    console.error(errorInterfaces.message);
    return null;
  }

  return (
    <div>
      <TableContainer component={Paper} sx={{height: '80vh'}}>
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
            {data?.map(({ id, name, type, device_id }) => (
              <TableRow
                onClick={() => {
                  setOpen(true);
                  setDevData({ id, name, type, device_id });
                }}
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

      {open ? (
        <div>
          <MyModal isOpen={open} onClose={() => setOpen(false)}>
            {devData.id} {devData.name}
            <TableContainer component={Paper}>
              <Table sx={{ minWidth: 250 }} aria-label="simple table">
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
            {isLoadingInterfaces ? (
              <div>
                <CircularProgress sx={{ margin: '20px' }} />
              </div>
            ) : null}
          </MyModal>
        </div>
      ) : null}
    </div>
  );
};

export default MyTable;
