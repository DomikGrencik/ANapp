import { FC } from 'react';
import { Handle, NodeProps, Position } from 'reactflow';
import { useQuery } from '@tanstack/react-query';

import { dataSchemaInterface } from '../../pages/Database';
import { API_ROUTE_BASE } from '../../utils/variables';

const MyRouterNode: FC<NodeProps> = ({ data, isConnectable }) => {
  const fetchInterfacesOfDevice = async () => {
    const response = await fetch(
      `${API_ROUTE_BASE}interface_of_devices/getInterfacesOfDevice/${data.id}`,
      {
        method: 'GET',
      }
    );
    const json = await response.json();

    return dataSchemaInterface.parse(json);
  };

  const {
    isLoading: isLoadingInterfaces,
    error: errorInterfaces,
    data: dataInterfaces,
  } = useQuery({
    queryKey: ['interfaces', data.id],
    queryFn: fetchInterfacesOfDevice,
  });

  if (errorInterfaces) {
    console.error(errorInterfaces.message);
    return null;
  }

  return (
    <div className="my-topology my-topology--router">
      {/* {dataInterfaces && dataInterfaces.map((element) => (
        <Handle
          key={element.interface_id}
          type="target"
          position={Position.Top}
          id={element.interface_id.toString()}
          isConnectable={isConnectable}
        />
      ))} */}

      <Handle
        type="target"
        position={Position.Top}
        id="a"
        isConnectable={isConnectable}
      />

      <div>{data.label}</div>

      <Handle
        type="source"
        position={Position.Bottom}
        id="b"
        isConnectable={isConnectable}
      />
    </div>
  );
};

export default MyRouterNode;
