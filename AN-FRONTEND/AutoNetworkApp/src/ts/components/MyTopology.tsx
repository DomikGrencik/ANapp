import { FC, SetStateAction, useCallback } from 'react';
import ReactFlow, {
  addEdge,
  Background,
  Connection,
  Controls,
  Edge,
  MiniMap,
  Node,
  useEdgesState,
  useNodesState,
} from 'reactflow';
import { z } from 'zod';

import { dataSchemaDevices } from '../pages/Database';

import MyButton from './MyButton';

interface TopologyProps {
  data: z.infer<typeof dataSchemaDevices>;
}

const MyTopology: FC<TopologyProps> = ({ data }) => {
  let posY = 0;

  const [nodes, setNodes, onNodesChange] = useNodesState([]);
  const [edges, setEdges, onEdgesChange] = useEdgesState([]);

  const nodesData:
    | SetStateAction<Node<unknown, string | undefined>[]>
    | {
        id: string;
        position: { x: number; y: number };
        data: { label: string };
      }[] = [];

  data.forEach((element) => {
    nodesData.push({
      id: element.id.toString(),
      position: { x: 0, y: posY },
      data: { label: element.name },
    });
    posY += 100;
  });
  // Add your component logic here

  const onConnect = useCallback(
    (params: Edge | Connection) => setEdges((eds) => addEdge(params, eds)),
    [setEdges]
  );

  return (
    <div
      style={{
        height: '100%',
      }}
    >
      <ReactFlow
        nodes={nodes}
        edges={edges}
        onNodesChange={onNodesChange}
        onEdgesChange={onEdgesChange}
        onConnect={onConnect}
        onNodeClick={(event, node) => console.log(node.id)}
      >
        <Controls />
        <MiniMap />
        <Background variant="dots" gap={12} size={1} />
      </ReactFlow>

      <MyButton onClick={() => setNodes(nodesData)}>nodes</MyButton>
    </div>
  );
};

export default MyTopology;
