<?php

namespace App\Controllers;

use App\Models\UserModel;

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
        $rules = [
            'username' => 'required|max_length[50]',
            'password' => 'required|max_length[255]',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()
                ->with('login_username', trim((string) $this->request->getPost('username')))
                ->with('errors', $this->validator->getErrors());
        }

        $user = (new UserModel())
            ->where('username', trim((string) $this->request->getPost('username')))
            ->where('is_archived', false)
            ->first();

        if (! $user || ! password_verify((string) $this->request->getPost('password'), $user['password'])) {
            return redirect()->back()
                ->with('login_username', trim((string) $this->request->getPost('username')))
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
