import { FC, useState } from 'react';
import {
  Paper,
  Table,
  TableBody,
  TableCell,
  TableContainer,
  TableHead,
  TableRow,
} from '@mui/material';
import { z } from 'zod';

import { dataSchemaDevices } from '../pages/Database';

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

  return (
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
          </MyModal>
        </div>
      ) : null}
    </div>
  );
};

export default MyTable;
