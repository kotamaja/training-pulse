import {Component} from '@angular/core';
import {AppShellComponent} from './core/layout/app-shell/app-shell.component';

@Component({
  selector: 'app-root',
  imports: [AppShellComponent],
  template: '<tp-app-shell />',
})
export class AppComponent {
}
