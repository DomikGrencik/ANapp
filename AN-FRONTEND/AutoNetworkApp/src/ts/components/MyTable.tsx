import { FC, useState } from 'react';
import { CircularProgress } from '@mui/material';
import { z } from 'zod';

import {
  dataSchemaDeviceDatabase,
  dataSchemaDevices,
} from '../types/data-types';

import MyModal from './MyModal';

interface TableProps {
  data: {
    devices: z.infer<typeof dataSchemaDevices>;
    devicesDatabase: z.infer<typeof dataSchemaDeviceDatabase>;
  };
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

  const combinedData = data.devices.map((device) => {
    const matchingDevice = data.devicesDatabase?.find(
      (dbDevice) => dbDevice.device_id === device.device_id
    );
    return { ...device, ...matchingDevice };
  });

  return (
    <>
      <div className="my-table">
        <div className="my-table__layout my-table__layout-header">
          <div>Typ</div>
          <div>Názov</div>
          <div>Výrobca</div>
          <div>Model_zariadenia</div>
        </div>

        {isLoading ? (
          <div className="my-table__loading">
            <CircularProgress sx={{ color: '#d6d9dd' }} />
          </div>
        ) : (
          <div className="my-table__body">
            {combinedData?.map(
              ({ id, name, type, device_id, manufacturer, model }) => (
                <div
                  className="my-table__layout my-table__layout-body my-table__layout-body-interactive"
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
                  <div>{type}</div>
                  <div>{name}</div>
                  <div>{manufacturer}</div>
                  <div>{model}</div>
                </div>
              )
            )}
          </div>
        )}
      </div>

      {open ? (
        <MyModal
          isOpen={open}
          onClose={() => setOpen(false)}
          hasTable
          idDevice={devData.id}
        />
      ) : null}
    </>
  );
};

export default MyTable;
