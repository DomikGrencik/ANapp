import { z } from 'zod';

export const dataSchemaDevices = z.array(
  z.object({
    id: z.number().int(),
    name: z.string(),
    type: z.string(),
    device_id: z.number().int(),
  })
);

export const dataSchemaInterface = z.array(
  z.object({
    interface_id: z.number().int(),
    name: z.string(),
    IP_address: z.string().nullable(),
    connector: z.string(),
    AN: z.string().nullable(),
    speed: z.string(),
    interface_id2: z.number().int().nullable(),
    id: z.number().int(),
    type: z.string(),
  })
);

export const dataSchemaConnections = z.array(
  z.object({
    connection_id: z.number().int(),
    interface_id1: z.number().int(),
    interface_id2: z.number().int(),
    device_id1: z.number().int(),
    device_id2: z.number().int(),
    name1: z.string(),
    name2: z.string(),
  })
);
