import {z} from 'zod';

export const PaginationDtoSchema = z.object({
  page: z.number().int(),
  itemsPerPage: z.number().int(),
  lastPage: z.number().int(),
  totalItems: z.number().int(),
});

export type PaginationDto = z.infer<typeof PaginationDtoSchema>;
