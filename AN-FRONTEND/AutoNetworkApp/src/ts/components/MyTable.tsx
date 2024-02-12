import { FC } from 'react';
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

interface TableProps {
  //onClick: () => void;
  data: z.infer<typeof dataSchemaDevices>;
}

const MyTable: FC<TableProps> = ({ data }) => {
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
                  console.log('clicked');
                  console.log({ id, name, type, device_id });
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
    </div>
  );
};

export default MyTable;
