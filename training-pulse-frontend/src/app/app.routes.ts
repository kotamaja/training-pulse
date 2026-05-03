import {Routes} from '@angular/router';
import {authGuard} from './core/auth/guards/auth.guard';
import {guestGuard} from './core/auth/guards/guest.guard';

export const routes: Routes = [
  {
    path: '',
    redirectTo: 'activities',
    pathMatch: 'full',
  },
  {
    path: 'login',
    canActivate: [guestGuard],
    loadComponent: () =>
      import('./features/auth/pages/login-page/login-page.component')
        .then((m) => m.LoginPageComponent),
  },
  {
    path: 'activities',
    canActivate: [authGuard],
    loadChildren: () =>
      import('./features/training-activities/training-activities.routes')
        .then((m) => m.TRAINING_ACTIVITIES_ROUTES),
  },
  {
    path: 'settings',
    canActivate: [authGuard],
    loadComponent: () =>
      import('./features/settings/pages/settings-page/settings-page.component')
        .then((m) => m.SettingsPageComponent),
  },
  {
    path: '**',
    redirectTo: 'activities',
  },
];
