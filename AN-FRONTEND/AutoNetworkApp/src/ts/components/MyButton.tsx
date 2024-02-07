import React from 'react';

interface ButtonProps {
  onClick: () => void;
  children: string;
}

const MyButton: React.FC<ButtonProps> = ({ onClick, children }) => {
  return (
    <button className="my-button" type="button" onClick={onClick}>
      {children}
    </button>
  );
};

export default MyButton;
