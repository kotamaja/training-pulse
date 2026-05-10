import { HttpErrorResponse, HttpInterceptorFn } from '@angular/common/http';
import { inject } from '@angular/core';
import { Router } from '@angular/router';
import { catchError, from, switchMap, throwError } from 'rxjs';
import { AuthSessionService } from '../services/auth-session.service';

export const authErrorInterceptor: HttpInterceptorFn = (req, next) => {
  const authSession = inject(AuthSessionService);
  const router = inject(Router);

  return next(req).pipe(
    catchError((error: unknown) => {
      if (!(error instanceof HttpErrorResponse) || error.status !== 401) {
        return throwError(() => error);
      }

      const isAuthRequest =
        req.url === '/api/v1/auth/login' ||
        req.url === '/api/v1/auth/refresh';

      if (isAuthRequest) {
        return throwError(() => error);
      }

      return from(authSession.refreshAccessToken()).pipe(
        switchMap((newToken) => {
          const retriedRequest = req.clone({
            setHeaders: {
              Authorization: `Bearer ${newToken}`,
            },
          });

          return next(retriedRequest);
        }),
        catchError((refreshError: unknown) => {
          authSession.logout();
          void router.navigateByUrl('/login');

          return throwError(() => refreshError);
        }),
      );
    }),
  );
};
