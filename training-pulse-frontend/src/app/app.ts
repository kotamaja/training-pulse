import { Component, inject, OnInit } from '@angular/core';
import { AppShellComponent } from './core/layout/app-shell/app-shell.component';
import {AuthSessionService} from './core/auth/services/auth-session.service';

@Component({
  selector: 'app-root',
  imports: [AppShellComponent],
  template: `
    @if (!authSession.initialized()) {
      <div class="app-loading">
        Chargement...
      </div>
    } @else {
      <tp-app-shell />
    }
  `,
})
export class AppComponent implements OnInit {
  protected readonly authSession = inject(AuthSessionService);

  ngOnInit(): void {
    void this.authSession.ensureInitialized();
  }
}
