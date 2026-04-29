// src/app/features/training-activities/model/sport-type-label.ts

import {SportType} from './training-activity.model';

export function sportTypeLabel(sportType: SportType): string {
  switch (sportType) {
    case 'cycling':
      return 'Vélo';

    case 'road_cycling':
      return 'Vélo route';

    case 'mountain_biking':
      return 'VTT';

    case 'gravel_cycling':
      return 'Gravel';

    case 'indoor_cycling':
      return 'Home trainer';

    case 'rowing':
      return 'Aviron';

    case 'indoor_rowing':
      return 'Rameur';

    case 'nordic_skiing':
      return 'Ski nordique';

    case 'nordic_skiing_classic':
      return 'Ski classique';

    case 'nordic_skiing_skating':
      return 'Ski skating';

    case 'running':
      return 'Course à pied';

    case 'walking':
      return 'Marche';

    case 'hiking':
      return 'Randonnée';

    case 'strength_training':
      return 'Renforcement';

    case 'mobility':
      return 'Mobilité';

    case 'other':
      return 'Autre';

    default:
      return sportType;
  }
}
