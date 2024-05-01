import { FC } from 'react';

export interface InputProps {
  onChange?:
    | ((e: React.ChangeEvent<HTMLInputElement>) => Promise<string | void>)
    | ((e: React.ChangeEvent<HTMLSelectElement>) => Promise<string | void>);
  label?: string;
  value?: string;
  placeholder?: string;
  required?: boolean;
  options?: string[];
}

const MyInput: FC<InputProps> = ({
  onChange,
  label,
  value,
  placeholder,
  required,
  options,
}) => {
  return (
    <div>
      <label className="my-input__label">{label}</label>
      {options ? (
        <select
          className="my-input"
          value={value}
          required={required}
          onChange={
            onChange as (e: React.ChangeEvent<HTMLSelectElement>) => void
          }
        >
          <option className="option" value="" disabled>
            Vyberte možnosť
          </option>
          {options.map((option) => (
            <option className="option" key={option} value={option}>
              {option}
            </option>
          ))}
        </select>
      ) : (
        <input
          className="my-input"
          value={value}
          placeholder={placeholder}
          onChange={
            onChange as (e: React.ChangeEvent<HTMLInputElement>) => void
          }
          required={required}
        />
      )}
    </div>
  );
};

export default MyInput;
