import { FC } from 'react';
import { FormikHelpers } from 'formik';

import MyForm from '../components/form/MyForm';
import MyButton from '../components/MyButton';
import MyTable from '../components/MyTable';
import MyTopology from '../components/topology/MyTopology';
import { YourFormData } from '../types/core-types';
import useDeleteConnections from '../utils/hooks/useDeleteConnections';
import useDeleteDevices from '../utils/hooks/useDeleteDevices';
import useDeleteInterfaces from '../utils/hooks/useDeleteInterfaces';
import useFetchConnections from '../utils/hooks/useFetchConnentions';
import useFetchDevices from '../utils/hooks/useFetchDevices';
import usePostNetwork from '../utils/hooks/usePostNetwork';

const AutoNetwork: FC = () => {
  const postNetworkData = usePostNetwork();
  const deleteDevices = useDeleteDevices();
  const deleteInterfaces = useDeleteInterfaces();
  const deleteConnections = useDeleteConnections();

  const {
    data: dataDevices,
    isLoading: isLoadingDevices,
    error: errorDevices,
  } = useFetchDevices();

  const {
    data: dataConnections,
    isLoading: isLoadingConnections,
    error: errorConnections,
  } = useFetchConnections();

  if (errorDevices) {
    console.error(errorDevices.message);
    return null;
  }

  if (errorConnections) {
    console.error(errorConnections.message);
    return null;
  }

  const handleSubmit = async (
    values: YourFormData,
    formikHelpers: FormikHelpers<YourFormData>
  ) => {
    try {
      await postNetworkData(values);
      formikHelpers.resetForm();
    } catch (error) {
      console.error('Error submitting form:', error);
    }
  };

  const handleDelete = () => {
    console.log('delete');
    deleteDevices();
    deleteInterfaces();
    deleteConnections();
  };

  return (
    <main className="page flex--justify-space-between container--wide flex">
      <div className="flex--column flex">
        <MyForm onSubmit={handleSubmit} />
        <MyButton onClick={handleDelete}>Delete</MyButton>
      </div>

      <div
        style={{
          width: '100%',
          minWidth: '300px',
          border: '1px solid #e5e5e5',
          borderRadius: '5px',
          marginLeft: '20px',
          marginRight: '20px',
        }}
      >
        <MyTopology
          dataDevices={dataDevices ?? []}
          dataConnections={dataConnections ?? []}
        />
      </div>

      <div>
        {isLoadingDevices || isLoadingConnections ? (
          <div>loading</div>
        ) : (
          <MyTable data={dataDevices ?? []} />
        )}
      </div>
    </main>
  );
};

export default AutoNetwork;
