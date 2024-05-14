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
    connector: z.string(),
    AN: z.string().nullable(),
    speed: z.string(),
    direction: z.string().nullable(),
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

export const dataSchemaDeviceDatabase = z.array(
  z.object({
    device_id: z.number().int(),
    manufacturer: z.string(),
    model: z.string(),
    type: z.string(),
    r_throughput: z.number().int().nullish(),
    r_branch: z.string().nullish(),
    s_forwarding_rate: z.number().nullish(),
    s_switching_capacity: z.number().nullish(),
    s_vlans: z.string().nullish(),
    s_L3: z.string().nullish(),
    price: z.number().int().nullish(),
  })
);
