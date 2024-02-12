import { FC } from 'react';

export interface ButtonProps {
  onClick?: () => void;
  type?: 'button' | 'submit' | 'reset';
  disabled?: boolean;
  children?: string;
  className?: string;
}

const MyButton: FC<ButtonProps> = ({
  onClick,
  type,
  disabled,
  children,
  className,
}) => {
  return (
    <button
      className={`my-button ${className}`}
      type={type || 'button'}
      disabled={disabled}
      onClick={onClick}
    >
      {children}
    </button>
  );
};

export default MyButton;
