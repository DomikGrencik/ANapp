import { FC, ReactNode } from 'react';
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

import { dataSchemaInterface } from '../pages/Database';
import { API_ROUTE_BASE } from '../utils/variables';

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
  const fetchInterfacesOfDevice = async () => {
    const response = await fetch(
      `${API_ROUTE_BASE}interface_of_devices/getInterfacesOfDevice/${idDevice}`,
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
    queryKey: ['interfaces', idDevice],
    queryFn: fetchInterfacesOfDevice,
  });

  if (errorInterfaces) {
    console.error(errorInterfaces.message);
    return null;
  }

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
        <div className='my-modal__content'>
          {children}
          {hasTable ? (
            <>
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
            </>
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
