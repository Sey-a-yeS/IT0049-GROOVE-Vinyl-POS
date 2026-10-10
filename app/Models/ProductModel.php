<?php

namespace App\Models;

use CodeIgniter\Model;

class ProductModel extends Model
{
    protected $table = 'products';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $allowedFields = ['name', 'price', 'stock_quantity', 'image', 'is_archived', 'created_at'];

    public function active()
    {
        return $this->where('is_archived', false);
    }
}
