<?php

namespace App\Models;

use CodeIgniter\Model;

class UserModel extends Model
{
    protected $table = 'users';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $allowedFields = ['username', 'full_name', 'password', 'avatar', 'is_archived', 'created_at'];

    public function active()
    {
        return $this->where('is_archived', false);
    }
}
