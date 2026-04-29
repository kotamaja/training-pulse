// src/app/shared/pipes/heartrate.pipe.ts

import {Pipe, PipeTransform} from '@angular/core';

@Pipe({
  name: 'tpHeartrate',
})
export class HeartratePipe implements PipeTransform {
  transform(heartrate: number | null | undefined): string {
    if (heartrate === null || heartrate === undefined) {
      return '—';
    }

    return `${Math.round(heartrate)} bpm`;
  }
}
