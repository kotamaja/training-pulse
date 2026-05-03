import { HttpClient } from '@angular/common/http';
import { inject, Injectable } from '@angular/core';
import { firstValueFrom } from 'rxjs';
import { parseOrThrow } from '../../api/parse-or-throw';
import {
  AuthSession,
  authSessionSchema,
  LoginRequest,
} from '../model/auth-session.model';

@Injectable({
  providedIn: 'root',
})
export class AuthApiService {
  private readonly http = inject(HttpClient);

  async login(payload: LoginRequest): Promise<AuthSession> {
    const response = await firstValueFrom(
      this.http.post<unknown>(
        '/api/v1/auth/login',
        payload,
        { withCredentials: true },
      ),
    );

    return parseOrThrow(authSessionSchema, response);
  }

  async me(): Promise<AuthSession> {
    const response = await firstValueFrom(
      this.http.get<unknown>(
        '/api/v1/me',
        { withCredentials: true },
      ),
    );

    return parseOrThrow(authSessionSchema, response);
  }

  async logout(): Promise<void> {
    await firstValueFrom(
      this.http.post<void>(
        '/api/v1/auth/logout',
        {},
        { withCredentials: true },
      ),
    );
  }
}
