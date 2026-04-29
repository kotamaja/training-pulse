// src/app/shared/pipes/distance.pipe.ts

import {Pipe, PipeTransform} from '@angular/core';

@Pipe({
  name: 'tpDistance',
})
export class DistancePipe implements PipeTransform {
  transform(distanceM: number | null | undefined): string {
    if (distanceM === null || distanceM === undefined) {
      return '—';
    }

    return `${(distanceM / 1000).toFixed(1)} km`;
  }
}
