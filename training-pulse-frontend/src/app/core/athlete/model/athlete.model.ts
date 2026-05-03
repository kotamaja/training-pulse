import { z } from 'zod';

export const athleteSchema = z.object({
  id: z.string(),
  displayName: z.string(),
  birthYear: z.number().nullable(),
  heightCm: z.number().nullable(),
  weightKg: z.number().nullable(),
  restingHeartRate: z.number().nullable(),
  maxHeartRate: z.number().nullable(),
  ftpWatts: z.number().nullable(),
  createdAt: z.string(),
  updatedAt: z.string().nullable(),
});

export type Athlete = z.infer<typeof athleteSchema>;
