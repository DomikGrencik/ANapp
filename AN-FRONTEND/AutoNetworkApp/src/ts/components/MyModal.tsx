import { FC, ReactNode, useState } from 'react';

import MyButton from './MyButton';

interface ModalProps {
  isOpen: boolean;
  onClose: () => void;
  children?: ReactNode;
}

const Modal: FC<ModalProps> = ({ isOpen, onClose, children }) => {
  const [isAnimating, setIsAnimating] = useState(false);

  const handleClose = () => {
    setIsAnimating(true);
    setTimeout(() => {
      setIsAnimating(false);
      onClose();
    }, 300); // Adjust the duration as needed
  };

  if (!isOpen && !isAnimating) {
    return null;
  }

  return (
    <div className="my-modal">
      <div
        className="my-modal"
        role="button"
        tabIndex={0}
        onClick={handleClose}
        onKeyDown={handleClose}
      />
      <div className="my-modal my-modal--overlay my-modal--content">
        {children}
        <MyButton onClick={handleClose}>Close</MyButton>
      </div>
    </div>
  );
};

export default Modal;
