import { FC } from 'react';
import { Handle, NodeProps, Position } from 'reactflow';

import switchL2Icon from '../../../../assets/switch_L2_v3.svg';

const MyAccessSwitchNode: FC<NodeProps> = ({ data, isConnectable }) => {
  return (
    <div className="node node--switch">
      <Handle
        type="target"
        position={Position.Top}
        id="a"
        onConnect={(params) => console.log('handle onConnect', params)}
        isConnectable={isConnectable}
        className='handle'
      />

      <div className='label'>{data.label}</div>
      <img src={switchL2Icon} alt='switch' className='icon'/>

      <Handle
        type="source"
        position={Position.Bottom}
        id="b"
        onConnect={(params) => console.log('handle onConnect', params)}
        isConnectable={isConnectable}
        className='handle'
      />
    </div>
  );
};

export default MyAccessSwitchNode;
