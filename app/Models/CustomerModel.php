<?php

namespace App\Models;

use CodeIgniter\Model;

class CustomerModel extends Model
{
    protected $table = 'customers';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $allowedFields = ['full_name', 'email', 'phone', 'is_archived', 'created_at'];

    public function active()
    {
        return $this->where('is_archived', false);
    }
}
