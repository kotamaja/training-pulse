import {Component, input} from '@angular/core';
import {MatCardModule} from '@angular/material/card';
import {MatIconModule} from '@angular/material/icon';

import {TrainingActivitySummaryDto} from '../../model/training-activity.model';
import {DatePipe} from '@angular/common';
import {RouterLink} from '@angular/router';
import {sportTypeLabel} from '../../model/sport-type-label';
import {DistancePipe} from '../../../../shared/pipes/distance.pipe';
import {DurationPipe} from '../../../../shared/pipes/duration.pipe';
import {ElevationPipe} from '../../../../shared/pipes/elevation.pipe';
import {HeartratePipe} from '../../../../shared/pipes/heartrate.pipe';
import {PowerPipe} from '../../../../shared/pipes/power.pipe';

@Component({
  selector: 'tp-activity-card',
  imports: [
    MatCardModule,
    MatIconModule,
    RouterLink,
    DatePipe,
    DistancePipe,
    DurationPipe,
    ElevationPipe,
    HeartratePipe,
    PowerPipe,
  ],
  templateUrl: './activity-card.component.html',
  styleUrl: './activity-card.component.scss',
})
export class ActivityCardComponent {
  readonly activity = input.required<TrainingActivitySummaryDto>();

  readonly sportTypeLabel = sportTypeLabel;


  getDisplayDate(activity: TrainingActivitySummaryDto): string {
    return activity.startedAtLocal ?? activity.startedAt;
  }
}
