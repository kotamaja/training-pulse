import {z} from 'zod';

export const SportTypeSchema = z.enum([
  'cycling',
  'road_cycling',
  'mountain_biking',
  'gravel_cycling',
  'indoor_cycling',

  'rowing',
  'indoor_rowing',

  'nordic_skiing',
  'nordic_skiing_classic',
  'nordic_skiing_skating',

  'running',
  'hiking',
  'walking',

  'strength_training',
  'mobility',

  'other',
]);


export type SportType = z.infer<typeof SportTypeSchema>;

export const ActivitySourceSchema = z.enum([
  'manual',
  'strava',
  'garmin',
  'polar',
  'file_import',
]);


export type ActivitySource = z.infer<typeof ActivitySourceSchema>;

export const TrainingActivitySummaryDtoSchema = z.object({
  id: z.ulid(),

  name: z.string(),
  sportType: SportTypeSchema,

  startedAt: z.string(),
  startedAtLocal: z.string().nullable(),
  timezone: z.string().nullable(),

  distanceM: z.number().nullable(),
  movingTimeS: z.number().int().nullable(),
  elevationGainM: z.number().nullable(),

  averageHeartrate: z.number().nullable(),
  averageWatts: z.number().nullable(),

  hasRoute: z.boolean(),
});

export type TrainingActivitySummaryDto = z.infer<
  typeof TrainingActivitySummaryDtoSchema
>;

export const TrainingActivityDetailDtoSchema = z.object({
  id: z.ulid(),

  name: z.string(),
  sportType: SportTypeSchema,
  source: ActivitySourceSchema,

  externalId: z.string(),

  startedAt: z.string(),
  startedAtLocal: z.string().nullable(),
  timezone: z.string().nullable(),

  distanceM: z.number().nullable(),
  movingTimeS: z.number().int().nullable(),
  elapsedTimeS: z.number().int().nullable(),

  elevationGainM: z.number().nullable(),

  averageSpeedMps: z.number().nullable(),
  maxSpeedMps: z.number().nullable(),

  averageHeartrate: z.number().nullable(),
  maxHeartrate: z.number().nullable(),

  averageWatts: z.number().nullable(),
  maxWatts: z.number().nullable(),

  calories: z.number().nullable(),

  hasRoute: z.boolean(),

  syncedAt: z.string().nullable(),
  createdAt: z.string(),
  updatedAt: z.string().nullable(),
});

export type TrainingActivityDetailDto = z.infer<
  typeof TrainingActivityDetailDtoSchema
>;
