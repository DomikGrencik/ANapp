import { FC } from 'react';
import { Handle, NodeProps, Position } from 'reactflow';

import routerIcon from '../../../../assets/router_v1.svg';

const MyRouterNode: FC<NodeProps> = ({ data, isConnectable }) => {
  return (
    <div className="node node--router">
      <Handle
        type="target"
        position={Position.Top}
        id="a"
        onConnect={(params) => console.log('handle onConnect', params)}
        isConnectable={isConnectable}
        className="handle"
      />

      <div className="label">{data.label}</div>
      <img src={routerIcon} alt="router" className="icon icon--router" />
      {/* <div className='icon'>
        <div className='icon--router'/>
        <div className='icon--router'/>
      </div> */}

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

export default MyRouterNode;
