import { ChangeEvent, FC, FormEvent, useState } from 'react';
import { TextField } from '@mui/material';

import MyButton from './MyButton';

interface FormProps {
  onSubmit: () => void;
}

const MyForm: FC<FormProps> = ({ onSubmit }) => {
  const [input1, setInput1] = useState('');
  const [input2, setInput2] = useState('');
  const [input3, setInput3] = useState('');

  const handleInputChange1 = (event: ChangeEvent<HTMLInputElement>) => {
    setInput1(event.target.value);
  };

  const handleInputChange2 = (event: ChangeEvent<HTMLInputElement>) => {
    setInput2(event.target.value);
  };

  const handleInputChange3 = (event: ChangeEvent<HTMLInputElement>) => {
    setInput3(event.target.value);
  };

  const handleSubmit = (event: FormEvent<HTMLFormElement>) => {
    event.preventDefault();
    // Perform form submission logic here
    onSubmit();
  };

  return (
    <form onSubmit={handleSubmit}>
      <TextField
        label="Input 1"
        value={input1}
        onChange={handleInputChange1}
        required
      />
      <TextField
        label="Input 2"
        value={input2}
        onChange={handleInputChange2}
        required
      />
      <TextField
        label="Input 3"
        value={input3}
        onChange={handleInputChange3}
        required
      />
      <div>
        <MyButton type="submit">MyPostButton</MyButton>
      </div>
      <div>
        <MyButton onClick={() => console.log('clicked')}>MyButton</MyButton>
      </div>
    </form>
  );
};

export default MyForm;
