import { z } from 'zod';

export const authUserSchema = z.object({
  id: z.string(),
  email: z.email(),
  username: z.string().nullable(),
  roles: z.array(z.string()),
  enabled: z.boolean(),
  createdAt: z.string(),
  updatedAt: z.string().nullable(),
});

export type AuthUser = z.infer<typeof authUserSchema>;
