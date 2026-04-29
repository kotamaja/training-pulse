import {httpResource} from '@angular/common/http';
import {Injectable, Signal} from '@angular/core';

import {
  TrainingActivityCollectionSchema,
  TrainingActivityListQueryParams
} from '../model/training-activity-collection.model';
import {TrainingActivityDetailDtoSchema} from '../model/training-activity.model';
import {TrainingActivityRouteGeoJsonDtoSchema} from '../model/training-activity-route.model';

@Injectable({providedIn: 'root'})
export class TrainingActivityResourceService {
  private readonly baseUrl = '/api/v1/training_activities';

  collection(params: Signal<TrainingActivityListQueryParams>) {
    return httpResource(
      () => ({
        url: this.baseUrl,
        params: this.toResourceParams(params()),
      }),
      {
        parse: (response) => TrainingActivityCollectionSchema.parse(response),
      },
    );
  }

  detail(id: Signal<string | null>) {
    return httpResource(
      () => {
        const activityId = id();

        if (!activityId) {
          return undefined;
        }

        return `${this.baseUrl}/${activityId}`;
      },
      {
        parse: (response) => TrainingActivityDetailDtoSchema.parse(response),
      },
    );
  }

  route(id: Signal<string | null>) {
    return httpResource(
      () => {
        const activityId = id();

        if (!activityId) {
          return undefined;
        }

        return `${this.baseUrl}/${activityId}/route`;
      },
      {
        parse: (response) => TrainingActivityRouteGeoJsonDtoSchema.parse(response),
      },
    );
  }

  private toResourceParams(params: TrainingActivityListQueryParams,): Record<string, string | number | boolean> {
    const result: Record<string, string | number | boolean> = {};

    Object.entries(params).forEach(([key, value]) => {
      if (value === undefined || value === null) {
        return;
      }

      result[key] = value;
    });

    return result;
  }
}
