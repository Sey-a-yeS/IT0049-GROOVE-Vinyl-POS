<?php

namespace App\Controllers;

use App\Models\UserModel;
use App\Services\ImageUploadService;
use RuntimeException;

class Staff extends BaseController
{
    private UserModel $users;
    private ImageUploadService $images;

    public function __construct()
    {
        $this->users = new UserModel();
        $this->images = new ImageUploadService();
    }

    public function index(): string
    {
        $query = trim((string) $this->request->getGet('q'));
        $this->users->where('is_archived', false);
        if ($query !== '') {
            $this->users->groupStart()->like('full_name', $query)->orLike('username', $query)->groupEnd();
        }

        return view('staff/index', [
            'title' => 'Staff',
            'staff' => $this->users->orderBy('full_name')->paginate(15),
            'pager' => $this->users->pager,
            'query' => $query,
        ]);
    }

    public function show(int $id)
    {
        $staff = $this->users->find($id);
        if (! $staff) {
            return redirect()->to('/staff')->with('error', 'Staff account not found.');
        }
        unset($staff['password']);
        return view('staff/show', ['title' => 'Staff details', 'staffMember' => $staff]);
    }

    public function new(): string
    {
        return view('staff/form', ['title' => 'New staff account', 'staffMember' => null]);
    }

    public function create()
    {
        $rules = $this->rules(true);
        if ($this->hasUpload('avatar')) {
            $rules['avatar'] = 'uploaded[avatar]|is_image[avatar]|mime_in[avatar,image/jpg,image/jpeg,image/png]|max_size[avatar,2048]';
        }
        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $avatar = null;
        try {
            if ($this->hasUpload('avatar')) {
                $avatar = $this->images->store($this->request->getFile('avatar'), 'avatars');
            }
            $this->users->insert([
                'username' => strtolower(trim((string) $this->request->getPost('username'))),
                'full_name' => trim((string) $this->request->getPost('full_name')),
                'password' => password_hash((string) $this->request->getPost('password'), PASSWORD_DEFAULT),
                'avatar' => $avatar,
                'is_archived' => false,
                'created_at' => date('Y-m-d H:i:s'),
            ]);
        } catch (RuntimeException $exception) {
            return redirect()->back()->withInput()->with('error', $exception->getMessage());
        }

        return redirect()->to('/staff')->with('success', 'Staff account created.');
    }

    public function edit(int $id)
    {
        $staff = $this->users->find($id);
        if (! $staff || $staff['is_archived']) {
            return redirect()->to('/staff')->with('error', 'Staff account not found.');
        }
        unset($staff['password']);
        return view('staff/form', ['title' => 'Edit staff account', 'staffMember' => $staff]);
    }

    public function update(int $id)
    {
        $staff = $this->users->find($id);
        if (! $staff || $staff['is_archived']) {
            return redirect()->to('/staff')->with('error', 'Staff account not found.');
        }

        $rules = $this->rules(false);
        $rules['username'] .= '|is_unique[users.username,id,' . $id . ']';
        if ($this->hasUpload('avatar')) {
            $rules['avatar'] = 'uploaded[avatar]|is_image[avatar]|mime_in[avatar,image/jpg,image/jpeg,image/png]|max_size[avatar,2048]';
        }
        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $newAvatar = null;
        try {
            if ($this->hasUpload('avatar')) {
                $newAvatar = $this->images->store($this->request->getFile('avatar'), 'avatars');
            }
            $payload = [
                'username' => strtolower(trim((string) $this->request->getPost('username'))),
                'full_name' => trim((string) $this->request->getPost('full_name')),
                'avatar' => $newAvatar ?? $staff['avatar'],
            ];
            if ((string) $this->request->getPost('password') !== '') {
                $payload['password'] = password_hash((string) $this->request->getPost('password'), PASSWORD_DEFAULT);
            }
            $this->users->update($id, $payload);
            if ($newAvatar) {
                $this->images->delete($staff['avatar'], 'avatars');
            }
        } catch (RuntimeException $exception) {
            if ($newAvatar) {
                $this->images->delete($newAvatar, 'avatars');
            }
            return redirect()->back()->withInput()->with('error', $exception->getMessage());
        }

        if ((int) session()->get('staff_id') === $id) {
            session()->set(['staff_username' => $payload['username'], 'staff_name' => $payload['full_name'], 'staff_avatar' => $payload['avatar']]);
        }

        return redirect()->to('/staff/' . $id)->with('success', 'Staff account updated.');
    }

    public function archive(int $id)
    {
        $staff = $this->users->find($id);
        if (! $staff || $staff['is_archived']) {
            return redirect()->to('/staff')->with('error', 'Staff account not found.');
        }
        if ((int) session()->get('staff_id') === $id) {
            return redirect()->to('/staff')->with('error', 'You cannot archive the account currently signed in.');
        }
        if ($this->users->where('is_archived', false)->countAllResults() <= 1) {
            return redirect()->to('/staff')->with('error', 'At least one active staff account must remain.');
        }
        $this->users->update($id, ['is_archived' => true]);
        return redirect()->to('/staff')->with('success', 'Staff account archived. Historical sales remain intact.');
    }

    private function rules(bool $creating): array
    {
        return [
            'username' => 'required|max_length[50]|regex_match[/^[A-Za-z0-9._-]+$/]' . ($creating ? '|is_unique[users.username]' : ''),
            'full_name' => 'required|max_length[100]',
            'password' => ($creating ? 'required' : 'permit_empty') . '|min_length[10]|max_length[255]',
        ];
    }

    private function hasUpload(string $field): bool
    {
        $file = $this->request->getFile($field);
        return $file !== null && $file->getError() !== UPLOAD_ERR_NO_FILE;
    }
}
