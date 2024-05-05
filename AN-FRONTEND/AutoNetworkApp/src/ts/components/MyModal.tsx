import { FC, ReactNode } from 'react';
import { CircularProgress } from '@mui/material';

import useFetchConnections from '../utils/hooks/useFetchConnentions';
import useFetchDevices from '../utils/hooks/useFetchDevices';
import useFetchInterfaces from '../utils/hooks/useFetchInterfaces';

import MyButton from './MyButton';

interface ModalProps {
  isOpen: boolean;
  onClose: () => void;
  children?: ReactNode;
  hasTable?: boolean;
  idDevice?: number;
}

const Modal: FC<ModalProps> = ({
  isOpen,
  onClose,
  children,
  hasTable,
  idDevice,
}) => {
  const {
    data: dataDevices,
    isLoading: isLoadingDevices,
    error: errorDevices,
  } = useFetchDevices();

  const {
    data: dataInterfaces,
    isLoading: isLoadingInterfaces,
    error: errorInterfaces,
  } = useFetchInterfaces();

  const {
    data: dataConnections,
    isLoading: isLoadingConnections,
    error: errorConnections,
  } = useFetchConnections();

  if (errorDevices) {
    console.error(errorDevices.message);
    return null;
  }

  if (errorInterfaces) {
    console.error(errorInterfaces.message);
    return null;
  }

  if (errorConnections) {
    console.error(errorConnections.message);
    return null;
  }

  // Filtrovanie zariadení
  const filteredDevices = dataDevices?.filter(
    (device) => device.id === idDevice
  );

  // Filtrovanie rozhraní
  const filteredInterfaces = dataInterfaces?.filter(
    (interfaceItem) => interfaceItem.id === idDevice
  );

  // Filtrovanie spojení
  const filteredConnections = dataConnections?.filter((connectionItem) => {
    return (
      connectionItem.device_id1 === idDevice ||
      connectionItem.device_id2 === idDevice
    );
  });

  // Rozšírenie rozhraní o príslušné zariadenia
  const interfacesWithDevices = filteredInterfaces?.map((interfaceItem) => ({
    ...interfaceItem,
    device:
      filteredDevices?.find(
        (deviceItem) => deviceItem.id === interfaceItem.id
      ) || null,
  }));

  // Rozšírenie spojení o príslušné zariadenia
  const connectionsWithDevices = filteredConnections?.map((connectionItem) => ({
    ...connectionItem,
    device1:
      dataDevices?.find(
        (deviceItem) => deviceItem.id === connectionItem.device_id1
      ) || null,
    device2:
      dataDevices?.find(
        (deviceItem) => deviceItem.id === connectionItem.device_id2
      ) || null,
  }));

  // Rozšírenie rozhraní o príslušné spojenia
  const interfacesWithConnections = interfacesWithDevices?.map(
    (interfaceItem) => ({
      ...interfaceItem,
      connection:
        connectionsWithDevices?.find(
          (connectionItem) =>
            connectionItem.interface_id1 === interfaceItem.interface_id ||
            connectionItem.interface_id2 === interfaceItem.interface_id
        ) || null,
    })
  );

  const isLoadingData =
    isLoadingDevices || isLoadingInterfaces || isLoadingConnections;

  if (!isOpen) {
    return null;
  }

  return (
    <div
      className="my-modal__background"
      onClick={onClose}
      onKeyDown={(e) => e.key === 'Esc' && onClose}
      role="button"
      tabIndex={0}
    >
      <div
        className="my-modal"
        onClick={(e) => e.stopPropagation()}
        onKeyDown={(e) => e.stopPropagation()}
        role="button"
        tabIndex={0}
      >
        <div className="my-modal__content">
          {children}
          {hasTable ? (
            <div className="my-table">
              <div className="my-table__layout my-table__layout-modal my-table__layout-header">
                <div>Interface</div>
                <div />
                <div>Pripojené zariadenie</div>
                <div>Rozhranie zariadenia</div>
              </div>
              {isLoadingData ? (
                <div className="my-table__loading-wrapper">
                  <div className="my-table__loading">
                    <CircularProgress sx={{ color: '#d6d9dd' }} />
                  </div>
                </div>
              ) : (
                <div className="my-table__body">
                  {/* {filteredInterfaces?.map(
                    ({ interface_id, name, id, type }) => (
                      <div
                        className="my-table__layout my-table__layout-body"
                        key={interface_id}
                      >
                        <div>{interface_id}</div>
                        <div>{name}</div>
                        <div>{id}</div>
                        <div>{type}</div>
                      </div>
                    )
                  )} */}
                  {interfacesWithConnections?.map(
                    ({ interface_id, name, id, connection }) => (
                      <div
                        className="my-table__layout my-table__layout-modal my-table__layout-body"
                        key={interface_id}
                      >
                        <div>{name}</div>
                        {connection ? <div>connected</div> : null}
                        {id === connection?.device_id1 ? (
                          <>
                            <div>{connection?.device2?.name}</div>
                            <div>{connection?.name2}</div>
                          </>
                        ) : (
                          <>
                            <div>{connection?.device1?.name}</div>
                            <div>{connection?.name1}</div>
                          </>
                        )}
                      </div>
                    )
                  )}
                </div>
              )}
            </div>
          ) : null}
        </div>
        <div>
          <MyButton onClick={onClose}>Close</MyButton>
        </div>
      </div>
    </div>
  );
};

export default Modal;
