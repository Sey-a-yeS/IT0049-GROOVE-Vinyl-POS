<?php

namespace App\Controllers;

use App\Models\UserModel;
use Config\Services;

class Auth extends BaseController
{
    public function login()
    {
        if (session()->get('staff_id')) {
            return redirect()->to('/dashboard');
        }

        return view('auth/login', ['title' => 'Staff sign in']);
    }

    public function attempt()
    {
        $username = strtolower(trim((string) $this->request->getPost('username')));
        $throttleKey = 'groove-login-' . hash('sha256', $this->request->getIPAddress() . '|' . $username);
        if (ENVIRONMENT !== 'testing' && ! Services::throttler()->check($throttleKey, 5, MINUTE)) {
            return redirect()->back()
                ->with('login_username', $username)
                ->with('error', 'Too many sign-in attempts. Please wait one minute and try again.');
        }

        $rules = [
            'username' => 'required|max_length[50]',
            'password' => 'required|max_length[255]',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()
                ->with('login_username', $username)
                ->with('errors', $this->validator->getErrors());
        }

        $user = (new UserModel())
            ->where('username', $username)
            ->where('is_archived', false)
            ->first();

        if (! $user || ! password_verify((string) $this->request->getPost('password'), $user['password'])) {
            return redirect()->back()
                ->with('login_username', $username)
                ->with('error', 'The username or password is incorrect.');
        }

        session()->regenerate(true);
        session()->set([
            'staff_id' => (int) $user['id'],
            'staff_username' => $user['username'],
            'staff_name' => $user['full_name'],
            'staff_avatar' => $user['avatar'],
        ]);

        return redirect()->to('/dashboard')->with('success', 'Welcome back, ' . $user['full_name'] . '.');
    }

    public function logout()
    {
        session()->destroy();

        return redirect()->to('/login')->with('success', 'You have been signed out.');
    }
}
