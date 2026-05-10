import {HttpClient, HttpHeaders} from '@angular/common/http';
import {inject, Injectable} from '@angular/core';
import {firstValueFrom} from 'rxjs';
import {parseOrThrow} from '../../api/parse-or-throw';
import {
  AuthLoginResponse,
  authLoginResponseSchema,
  AuthSession,
  authSessionSchema,
  LoginRequest, RefreshTokenResponse, refreshTokenResponseSchema,
} from '../model/auth-session.model';

@Injectable({
  providedIn: 'root',
})
export class AuthApiService {
  private readonly http = inject(HttpClient);

  async login(payload: LoginRequest): Promise<AuthLoginResponse> {
    const response = await firstValueFrom(
      this.http.post<unknown>(
        '/api/v1/auth/login', payload, {withCredentials: true},
      ),
    );

    return parseOrThrow(authLoginResponseSchema, response);
  }

  async me(token: string): Promise<AuthSession> {
    const response = await firstValueFrom(
      this.http.get<unknown>(
        '/api/v1/me',
      ),
    );

    return parseOrThrow(authSessionSchema, response);
  }

  async refresh(): Promise<RefreshTokenResponse> {
    const response = await firstValueFrom(
      this.http.post<unknown>(
        '/api/v1/auth/refresh',
        {},
        {
          withCredentials: true,
        },
      ),
    );

    return parseOrThrow(refreshTokenResponseSchema, response);
  }

}
