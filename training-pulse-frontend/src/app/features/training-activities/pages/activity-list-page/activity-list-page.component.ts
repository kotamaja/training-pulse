import {Component, computed, inject, signal} from '@angular/core';
import {MatButtonModule} from '@angular/material/button';
import {MatProgressSpinnerModule} from '@angular/material/progress-spinner';

import {ActivityCardComponent} from '../../components/activity-card/activity-card.component';
import {TrainingActivityResourceService} from '../../services/training-activity-resource.service';
import {SportType} from '../../model/training-activity.model';
import {sportTypeLabel} from '../../model/sport-type-label';
import {MatFormField, MatLabel} from '@angular/material/input';
import {MatOption, MatSelect} from '@angular/material/select';

@Component({
  selector: 'tp-activity-list-page',
  imports: [
    MatButtonModule,
    MatProgressSpinnerModule,
    ActivityCardComponent,
    MatFormField,
    MatLabel,
    MatSelect,
    MatOption,
  ],
  templateUrl: './activity-list-page.component.html',
  styleUrl: './activity-list-page.component.scss',
})
export class ActivityListPageComponent {
  private readonly trainingActivityResource = inject(TrainingActivityResourceService);

  readonly page = signal(1);
  readonly itemsPerPage = signal(20);
  readonly sportType = signal<SportType | null>(null);

  readonly sportTypeLabel = sportTypeLabel;

  readonly sportTypes: SportType[] = [
    'cycling',
    'road_cycling',
    'mountain_biking',
    'gravel_cycling',
    'indoor_cycling',
    'rowing',
    'indoor_rowing',
    'nordic_skiing',
    'nordic_skiing_classic',
    'nordic_skiing_skating',
    'running',
    'walking',
    'hiking',
    'strength_training',
    'mobility',
    'other',
  ];

  readonly queryParams = computed(() => ({
    page: this.page(),
    itemsPerPage: this.itemsPerPage(),
    sportType: this.sportType() ?? undefined,
  }));

  readonly activitiesResource = this.trainingActivityResource.collection(this.queryParams);

  onSportTypeChange(sportType: SportType | null): void {
    this.sportType.set(sportType);
    this.page.set(1);
  }

  nextPage(): void {
    const collection = this.activitiesResource.value();

    if (!collection) {
      return;
    }

    if (collection.pagination.page >= collection.pagination.lastPage) {
      return;
    }

    this.page.update((page) => page + 1);
  }

  previousPage(): void {
    if (this.page() <= 1) {
      return;
    }

    this.page.update((page) => page - 1);
  }


}
