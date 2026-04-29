import {DatePipe, DecimalPipe} from '@angular/common';
import {Component, computed, inject} from '@angular/core';
import {ActivatedRoute, RouterLink} from '@angular/router';

import {MatButtonModule} from '@angular/material/button';
import {MatIconModule} from '@angular/material/icon';
import {MatProgressSpinnerModule} from '@angular/material/progress-spinner';

import {DistancePipe} from '../../../../shared/pipes/distance.pipe';
import {DurationPipe} from '../../../../shared/pipes/duration.pipe';
import {ElevationPipe} from '../../../../shared/pipes/elevation.pipe';
import {HeartratePipe} from '../../../../shared/pipes/heartrate.pipe';
import {PowerPipe} from '../../../../shared/pipes/power.pipe';

import {sportTypeLabel} from '../../model/sport-type-label';
import {TrainingActivityDetailDto} from '../../model/training-activity.model';
import {TrainingActivityResourceService} from '../../services/training-activity-resource.service';
import {SpeedPipe} from '../../../../shared/pipes/speed.pipe';
import {ActivityRouteMapComponent} from '../../components/activity-route-map/activity-route-map.component';

@Component({
  selector: 'tp-activity-detail-page',
  imports: [
    RouterLink,
    DatePipe,
    MatButtonModule,
    MatIconModule,
    MatProgressSpinnerModule,
    DistancePipe,
    DurationPipe,
    ElevationPipe,
    HeartratePipe,
    PowerPipe,
    DecimalPipe,
    SpeedPipe,
    ActivityRouteMapComponent,
  ],
  templateUrl: './activity-detail-page.component.html',
  styleUrl: './activity-detail-page.component.scss',
})
export class ActivityDetailPageComponent {
  private readonly route = inject(ActivatedRoute);
  private readonly trainingActivityResource = inject(TrainingActivityResourceService);

  readonly activityId = computed(() => this.route.snapshot.paramMap.get('id'));

  readonly activityResource = this.trainingActivityResource.detail(this.activityId);

  readonly routeActivityId = computed(() => {
    const activity = this.activityResource.value();

    if (!activity?.hasRoute) {
      return null;
    }

    return activity.id;
  });

  readonly routeResource = this.trainingActivityResource.route(this.routeActivityId);

  readonly sportTypeLabel = sportTypeLabel;

  getDisplayDate(activity: TrainingActivityDetailDto): string {
    return activity.startedAtLocal ?? activity.startedAt;
  }

  formatSource(source: string): string {
    switch (source) {
      case 'strava':
        return 'Strava';
      case 'manual':
        return 'Manuel';
      default:
        return source;
    }
  }
}
