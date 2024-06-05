import { FC, useState } from 'react';
import { FormikHelpers } from 'formik';

import MyForm from '../components/form/MyForm';
import MyButton from '../components/MyButton';
import MyLoader from '../components/MyLoader';
import MyModal from '../components/MyModal';
import MyTable from '../components/MyTable';
import MyTopology from '../components/topology/MyTopology';
import { YourFormData } from '../types/core-types';
import useDeleteDevices from '../utils/hooks/useDeleteDevices';
import useFetchConnections from '../utils/hooks/useFetchConnentions';
import useFetchDeviceDatabase from '../utils/hooks/useFetchDeviceDatabase';
import useFetchDevices from '../utils/hooks/useFetchDevices';
import usePostNetwork from '../utils/hooks/usePostNetwork';

const AutoNetwork: FC = () => {
  const [open, setOpen] = useState(false);

  const postNetworkData = usePostNetwork();

  const { deleteDevices, isPending: isPendingDevices } = useDeleteDevices();

  /* const { deleteInterfaces, isPending: isPendingInterfaces } =
    useDeleteInterfaces();
  const { deleteConnections, isPending: isPendingConnections } =
    useDeleteConnections(); */

  const {
    data: dataDeviceDatabase,
    isLoading: isLoadingDeviceDatabase,
    error: errorDeviceDatabase,
  } = useFetchDeviceDatabase();

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

  if (errorDeviceDatabase) {
    console.error(errorDeviceDatabase.message);
    return null;
  }

  if (errorDevices) {
    console.error(errorDevices.message);
    return null;
  }

  if (errorConnections) {
    console.error(errorConnections.message);
    return null;
  }

  /* if (isPendingDevices || isPendingInterfaces || isPendingConnections) {
    console.log('isPending');
  } */

  const handleSubmit = async (
    values: YourFormData,
    formikHelpers: FormikHelpers<YourFormData>
  ) => {
    await postNetworkData(values);
    formikHelpers.resetForm();
  };

  const handleDelete = () => {
    console.log('delete');
    deleteDevices();
    /* deleteInterfaces();
    deleteConnections(); */
  };

  return (
    <main className="layout">
      <div className="layout__form">
        <MyForm onSubmit={handleSubmit} />
        <MyButton className="layout__delete-button" onClick={handleDelete}>
          Delete
        </MyButton>
        {/* <MyButton
          className="layout__delete-button"
          onClick={() => setOpen(true)}
        >
          Modal
        </MyButton> */}
      </div>

      <div className="layout__topology">
        <MyTopology
          dataDevices={dataDevices ?? []}
          dataConnections={dataConnections ?? []}
        />
      </div>

      <div className="layout__table">
        <div className="layout__table-wrapper">
          <MyTable
            data={{
              devices: dataDevices ?? [],
              devicesDatabase: dataDeviceDatabase ?? [],
            }}
            isLoading={
              isLoadingDevices ||
              isLoadingConnections ||
              isLoadingDeviceDatabase
            }
          />
        </div>
      </div>

      {isPendingDevices /* || isPendingInterfaces || isPendingConnections */ ? (
        <MyLoader text="Mazanie dát" />
      ) : null}

      {open ? (
        <MyModal isOpen={open} onClose={() => setOpen(false)}>
          <p style={{ width: '100%' }}>
            Lorem ipsum dolor sit amet consectetur adipisicing elit. Quisquam
            aperiam laudantium ea odio repellendus, omnis iure, veniam in
            voluptatum eos possimus dolorum quae sunt, adipisci culpa error
            dignissimos temporibus porro. Lorem
          </p>
          <p style={{ width: '100%' }}>
            Lorem ipsum dolor sit amet consectetur adipisicing elit. Quisquam
            aperiam laudantium ea odio repellendus, omnis iure, veniam in
            voluptatum eos possimus dolorum quae sunt, adipisci culpa error
            dignissimos temporibus porro. Lorem
          </p>
          <p style={{ width: '100%' }}>
            Lorem ipsum dolor sit amet consectetur adipisicing elit. Quisquam
            aperiam laudantium ea odio repellendus, omnis iure, veniam in
            voluptatum eos possimus dolorum quae sunt, adipisci culpa error
            dignissimos temporibus porro. Lorem
          </p>
          <p style={{ width: '100%' }}>
            Lorem ipsum dolor sit amet consectetur adipisicing elit. Quisquam
            aperiam laudantium ea odio repellendus, omnis iure, veniam in
            voluptatum eos possimus dolorum quae sunt, adipisci culpa error
            dignissimos temporibus porro. Lorem
          </p>
          <p style={{ width: '100%' }}>
            Lorem ipsum dolor sit amet consectetur adipisicing elit. Quisquam
            aperiam laudantium ea odio repellendus, omnis iure, veniam in
            voluptatum eos possimus dolorum quae sunt, adipisci culpa error
            dignissimos temporibus porro. Lorem
          </p>
          <p style={{ width: '100%' }}>
            Lorem ipsum dolor sit amet consectetur adipisicing elit. Quisquam
            aperiam laudantium ea odio repellendus, omnis iure, veniam in
            voluptatum eos possimus dolorum quae sunt, adipisci culpa error
            dignissimos temporibus porro. Lorem
          </p>
          <p style={{ width: '100%' }}>
            Lorem ipsum dolor sit amet consectetur adipisicing elit. Quisquam
            aperiam laudantium ea odio repellendus, omnis iure, veniam in
            voluptatum eos possimus dolorum quae sunt, adipisci culpa error
            dignissimos temporibus porro. Lorem
          </p>
        </MyModal>
      ) : null}
    </main>
  );
};

export default AutoNetwork;
