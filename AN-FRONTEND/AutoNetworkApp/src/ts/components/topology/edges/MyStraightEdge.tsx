import { FC } from 'react';
import { BaseEdge, EdgeProps, getStraightPath } from 'reactflow';

const MyStraightEdge: FC<EdgeProps> = ({
  id,
  sourceX,
  sourceY,
  targetX,
  targetY,
}) => {
  const [edgePath] = getStraightPath({
    sourceX,
    sourceY,
    targetX,
    targetY,
  });

  return (
    <BaseEdge
      id={id}
      path={edgePath}
      //style={{ stroke: '#363636', strokeWidth: '1px'}}
    />
  );
};

export default MyStraightEdge;
