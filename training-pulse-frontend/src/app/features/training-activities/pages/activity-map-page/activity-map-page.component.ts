import {Component, computed, inject} from '@angular/core';
import {ActivatedRoute, RouterLink} from '@angular/router';

import {MatIconModule} from '@angular/material/icon';
import {MatProgressSpinnerModule} from '@angular/material/progress-spinner';

import {ActivityRouteMapComponent} from '../../components/activity-route-map/activity-route-map.component';
import {TrainingActivityResourceService} from '../../services/training-activity-resource.service';

@Component({
  selector: 'tp-activity-map-page',
  imports: [
    RouterLink,
    MatIconModule,
    MatProgressSpinnerModule,
    ActivityRouteMapComponent,
  ],
  templateUrl: './activity-map-page.component.html',
  styleUrl: './activity-map-page.component.scss',
})
export class ActivityMapPageComponent {
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
}
