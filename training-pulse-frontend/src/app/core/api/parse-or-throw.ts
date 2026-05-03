import { z, ZodError, ZodType } from 'zod';

export function parseOrThrow<T>(schema: ZodType<T>, data: unknown): T {
  const result = schema.safeParse(data);

  if (result.success) {
    return result.data;
  }

  throw new ApiResponseParseError(result.error);
}

export class ApiResponseParseError extends Error {
  constructor(
    public readonly zodError: ZodError,
  ) {
    super('API response does not match expected schema.');
    this.name = 'ApiResponseParseError';
  }
}
