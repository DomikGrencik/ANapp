import { FC, useCallback, useState } from 'react';
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

import {
  dataSchemaConnections,
  dataSchemaDevices,
} from '../../types/data-types';
import MyButton from '../MyButton';
import MyModal from '../MyModal';

import MyAccessSwitchNode from './nodes/MyAccessSwitchNode';
import MyEDNode from './nodes/MyEDNode';
import MyRouterNode from './nodes/MyRouterNode';

interface TopologyProps {
  dataDevices: z.infer<typeof dataSchemaDevices>;
  dataConnections: z.infer<typeof dataSchemaConnections>;
}

const nodeTypes = {
  router: MyRouterNode,
  accessSwitch: MyAccessSwitchNode,
  ED: MyEDNode,
};

const MyTopology: FC<TopologyProps> = ({ dataDevices, dataConnections }) => {
  const [nodes, setNodes, onNodesChange] = useNodesState([]);
  const [edges, setEdges, onEdgesChange] = useEdgesState([]);

  const [isToggledNodes, setIsToggledNodes] = useState(true);
  const [isToggledEdges, setIsToggledEdges] = useState(true);

  const [open, setOpen] = useState(false);
  const [idDevice, setIdDevice] = useState(0);

  const nodesData: Node<unknown, string | undefined>[] = dataDevices.map(
    (item, index) => {
      switch (item.type) {
        case 'router':
          console.log('router');
          break;

        default:
          break;
      }

      return {
        id: item.id.toString(),
        type: item.type,
        position: { x: 0, y: 100 * index },
        data: { label: item.name, id: item.id },
      };
    }
  );

  const edgesData: Edge<string | undefined>[] = dataConnections.map((item) => {
    return {
      id: `${item.interface_id1.toString()}-${item.interface_id2.toString()}`,
      source: item.device_id1.toString(),
      target: item.device_id2.toString(),
      sourceHandle: item.interface_id1.toString(),
      targetHandle: item.interface_id2.toString(),
    };
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
