import { z } from 'zod';
import { authUserSchema } from './auth-user.model';
import { athleteSchema } from '../../athlete/model/athlete.model';

export const authSessionSchema = z.object({
  user: authUserSchema,
  athlete: athleteSchema,
});

export type AuthSession = z.infer<typeof authSessionSchema>;


export const authLoginResponseSchema = z.object({
  token: z.string().min(1),
  session: authSessionSchema,
});

export type AuthLoginResponse = z.infer<typeof authLoginResponseSchema>;


export const loginRequestSchema = z.object({
  email: z.email(),
  password: z.string().min(1),
});

export type LoginRequest = z.infer<typeof loginRequestSchema>;

export const apiErrorResponseSchema = z.object({
  error: z.object({
    code: z.string(),
    message: z.string(),
  }),
});

export type ApiErrorResponse = z.infer<typeof apiErrorResponseSchema>;
