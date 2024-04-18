import { FC, useState } from 'react';
import { z } from 'zod';

import { dataSchemaDevices } from '../pages/Database';

import MyModal from './MyModal';

interface TableProps {
  //onClick: () => void;
  data: z.infer<typeof dataSchemaDevices>;
  isLoading?: boolean;
}

const MyTable: FC<TableProps> = ({ data, isLoading }) => {
  const [open, setOpen] = useState(false);

  const [devData, setDevData] = useState({
    id: 0,
    name: '',
    type: '',
    device_id: 0,
  });

  return (
    <>
      <div className="my-table__wrapper">
        {/* <TableContainer component={Paper}>
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
        </TableContainer> */}
        {/* {isLoading ? <CircularProgress /> : null} */}

        <div className="my-table">
          <div className="my-table__header">
            <div>ID</div>
            <div>name</div>
            <div>type</div>
            <div>device_id</div>
          </div>

          {isLoading ? (
            <div>loading</div>
          ) : (
            <div className="my-table__body-wrapper">
              {data?.map(({ id, name, type, device_id }) => (
                <div
                  className="my-table__body"
                  onClick={() => {
                    setOpen(true);
                    setDevData({ id, name, type, device_id });
                  }}
                  onKeyDown={(e) => {
                    e.key === 'Enter' &&
                      (setOpen(true),
                      setDevData({ id, name, type, device_id }));
                  }}
                  key={id}
                  role="button"
                  tabIndex={0}
                >
                  <div>{id}</div>
                  <div>{name}</div>
                  <div>{type}</div>
                  <div>{device_id}</div>
                </div>
              ))}
            </div>
          )}
        </div>
      </div>

      {open ? (
        <div>
          <MyModal
            isOpen={open}
            onClose={() => setOpen(false)}
            hasTable
            idDevice={devData.id}
          >
            {devData.id} {devData.name}
          </MyModal>
        </div>
      ) : null}
    </>
  );
};

export default MyTable;
