// src/app/shared/pipes/power.pipe.ts

import {Pipe, PipeTransform} from '@angular/core';

@Pipe({
  name: 'tpPower',
})
export class PowerPipe implements PipeTransform {
  transform(watts: number | null | undefined): string {
    if (watts === null || watts === undefined) {
      return '—';
    }

    return `${Math.round(watts)} W`;
  }
}
