import { FC } from 'react';
import { Form, Formik, FormikHelpers } from 'formik';

import { YourFormData } from '../../types/core-types';
import MyButton from '../MyButton';
import MyLoader from '../MyLoader';

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
        //networkTraffic: '',
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
            props={{
              label: 'Počet používateľov',
              placeholder: 'Zadajte počet používateľov',
            }}
          />
          <MyFormikInput
            name="vlans"
            props={{
              label: 'VLANs',
              placeholder: 'Enter number of vlans',
              options: ['yes', 'no'],
            }}
          />
          <MyFormikInput
            name="userConnection"
            props={{
              label: 'Rýchlosť pripojenia používateľov',
              placeholder: 'Enter connection speed of users',
              options: ['100', '1000'],
            }}
          />
          {/* <MyFormikInput
            name="networkTraffic"
            props={{
              label: 'Network traffic',
              placeholder: 'Enter network traffic',
              options: ['small', 'medium', 'large'],
            }}
          /> */}
          <MyButton type="submit" disabled={isSubmitting}>
            Odoslať formulár
          </MyButton>
          {isSubmitting ? <MyLoader text="Odosielanie formulára" /> : null}
        </Form>
      )}
    </Formik>
  );
};

export default MyForm;
