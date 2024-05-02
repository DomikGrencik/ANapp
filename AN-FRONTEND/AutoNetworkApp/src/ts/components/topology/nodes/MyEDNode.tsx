import { FC } from 'react';
import { Handle, NodeProps, Position } from 'reactflow';

const MyEDNode: FC<NodeProps> = ({ data, isConnectable }) => {
  return (
    <div className="node node--ed">
      <Handle
        type="target"
        position={Position.Top}
        id="a"
        onConnect={(params) => console.log('handle onConnect', params)}
        isConnectable={isConnectable}
      />

      <div>{data.label}</div>
    </div>
  );
};

export default MyEDNode;
