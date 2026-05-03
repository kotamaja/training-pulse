import { Component, inject } from '@angular/core';
import { Router } from '@angular/router';
import {AuthSessionService} from '../../../../core/auth/services/auth-session.service';

@Component({
  selector: 'tp-settings-page',
  standalone: true,
  templateUrl: './settings-page.component.html',
  styleUrl: './settings-page.component.scss',
})
export class SettingsPageComponent {
  private readonly router = inject(Router);
  protected readonly authSession = inject(AuthSessionService);

  protected async logout(): Promise<void> {
    await this.authSession.logout();
    await this.router.navigateByUrl('/login');
  }
}
