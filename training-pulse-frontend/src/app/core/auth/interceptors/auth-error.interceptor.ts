// src/app/core/auth/interceptors/auth-error.interceptor.ts

import { HttpErrorResponse, HttpInterceptorFn } from '@angular/common/http';
import { inject } from '@angular/core';
import { Router } from '@angular/router';
import { catchError, throwError } from 'rxjs';
import { AuthSessionService } from '../services/auth-session.service';

export const authErrorInterceptor: HttpInterceptorFn = (req, next) => {
  const authSession = inject(AuthSessionService);
  const router = inject(Router);

  return next(req).pipe(
    catchError((error: unknown) => {
      if (error instanceof HttpErrorResponse && error.status === 401) {
        const isLoginRequest = req.url === '/api/v1/auth/login';

        if (!isLoginRequest) {
          authSession.expireSession();
          void router.navigateByUrl('/login');
        }
      }

      return throwError(() => error);
    }),
  );
};
