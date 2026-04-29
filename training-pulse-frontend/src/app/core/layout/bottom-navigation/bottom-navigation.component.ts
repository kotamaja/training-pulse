// src/app/core/layout/bottom-navigation/bottom-navigation.component.ts

import {Component} from '@angular/core';
import {RouterLink, RouterLinkActive} from '@angular/router';
import {MatIconModule} from '@angular/material/icon';

type BottomNavigationItem = {
  label: string;
  icon: string;
  route: string;
  exact: boolean;
};

@Component({
  selector: 'tp-bottom-navigation',
  imports: [
    RouterLink,
    RouterLinkActive,
    MatIconModule,
  ],
  templateUrl: './bottom-navigation.component.html',
  styleUrl: './bottom-navigation.component.scss',
})
export class BottomNavigationComponent {
  readonly items: BottomNavigationItem[] = [
    {
      label: 'Activités',
      icon: 'directions_bike',
      route: '/activities',
      exact: false,
    },
    {
      label: 'Réglages',
      icon: 'settings',
      route: '/settings',
      exact: true,
    },
  ];
}
