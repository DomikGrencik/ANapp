import { FC } from 'react';
import { useField } from 'formik';

import MyInput from './MyInput';

const MyFormikInput: FC<{
  name: string;
  props?: {
    label?: string;
    placeholder?: string;
    options?: string[];
  };
}> = ({ name, props }) => {
  const [{ value }, , { setValue }] = useField<string>(name);

  return (
    <MyInput
      {...props}
      value={value}
      onChange={(e: React.ChangeEvent<HTMLInputElement>) =>
        setValue(e.target.value)
      }
      required
    />
  );
};

export default MyFormikInput;
