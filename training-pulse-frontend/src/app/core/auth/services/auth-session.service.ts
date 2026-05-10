import {computed, inject, Injectable, signal} from '@angular/core';
import {AuthSession} from '../model/auth-session.model';
import {AuthApiService} from './auth-api.service';

const AUTH_TOKEN_STORAGE_KEY = 'trainingpulse.auth.token';

@Injectable({
  providedIn: 'root',
})
export class AuthSessionService {
  private readonly authApi = inject(AuthApiService);

  private readonly tokenSignal = signal<string | null>(null);
  private readonly sessionSignal = signal<AuthSession | null>(null);
  private readonly initializedSignal = signal(false);
  private readonly loadingSignal = signal(false);
  private readonly errorSignal = signal<string | null>(null);

  private initializationPromise: Promise<void> | null = null;

  readonly token = this.tokenSignal.asReadonly();
  readonly session = this.sessionSignal.asReadonly();
  readonly initialized = this.initializedSignal.asReadonly();
  readonly loading = this.loadingSignal.asReadonly();
  readonly error = this.errorSignal.asReadonly();

  readonly user = computed(() => this.sessionSignal()?.user ?? null);
  readonly athlete = computed(() => this.sessionSignal()?.athlete ?? null);
  readonly isAuthenticated = computed(() => this.tokenSignal() !== null && this.sessionSignal() !== null,);
  readonly roles = computed(() => this.sessionSignal()?.user.roles ?? []);

  hasRole(role: string): boolean {
    return this.roles().includes(role);
  }

  async ensureInitialized(): Promise<void> {
    if (this.initializedSignal()) {
      return;
    }

    if (this.initializationPromise !== null) {
      return this.initializationPromise;
    }

    this.initializationPromise = this.loadCurrentSession();

    try {
      await this.initializationPromise;
    } finally {
      this.initializationPromise = null;
    }
  }

  async loadCurrentSession(): Promise<void> {
    this.loadingSignal.set(true);
    this.errorSignal.set(null);

    const token = localStorage.getItem(AUTH_TOKEN_STORAGE_KEY);

    if (token === null || token === '') {
      this.clearSessionState();
      this.initializedSignal.set(true);
      this.loadingSignal.set(false);

      return;
    }

    this.tokenSignal.set(token);

    try {
      const session = await this.authApi.me(token);
      this.sessionSignal.set(session);
    } catch {
      this.clearStoredToken();
      this.clearSessionState();
    } finally {
      this.initializedSignal.set(true);
      this.loadingSignal.set(false);
    }
  }

  async login(email: string, password: string): Promise<void> {
    this.loadingSignal.set(true);
    this.errorSignal.set(null);

    try {
      const response = await this.authApi.login({ email, password });

      localStorage.setItem(AUTH_TOKEN_STORAGE_KEY, response.token);

      this.tokenSignal.set(response.token);
      this.sessionSignal.set(response.session);
      this.initializedSignal.set(true);
    } catch {
      this.clearStoredToken();
      this.clearSessionState();
      this.initializedSignal.set(true);
      this.errorSignal.set('Email ou mot de passe incorrect.');
    } finally {
      this.loadingSignal.set(false);
    }
  }

  logout(): void {
    this.clearStoredToken();
    this.clearSessionState();
    this.initializedSignal.set(true);
    this.errorSignal.set(null);
  }

  expireSession(): void {
    this.clearStoredToken();
    this.clearSessionState();
    this.initializedSignal.set(true);
    this.errorSignal.set(null);
  }

  clearError(): void {
    this.errorSignal.set(null);
  }

  private clearStoredToken(): void {
    localStorage.removeItem(AUTH_TOKEN_STORAGE_KEY);
  }

  private clearSessionState(): void {
    this.tokenSignal.set(null);
    this.sessionSignal.set(null);
  }
}
