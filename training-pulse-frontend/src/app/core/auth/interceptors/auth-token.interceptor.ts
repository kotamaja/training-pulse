import { HttpInterceptorFn } from '@angular/common/http';

const AUTH_TOKEN_STORAGE_KEY = 'trainingpulse.auth.token';

export const authTokenInterceptor: HttpInterceptorFn = (req, next) => {
  if (!req.url.startsWith('/api/')) {
    return next(req);
  }

  if (req.url === '/api/v1/auth/login') {
    return next(req);
  }

  const token = localStorage.getItem(AUTH_TOKEN_STORAGE_KEY);

  if (!token) {
    return next(req);
  }

  return next(req.clone({
    setHeaders: {
      Authorization: `Bearer ${token}`,
    },
  }));
};
