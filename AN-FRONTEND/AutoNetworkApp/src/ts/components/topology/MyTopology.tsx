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

import MyStraightEdge from './edges/MyStraightEdge';
import MyAccessSwitchNode from './nodes/MyAccessSwitchNode';
import MyDistributionSwitchNode from './nodes/MyDistributionSwitchNode';
import MyEDNode from './nodes/MyEDNode';
import MyRouterNode from './nodes/MyRouterNode';

interface TopologyProps {
  dataDevices: z.infer<typeof dataSchemaDevices>;
  dataConnections: z.infer<typeof dataSchemaConnections>;
}

const nodeTypes = {
  router: MyRouterNode,
  accessSwitch: MyAccessSwitchNode,
  distributionSwitch: MyDistributionSwitchNode,
  ED: MyEDNode,
};

const edgeTypes = {
  straight: MyStraightEdge,
};

const MyTopology: FC<TopologyProps> = ({ dataDevices, dataConnections }) => {
  const [nodes, setNodes, onNodesChange] = useNodesState([]);
  const [edges, setEdges, onEdgesChange] = useEdgesState([]);

  const [isToggledNodes, setIsToggledNodes] = useState(true);
  const [isToggledEdges, setIsToggledEdges] = useState(true);

  const [open, setOpen] = useState(false);
  const [idDevice, setIdDevice] = useState(0);

  const filteredConnections: z.infer<typeof dataSchemaConnections> = [];

  const deviceIds = new Set();
  const uniqueDeviceIds = new Set();

  dataConnections.forEach((item, index) => {
    const prevItem = index > 0 ? dataConnections[index - 1] : null;

    if (item.device_id1 === (prevItem?.device_id1 ?? item.device_id1)) {
      if (
        !deviceIds.has(item.device_id1) ||
        filteredConnections.filter((i) => i.device_id1 === item.device_id1)
          .length < 10
      ) {
        filteredConnections.push(item);
        deviceIds.add(item.device_id1);

        uniqueDeviceIds.add(item.device_id1);
        uniqueDeviceIds.add(item.device_id2);
      }
    } else {
      filteredConnections.push(item);
      deviceIds.add(item.device_id1);

      uniqueDeviceIds.add(item.device_id1);
      uniqueDeviceIds.add(item.device_id2);
    }
  });

  // Filtrácia dataDevices na základe unique device_id
  const filteredDevices = dataDevices.filter((device) =>
    uniqueDeviceIds.has(device.id)
  );

  //console.log(filteredDevices);

  const nodesData: Node<unknown, string | undefined>[] = filteredDevices.map(
    (item, index) => {
      let position = { x: 0, y: 100 * index };
      switch (item.type) {
        case 'router':
          position = { x: 100, y: 100 * index };
          break;

        default:
          break;
      }

      return {
        id: item.id.toString(),
        type: item.type,
        position,
        data: { label: item.name, id: item.id },
      };
    }
  );

  const edgesData: Edge<string | undefined>[] = filteredConnections.map(
    (item) => {
      return {
        id: `${item.interface_id1.toString()}-${item.interface_id2.toString()}`,
        source: item.device_id1.toString(),
        target: item.device_id2.toString(),
        sourceHandle: item.interface_id1.toString(),
        targetHandle: item.interface_id2.toString(),
        type: 'straight',
      };
    }
  );

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
          edgeTypes={edgeTypes}
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
