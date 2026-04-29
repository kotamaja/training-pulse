import {z} from 'zod';
import {SportType, TrainingActivitySummaryDtoSchema,} from './training-activity.model';
import {PaginationDtoSchema} from '../../../core/api/pagination.model';


export const TrainingActivityCollectionSchema = z.object({
  items: z.array(TrainingActivitySummaryDtoSchema),
  pagination: PaginationDtoSchema,
});

export type TrainingActivityCollection = z.infer<
  typeof TrainingActivityCollectionSchema
>;

export type TrainingActivityListQueryParams = {
  page?: number;
  itemsPerPage?: number;
  sportType?: SportType;
};
