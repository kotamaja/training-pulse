import { Component, computed, inject, signal } from '@angular/core';
import { Router } from '@angular/router';
import { form, FormField, FormRoot, required, email } from '@angular/forms/signals';

import { AuthSessionService } from '../../../../core/auth/services/auth-session.service';
import {createEmptyLoginFormValue, LoginFormValue} from '../../model/login-form.model';


@Component({
  selector: 'tp-login-page',
  standalone: true,
  imports: [
    FormRoot,
    FormField,
  ],
  templateUrl: './login-page.component.html',
  styleUrl: './login-page.component.scss',
})
export class LoginPageComponent {
  private readonly router = inject(Router);
  protected readonly authSession = inject(AuthSessionService);

  protected readonly submitted = signal(false);

  readonly formValue = signal<LoginFormValue>(createEmptyLoginFormValue());

  readonly loginForm = form(this.formValue, (path) => {
    required(path.email, {
      message: 'Adresse email obligatoire.',
    });

    email(path.email, {
      message: 'Adresse email invalide.',
    });

    required(path.password, {
      message: 'Mot de passe obligatoire.',
    });
  }, {
    submission: {
      action: async () => {
        if (this.authSession.loading()) {
          return;
        }

        this.submitted.set(true);
        this.authSession.clearError();

        await this.authSession.login(
          this.formValue().email,
          this.formValue().password,
        );

        if (this.authSession.isAuthenticated()) {
          await this.router.navigateByUrl('/activities');
        }

        this.formValue.set(createEmptyLoginFormValue());
      },
    },
  });

  readonly emailInvalid = computed(() =>
    this.loginForm.email().touched() && this.loginForm.email().invalid(),
  );

  readonly passwordInvalid = computed(() =>
    this.loginForm.password().touched() && this.loginForm.password().invalid(),
  );

  readonly emailError = computed(() =>
    this.loginForm.email().errors()[0]?.message ?? null,
  );

  readonly passwordError = computed(() =>
    this.loginForm.password().errors()[0]?.message ?? null,
  );
}
