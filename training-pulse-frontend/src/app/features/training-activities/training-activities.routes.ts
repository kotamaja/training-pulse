import {Routes} from '@angular/router';

export const TRAINING_ACTIVITIES_ROUTES: Routes = [
  {
    path: '',
    loadComponent: () =>
      import('./pages/activity-list-page/activity-list-page.component')
        .then(m => m.ActivityListPageComponent),
  },
  {
    path: ':id',
    loadComponent: () =>
      import('./pages/activity-detail-page/activity-detail-page.component')
        .then(m => m.ActivityDetailPageComponent),
  },
  {
    path: ':id/map',
    loadComponent: () =>
      import('./pages/activity-map-page/activity-map-page.component')
        .then((m) => m.ActivityMapPageComponent),
  },
];
