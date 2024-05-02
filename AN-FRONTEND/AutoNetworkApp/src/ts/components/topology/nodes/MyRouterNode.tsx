import { FC } from 'react';
import { Handle, NodeProps, Position } from 'reactflow';

const MyRouterNode: FC<NodeProps> = ({ data, isConnectable }) => {

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
