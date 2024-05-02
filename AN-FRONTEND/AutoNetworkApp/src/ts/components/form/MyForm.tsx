import { FC } from 'react';
import { Form, Formik, FormikHelpers } from 'formik';

import { YourFormData } from '../../types/core-types';
import MyButton from '../MyButton';

import MyFormikInput from './MyFormikInput';

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
        userConnection: '',
        networkTraffic: '',
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
        <Form className="my-form">
          <MyFormikInput
            name="users"
            props={{ label: 'Users', placeholder: 'Enter number of users' }}
          />
          <MyFormikInput
            name="vlans"
            props={{
              label: 'Vlans',
              placeholder: 'Enter number of vlans',
              options: ['yes', 'no'],
            }}
          />
          <MyFormikInput
            name="userConnection"
            props={{
              label: 'Connection',
              placeholder: 'Enter connection speed of users',
            }}
          />
          <MyFormikInput
            name="networkTraffic"
            props={{
              label: 'Network traffic',
              placeholder: 'Enter network traffic',
              options: ['small', 'medium', 'large'],
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
