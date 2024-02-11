import { ComponentProps, FC, useId } from 'react';
import { useMutation } from '@tanstack/react-query';
import { Form, Formik, FormikHelpers, useField } from 'formik';

type FormData = {
  field1: string;
  field2: string;
  field3: boolean;
};

export const MyInput: FC<{
  label?: string;
  placeholder?: string;
  value: string;
  onChange: (value: string) => void;
}> = ({ label, placeholder, value, onChange }) => {
  const id = useId();

  return (
    <div>
      <label htmlFor={id}>{label}</label>
      <input
        id={id}
        type="text"
        placeholder={placeholder}
        value={value}
        onChange={(e) => onChange(e.target.value)}
      />
    </div>
  );
};

export const MyFormikInput: FC<{
  name: string;
  props?: Omit<ComponentProps<typeof MyInput>, 'value' | 'onChange'>;
}> = ({ name, props }) => {
  const [{ value }, , { setValue }] = useField(name);

  return <MyInput {...props} value={value} onChange={setValue} />;
};

export const MyForm: FC<{
  onSubmit: (
    values: FormData,
    formikHelpers: FormikHelpers<FormData>
  ) => Promise<void>;
}> = ({ onSubmit }) => {
  return (
    <Formik
      initialValues={{
        field1: '',
        field2: '',
        field3: false,
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
        <Form>
          <MyFormikInput
            name="field1"
            props={{
              label: 'Field 1',
              placeholder: 'Enter field 1',
            }}
          />
          <MyFormikInput
            name="field2"
            props={{
              label: 'Field 2',
              placeholder: 'Enter field 2',
            }}
          />
          <button disabled={isSubmitting}>submit</button>
        </Form>
      )}
    </Formik>
  );
};

export const MyComponent: FC = () => {
  const { mutateAsync: postNetwork } = useMutation({
    mutationFn: (values: FormData) => {
      return fetch('/api/network', {
        method: 'POST',
        body: JSON.stringify(values),
      });
    },
  });

  return (
    <MyForm
      onSubmit={async (values, formikHelpers) => {
        await postNetwork(values);
        formikHelpers.resetForm();
      }}
    />
  );
};
