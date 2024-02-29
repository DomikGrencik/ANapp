import { FC } from 'react';
import { Handle, NodeProps, Position } from 'reactflow';

const MyRouterNode: FC<NodeProps> = ({ data, isConnectable }) => {
  return (
    <div className="my-topology my-topology--router">
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
