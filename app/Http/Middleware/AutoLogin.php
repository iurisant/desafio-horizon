<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Este é um projeto de desafio/demo: nenhuma página deve exigir login manual.
 * Autentica a requisição automaticamente com um usuário padrão, sem passar
 * pela tela de login.
 */
class AutoLogin
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! Auth::check()) {
            Auth::login($this->defaultUser());
        }

        return $next($request);
    }

    protected function defaultUser(): User
    {
        return User::query()->orderBy('id')->first() ?? User::query()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'email_verified_at' => now(),
            'password' => 'password',
        ]);
    }
}
