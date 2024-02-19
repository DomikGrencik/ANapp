import { FC } from 'react';
import { Form, Formik, FormikHelpers } from 'formik';

import MyButton from '../MyButton';

import MyFormikInput from './MyFormikInput';

export type YourFormData = {
  users: string;
  vlans: string;
  IPaddr: string;
  userConnection: string;
};

interface FormProps {
  onSubmit: (
    values: YourFormData,
    formikHelpers: FormikHelpers<YourFormData>
  ) => Promise<void>;
}

const MyForm: FC<FormProps> = ({ onSubmit }) => {
  return (
    <Formik
      initialValues={{
        users: '',
        vlans: '',
        IPaddr: '',
        userConnection: '',
      }}
      onSubmit={async (values, formikHelpers) => {
        try {
          await onSubmit(values, formikHelpers);
        } catch (error) {
          console.error(error);
        }
      }}
    >
      {({ isSubmitting }) => (
        <Form className="flex--column flex my-form">
          <MyFormikInput
            name="users"
            props={{ label: 'Users', placeholder: 'Enter number of users' }}
          />
          <MyFormikInput
            name="vlans"
            props={{ label: 'Vlans', placeholder: 'Enter number of vlans' }}
          />
          <MyFormikInput
            name="IPaddr"
            props={{ label: 'IP address', placeholder: 'Enter IP address' }}
          />
          <MyFormikInput
            name="userConnection"
            props={{
              label: 'Connection',
              placeholder: 'Enter connection speed of users',
            }}
          />
          <MyButton type="submit" disabled={isSubmitting}>
            Submit
          </MyButton>
        </Form>
      )}
    </Formik>
  );
};

export default MyForm;
