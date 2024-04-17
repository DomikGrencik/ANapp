import { FC } from 'react';

export interface InputProps {
  onChange?: (e: React.ChangeEvent<HTMLInputElement>) => Promise<string | void>;
  label?: string;
  value?: string;
  placeholder?: string;
  required?: boolean;
}

const MyInput: FC<InputProps> = ({
  onChange,
  label,
  value,
  placeholder,
  required,
}) => {
  return (
    <div>
      <label className="my-input__label">{label}</label>
      <input
        className="my-input"
        value={value}
        placeholder={placeholder}
        onChange={onChange}
        required={required}
      />
    </div>
  );
};

export default MyInput;
