import { FC, SetStateAction, useCallback, useState } from 'react';
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
import MyModal from './MyModal';

interface TopologyProps {
  data: z.infer<typeof dataSchemaDevices>;
}

const MyTopology: FC<TopologyProps> = ({ data }) => {
  let posY = 0;

  const [nodes, setNodes, onNodesChange] = useNodesState([]);
  const [edges, setEdges, onEdgesChange] = useEdgesState([]);

  const [open, setOpen] = useState(false);
  const [idDevice, setIdDevice] = useState(0);

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

  const onConnect = useCallback(
    (params: Edge | Connection) => setEdges((eds) => addEdge(params, eds)),
    [setEdges]
  );

  return (
    <>
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
          onNodeClick={(_event, node) => {
            console.log(node.id);
            setOpen(true);
            setIdDevice(parseInt(node.id));
          }}
        >
          <Controls />
          <MiniMap />
          <Background variant="dots" gap={12} size={1} />
        </ReactFlow>

        <MyButton onClick={() => setNodes(nodesData)}>nodes</MyButton>
      </div>
      {open ? (
        <div>
          <MyModal
            isOpen={open}
            onClose={() => setOpen(false)}
            hasTable
            idDevice={idDevice}
          >
            Ja som modal
          </MyModal>
        </div>
      ) : null}
    </>
  );
};

export default MyTopology;
