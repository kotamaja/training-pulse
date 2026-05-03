import { computed, inject, Injectable, signal } from '@angular/core';
import { AuthSession } from '../model/auth-session.model';
import { AuthApiService } from './auth-api.service';

@Injectable({
  providedIn: 'root',
})
export class AuthSessionService {
  private readonly authApi = inject(AuthApiService);

  private readonly sessionSignal = signal<AuthSession | null>(null);
  private readonly initializedSignal = signal(false);
  private readonly loadingSignal = signal(false);
  private readonly errorSignal = signal<string | null>(null);

  private initializationPromise: Promise<void> | null = null;

  readonly session = this.sessionSignal.asReadonly();
  readonly initialized = this.initializedSignal.asReadonly();
  readonly loading = this.loadingSignal.asReadonly();
  readonly error = this.errorSignal.asReadonly();

  readonly user = computed(() => this.sessionSignal()?.user ?? null);
  readonly athlete = computed(() => this.sessionSignal()?.athlete ?? null);
  readonly isAuthenticated = computed(() => this.sessionSignal() !== null);
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

    try {
      const session = await this.authApi.me();

      this.sessionSignal.set(session);
    } catch  {

      this.sessionSignal.set(null);
    } finally {
      this.initializedSignal.set(true);
      this.loadingSignal.set(false);
    }
  }

  async login(email: string, password: string): Promise<void> {
    this.loadingSignal.set(true);
    this.errorSignal.set(null);

    try {
      const session = await this.authApi.login({ email, password });

      this.sessionSignal.set(session);
      this.initializedSignal.set(true);
    } catch {
      this.sessionSignal.set(null);
      this.initializedSignal.set(true);
      this.errorSignal.set('Email ou mot de passe incorrect.');
    } finally {
      this.loadingSignal.set(false);
    }
  }

  async logout(): Promise<void> {
    this.loadingSignal.set(true);
    this.errorSignal.set(null);

    try {
      await this.authApi.logout();
    } finally {
      this.sessionSignal.set(null);
      this.initializedSignal.set(true);
      this.loadingSignal.set(false);
    }
  }

  clearError(): void {
    this.errorSignal.set(null);
  }
}
