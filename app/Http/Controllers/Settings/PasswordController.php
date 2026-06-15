<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\User;
use Phare\Attributes\Route;
use Phare\Http\Request;
use Phare\Support\Facades\Auth;
use Phare\Support\Facades\Inertia;

class PasswordController extends Controller
{
    #[Route('password', middlewares: ['auth', 'verified'], name: 'settings.password')]
    public function edit(Request $request)
    {
        return Inertia::render('settings/Password');
    }

    #[Route('password', methods: ['PUT'], middlewares: ['auth', 'verified'], name: 'settings.password.update')]
    public function update(Request $request)
    {
        $user = Auth::user();
        if (!$user instanceof User) {
            return $this->response->redirect('/user/login');
        }

        $current = (string)$request->get('current_password');
        $password = (string)$request->get('password');
        $confirmation = (string)$request->get('password_confirmation');

        if (!Auth::attempt(['id' => $user->id, 'password' => $current])) {
            $this->session->set('errors', ['current_password' => '現在のパスワードが正しくありません。']);

            return $this->response->redirect('/settings/password');
        }

        if (strlen($password) < 8) {
            $this->session->set('errors', ['password' => 'パスワードは8文字以上で入力してください。']);

            return $this->response->redirect('/settings/password');
        }

        if ($password !== $confirmation) {
            $this->session->set('errors', ['password' => 'パスワードが一致しません。']);

            return $this->response->redirect('/settings/password');
        }

        $user->password = $password;
        $user->save();

        $this->flashSession->success('パスワードを更新しました。');

        return $this->response->redirect('/settings/password');
    }
}
