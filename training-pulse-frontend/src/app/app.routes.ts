import {Routes} from '@angular/router';

export const routes: Routes = [
  {
    path: '',
    redirectTo: 'activities',
    pathMatch: 'full',
  },
  {
    path: 'login',
    loadComponent: () =>
      import('./features/auth/pages/login-page/login-page.component')
        .then((m) => m.LoginPageComponent),
  },
  {
    path: 'activities',
    loadChildren: () =>
      import('./features/training-activities/training-activities.routes')
        .then((m) => m.TRAINING_ACTIVITIES_ROUTES),
  },
  {
    path: 'settings',
    loadComponent: () =>
      import('./features/settings/pages/settings-page/settings-page.component')
        .then((m) => m.SettingsPageComponent),
  },
  {
    path: '**',
    redirectTo: 'activities',
  },
];
