<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\User;
use Phare\Attributes\Route;
use Phare\Http\Request;
use Phare\Support\Facades\Auth;
use Phare\Support\Facades\Inertia;

/**
 * Laravel パリティ: 機密操作の前にパスワードを再確認させる画面。
 * 確認に成功すると `auth.password_confirmed_at` をセッションに記録し、
 * `password.confirm` ミドルウェアがそのタイムスタンプを参照する。
 */
class ConfirmablePasswordController extends Controller
{
    #[Route('confirm-password', middlewares: ['auth'], name: 'password.confirm.show')]
    public function show(Request $request)
    {
        return Inertia::render('auth/ConfirmPassword');
    }

    #[Route('confirm-password', methods: ['POST'], middlewares: ['auth'], name: 'password.confirm.store')]
    public function store(Request $request)
    {
        $user = Auth::user();
        if (!$user instanceof User) {
            return $this->response->redirect('/user/login');
        }

        $password = (string)$request->get('password');

        if (!Auth::attempt(['id' => $user->id, 'password' => $password])) {
            $this->session->set('errors', ['password' => 'パスワードが正しくありません。']);

            return $this->response->redirect('/user/confirm-password');
        }

        $this->session->set('auth.password_confirmed_at', time());

        $intended = (string)$this->session->get('url.intended', '/dashboard');
        $this->session->remove('url.intended');

        return $this->response->redirect($intended ?: '/dashboard');
    }
}
