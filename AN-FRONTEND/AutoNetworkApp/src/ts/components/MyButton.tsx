import { FC } from 'react';

interface ButtonProps {
  onClick?: () => void;
  type?: 'button' | 'submit' | 'reset' | undefined;
  children?: string;
}

const MyButton: FC<ButtonProps> = ({ onClick, type, children }) => {
  return (
    <button className="my-button" type={type || 'button'} onClick={onClick}>
      {children}
    </button>
  );
};

export default MyButton;
