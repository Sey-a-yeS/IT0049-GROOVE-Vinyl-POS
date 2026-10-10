<?php

namespace App\Filters;

use App\Models\UserModel;
use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class AuthFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $staffId = (int) session()->get('staff_id');
        $activeStaff = $staffId > 0
            ? (new UserModel())->select('id')->where(['id' => $staffId, 'is_archived' => false])->first()
            : null;

        if (! $activeStaff) {
            if ($staffId > 0) {
                session()->destroy();
            }
            session()->set('intended_url', current_url());

            return redirect()->to('/login')->with('error', 'Please sign in to continue.');
        }

        return null;
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        return null;
    }
}
