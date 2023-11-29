// eslint-disable-next-line @typescript-eslint/no-explicit-any
export type AnyFunction<P extends any[] = any[], T = any> = (...args: P) => T;

// eslint-disable-next-line @typescript-eslint/no-explicit-any
export type AnyAsyncFunction<P extends any[] = any[], T = any> = (
  ...args: P
) => Promise<T>;

export type FixedLengthArray<T, L extends number> = [T, ...T[]] & {
  length: L;
};

export type ValueOf<T> = T[keyof T];

export type LooseAutocomplete<T extends string> = T | Omit<string, T>;

export type EmptyObject = Record<string, never>;

export type UnknownObject = Record<string, unknown>;

// eslint-disable-next-line @typescript-eslint/no-explicit-any
export type AnyObject = Record<string, any>;
