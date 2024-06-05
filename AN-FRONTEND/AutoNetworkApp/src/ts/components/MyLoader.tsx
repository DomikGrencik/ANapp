import { FC } from 'react';
import { CircularProgress } from '@mui/material';

export interface LoaderProps {
  text?: string;
}

const MyLoader: FC<LoaderProps> = ({ text }) => {
  return (
    <div className="loading">
      <div className="loader">
        <p className="text">{text}</p>
        <CircularProgress sx={{ color: '#d6d9dd', zIndex: 1000 }} />
      </div>
    </div>
  );
};

export default MyLoader;
