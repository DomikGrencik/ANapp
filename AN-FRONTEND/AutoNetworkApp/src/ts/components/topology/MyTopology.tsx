import { FC, SetStateAction, useCallback, useState } from 'react';
import ReactFlow, {
  addEdge,
  Background,
  BackgroundVariant,
  Connection,
  Controls,
  Edge,
  MiniMap,
  Node,
  useEdgesState,
  useNodesState,
} from 'reactflow';
import { z } from 'zod';

import { dataSchemaConnections, dataSchemaDevices } from '../../pages/Database';
import MyButton from '../MyButton';
import MyModal from '../MyModal';

import MyRouterNode from './MyRouterNode';

interface TopologyProps {
  dataDevices: z.infer<typeof dataSchemaDevices>;
  dataConnections: z.infer<typeof dataSchemaConnections>;
}

const nodeTypes = { routerNode: MyRouterNode };

const MyTopology: FC<TopologyProps> = ({ dataDevices, dataConnections }) => {
  let posY = 0;

  const [nodes, setNodes, onNodesChange] = useNodesState([]);
  const [edges, setEdges, onEdgesChange] = useEdgesState([]);

  const [isToggledNodes, setIsToggledNodes] = useState(true);
  const [isToggledEdges, setIsToggledEdges] = useState(true);

  const [open, setOpen] = useState(false);
  const [idDevice, setIdDevice] = useState(0);

  const nodesData:
    | SetStateAction<Node<unknown, string | undefined>[]>
    | {
        id: string;
        type: string;
        position: { x: number; y: number };
        data: { label: string; id: number };
      }[] = [];

  const edgesData:
    | SetStateAction<Edge<string | undefined>[]>
    | {
        id: string;
        source: string;
        target: string;
        sourceHandle: string;
        targetHandle: string;
      }[] = [];

  dataDevices.forEach((element) => {
    nodesData.push({
      id: element.id.toString(),
      type: 'routerNode',
      position: { x: 0, y: posY },
      data: { label: element.name, id: element.id },
    });
    posY += 100;
  });

  dataConnections.forEach((element) => {
    edgesData.push({
      id: `${element.interface_id1.toString()}-${element.interface_id2.toString()}`,
      source: element.device_id1.toString(),
      target: element.device_id2.toString(),
      sourceHandle: element.interface_id1.toString(),
      targetHandle: element.interface_id2.toString(),
    });
  });

  const toggleNodes = () => {
    if (isToggledNodes) {
      setNodes(nodesData);
    } else setNodes([]);

    setIsToggledNodes((prevState) => !prevState);
  };

  const toggleEdges = () => {
    if (isToggledEdges) {
      setEdges(edgesData);
    } else setEdges([]);

    setIsToggledEdges((prevState) => !prevState);
  };

  const onConnect = useCallback(
    (params: Edge | Connection) => setEdges((eds) => addEdge(params, eds)),
    [setEdges]
  );

  return (
    <>
      <div className="my-topology">
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
          nodeTypes={nodeTypes}
        >
          <Controls />
          <MiniMap />
          <Background variant={BackgroundVariant.Dots} gap={12} size={1} />
        </ReactFlow>
        <div>
          <MyButton onClick={toggleNodes}>
            nodes {isToggledNodes ? 'ON' : 'OFF'}
          </MyButton>
          <MyButton onClick={toggleEdges}>
            edges {isToggledEdges ? 'ON' : 'OFF'}
          </MyButton>
          <MyButton onClick={() => console.log(edges)}>console edges</MyButton>
        </div>
      </div>

      {open ? (
        <MyModal
          isOpen={open}
          onClose={() => setOpen(false)}
          hasTable
          idDevice={idDevice}
        >
          {/* Ja som modal */}
        </MyModal>
      ) : null}
    </>
  );
};

export default MyTopology;
