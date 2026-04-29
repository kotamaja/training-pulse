import {z} from 'zod';

export const GeoJsonPositionSchema = z.tuple([
  z.number(), // longitude
  z.number(), // latitude
]);

export const GeoJsonLineStringGeometrySchema = z.object({
  type: z.literal('LineString'),
  coordinates: z.array(GeoJsonPositionSchema),
});

export const TrainingActivityRouteGeoJsonDtoSchema = z.object({
  type: z.literal('Feature'),
  geometry: GeoJsonLineStringGeometrySchema,
  properties: z.record(z.string(), z.unknown()).optional(),
});

export type TrainingActivityRouteGeoJsonDto = z.infer<
  typeof TrainingActivityRouteGeoJsonDtoSchema
>;
