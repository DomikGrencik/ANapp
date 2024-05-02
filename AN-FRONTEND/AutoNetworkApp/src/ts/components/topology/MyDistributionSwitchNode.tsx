import { FC } from 'react';
import { Handle, NodeProps, Position } from 'reactflow';

const MyDistributionSwitchNode: FC<NodeProps> = ({ data, isConnectable }) => {
  return (
    <div className="node node--switch">
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

export default MyDistributionSwitchNode;
