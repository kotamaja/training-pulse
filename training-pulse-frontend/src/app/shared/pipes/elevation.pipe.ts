// src/app/shared/pipes/elevation.pipe.ts

import {Pipe, PipeTransform} from '@angular/core';

@Pipe({
  name: 'tpElevation',
})
export class ElevationPipe implements PipeTransform {
  transform(elevationGainM: number | null | undefined): string {
    if (elevationGainM === null || elevationGainM === undefined) {
      return '—';
    }

    return `${Math.round(elevationGainM)} m D+`;
  }
}
