// src/app/shared/pipes/duration.pipe.ts

import {Pipe, PipeTransform} from '@angular/core';

@Pipe({
  name: 'tpDuration',
})
export class DurationPipe implements PipeTransform {
  transform(seconds: number | null | undefined): string {
    if (seconds === null || seconds === undefined) {
      return '—';
    }

    const hours = Math.floor(seconds / 3600);
    const minutes = Math.floor((seconds % 3600) / 60);

    if (hours > 0) {
      return `${hours}h${minutes.toString().padStart(2, '0')}`;
    }

    return `${minutes} min`;
  }
}
