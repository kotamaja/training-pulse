import {AfterViewInit, Component, DestroyRef, effect, ElementRef, inject, input, viewChild,} from '@angular/core';

import * as L from 'leaflet';

import {TrainingActivityRouteGeoJsonDto} from '../../model/training-activity-route.model';

@Component({
  selector: 'tp-activity-route-map',
  imports: [],
  templateUrl: './activity-route-map.component.html',
  styleUrl: './activity-route-map.component.scss',
})
export class ActivityRouteMapComponent implements AfterViewInit {
  readonly route = input.required<TrainingActivityRouteGeoJsonDto>();

  private readonly destroyRef = inject(DestroyRef);

  private readonly mapContainer =
    viewChild.required<ElementRef<HTMLDivElement>>('mapContainer');

  private map: L.Map | null = null;
  private routeLayer: L.GeoJSON | null = null;
  private viewInitialized = false;

  constructor() {
    effect(() => {
      const route = this.route();

      if (!this.viewInitialized || !this.map) {
        return;
      }

      this.renderRoute(route);
    });
  }

  ngAfterViewInit(): void {
    this.viewInitialized = true;

    this.map = L.map(this.mapContainer().nativeElement, {
      zoomControl: true,
      attributionControl: true,
    });

    L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
      maxZoom: 19,
      attribution: '&copy; OpenStreetMap contributors',
    }).addTo(this.map);

    this.renderRoute(this.route());

    this.destroyRef.onDestroy(() => {
      this.map?.remove();
      this.map = null;
      this.routeLayer = null;
    });
  }

  private renderRoute(route: TrainingActivityRouteGeoJsonDto): void {
    if (!this.map) {
      return;
    }

    if (this.routeLayer) {
      this.routeLayer.removeFrom(this.map);
    }

    this.routeLayer = L.geoJSON(route, {
      style: {
        weight: 4,
        opacity: 0.9,
      },
    }).addTo(this.map);

    const bounds = this.routeLayer.getBounds();

    if (bounds.isValid()) {
      this.map.fitBounds(bounds, {
        padding: [24, 24],
      });
    }
  }
}
