import { FC } from 'react';
import {
  Paper,
  Table,
  TableCell,
  TableContainer,
  TableHead,
  TableRow,
} from '@mui/material';

interface TableProps {
  onClick: () => void;
}

const MyTable: FC<TableProps> = ({ onClick }) => {
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
          {/* <TableBody>
            {dataDevices?.map(({ id, name, type, device_id }) => (
              <TableRow
                onClick={onClick}
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
          </TableBody> */}
        </Table>
      </TableContainer>
    </div>
  );
};

export default MyTable;
