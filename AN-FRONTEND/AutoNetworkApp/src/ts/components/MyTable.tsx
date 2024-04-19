import { FC, useState } from 'react';
import { CircularProgress } from '@mui/material';
import { z } from 'zod';

import { dataSchemaDevices } from '../types/data-types';

import MyModal from './MyModal';

interface TableProps {
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
      <div className="my-table">
        <div className="my-table__layout my-table__layout-header">
          <div>ID</div>
          <div>name</div>
          <div>type</div>
          <div>device_id</div>
        </div>
        {isLoading ? (
          <div className="my-table__loading-wrapper">
            <div className="my-table__loading">
              <CircularProgress sx={{ color: '#d6d9dd' }} />
            </div>
          </div>
        ) : (
          <div className="my-table__body">
            {data?.map(({ id, name, type, device_id }) => (
              <div
                className="my-table__layout my-table__layout-body my-table__layout-body-interactive"
                onClick={() => {
                  setOpen(true);
                  setDevData({ id, name, type, device_id });
                }}
                onKeyDown={(e) => {
                  e.key === 'Enter' &&
                    (setOpen(true), setDevData({ id, name, type, device_id }));
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

      <MyModal
        isOpen={open}
        onClose={() => setOpen(false)}
        hasTable
        idDevice={devData.id}
      >
        {devData.id} {devData.name}
      </MyModal>
    </>
  );
};

export default MyTable;
