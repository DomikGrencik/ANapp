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

import { dataSchemaDevices, dataSchemaInterface } from '../pages/Database';

import MyButton from './MyButton';
import MyModal from './MyModal';

interface TopologyProps {
  dataDevices: z.infer<typeof dataSchemaDevices>;
  dataInterfaces: z.infer<typeof dataSchemaInterface>;
}

const MyTopology: FC<TopologyProps> = ({ dataDevices, dataInterfaces }) => {
  let posY = 0;
  let sourceInt: number;
  let targetInt: number;
  let sourceDev: number;
  let targetDev: number;

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

  const edgesData:
    | SetStateAction<Edge<string | undefined>[]>
    | {
        id: string;
        source: string;
        target: string;
      }[] = [];

  dataDevices.forEach((element) => {
    nodesData.push({
      id: element.id.toString(),
      position: { x: 0, y: posY },
      data: { label: element.name },
    });
    posY += 100;
  });


  const uniqueEdges = new Set<string>();

  for (let i = 0; i < dataInterfaces.length; i++) {
    if (dataInterfaces[i].interface_id2 !== null) {
      sourceInt = dataInterfaces[i].interface_id;
      targetInt = dataInterfaces[i].interface_id2 ?? 0;
      sourceDev = dataInterfaces[i].id;
      targetDev = dataInterfaces[targetInt - 1].id;

      const edgeId = `${sourceInt}-${targetInt}`;
      const reverseEdgeId = `${targetInt}-${sourceInt}`;

      if (!uniqueEdges.has(edgeId) && !uniqueEdges.has(reverseEdgeId)) {
        edgesData.push({
          id: edgeId,
          source: sourceDev.toString(),
          target: targetDev.toString(),
        });

        uniqueEdges.add(edgeId);
      }
    }
  }

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
            setOpen(true);
            setIdDevice(parseInt(node.id));
          }}
        >
          <Controls />
          <MiniMap />
          <Background variant="dots" gap={12} size={1} />
        </ReactFlow>

        <MyButton onClick={() => setNodes(nodesData)}>nodes</MyButton>
        <MyButton onClick={() => setEdges(edgesData)}>edges</MyButton>
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
