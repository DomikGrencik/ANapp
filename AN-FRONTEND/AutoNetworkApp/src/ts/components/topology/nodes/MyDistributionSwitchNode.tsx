import { FC } from 'react';
import { Handle, NodeProps, Position } from 'reactflow';

import switchL3Icon from '../../../../assets/switch_L3_v1.svg';

const MyDistributionSwitchNode: FC<NodeProps> = ({ data, isConnectable }) => {
  return (
    <div className="node node--switch">
      <Handle
        type="target"
        position={Position.Top}
        id="a"
        onConnect={(params) => console.log('handle onConnect', params)}
        isConnectable={isConnectable}
        className="handle"
      />

      <div className="label">{data.label}</div>
      <img src={switchL3Icon} alt="switch" className="icon" />

      <Handle
        type="source"
        position={Position.Bottom}
        id="b"
        onConnect={(params) => console.log('handle onConnect', params)}
        isConnectable={isConnectable}
        className="handle"
      />
    </div>
  );
};

export default MyDistributionSwitchNode;
