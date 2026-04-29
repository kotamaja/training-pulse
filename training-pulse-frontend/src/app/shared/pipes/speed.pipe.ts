import {Pipe, PipeTransform} from '@angular/core';

@Pipe({
  name: 'tpSpeed',
})
export class SpeedPipe implements PipeTransform {
  transform(speedMps: number | null | undefined): string {
    if (speedMps === null || speedMps === undefined) {
      return '—';
    }

    return `${(speedMps * 3.6).toFixed(1)} km/h`;
  }
}
