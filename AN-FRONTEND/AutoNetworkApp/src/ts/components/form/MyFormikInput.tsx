import { FC } from 'react';
import { TextField } from '@mui/material';
import { useField } from 'formik';

const MyFormikInput: FC<{
  name: string;
  props?: {
    label?: string;
    placeholder?: string;
  };
}> = ({ name, props }) => {
  const [{ value }, , { setValue }] = useField<string>(name);

  return (
    <TextField
      {...props}
      value={value}
      onChange={(e) => setValue(e.target.value)}
      required
      variant="outlined"
      autoComplete="off"
    />
  );
};

export default MyFormikInput;
