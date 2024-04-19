import { FC, ReactNode } from 'react';
import { CircularProgress } from '@mui/material';

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
  const { data, isLoading, error } = useFetchInterfaces();
  const filteredData = data?.filter((item) => item.id === idDevice);

  if (error) {
    console.error(error.message);
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
        <div className="my-modal__content">
          {children}
          {hasTable ? (
            <div className="my-table">
              <div className="my-table__layout my-table__layout-header">
                <div>int_id</div>
                <div>name</div>
                <div>id</div>
                <div>type</div>
              </div>
              {isLoading ? (
                <div className="my-table__loading-wrapper">
                  <div className="my-table__loading">
                    <CircularProgress sx={{ color: '#d6d9dd' }} />
                  </div>
                </div>
              ) : (
                <div className="my-table__body">
                  {filteredData?.map(({ interface_id, name, id, type }) => (
                    <div
                      className="my-table__layout my-table__layout-body"
                      key={interface_id}
                    >
                      <div>{interface_id}</div>
                      <div>{name}</div>
                      <div>{id}</div>
                      <div>{type}</div>
                    </div>
                  ))}
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
