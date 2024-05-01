import { FC } from 'react';
import { Handle, NodeProps, Position } from 'reactflow';

const MyRouterNode: FC<NodeProps> = ({ data, isConnectable }) => {
  /*  const fetchInterfacesOfDevice = async () => {
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
  } */

  return (
    <div className="node node--router">
      {/* {dataInterfaces &&
        dataInterfaces.map((element, index) => (
          <Handle
            key={element.interface_id}
            type="target"
            position={Position.Top}
            id={element.interface_id.toString()}
            isConnectable={isConnectable}
            style={{ left: 10 * (index + 1) }}
          />
        ))} */}

      <Handle
        type="target"
        position={Position.Top}
        id="a"
        onConnect={(params) => console.log('handle onConnect', params)}
        isConnectable={isConnectable}
      />

      <div>{data.label}</div>

      <Handle
        type="source"
        position={Position.Bottom}
        id="b"
        onConnect={(params) => console.log('handle onConnect', params)}
        isConnectable={isConnectable}
      />
    </div>
  );
};

export default MyRouterNode;
