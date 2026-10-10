<?php

namespace App\Controllers;

use App\Models\CustomerModel;
use App\Models\ProductModel;
use App\Models\SaleModel;
use App\Models\UserModel;

class Dashboard extends BaseController
{
    public function index(): string
    {
        $products = new ProductModel();
        $customers = new CustomerModel();
        $users = new UserModel();
        $sales = new SaleModel();

        return view('dashboard/index', [
            'title' => 'Dashboard',
            'metrics' => [
                'products' => $products->where('is_archived', false)->countAllResults(),
                'customers' => $customers->where('is_archived', false)->countAllResults(),
                'staff' => $users->where('is_archived', false)->countAllResults(),
                'sales' => $sales->countAllResults(),
            ],
            'lowStock' => (new ProductModel())->where('is_archived', false)->where('stock_quantity <=', 5)->orderBy('stock_quantity')->findAll(6),
            'recentSales' => (new SaleModel())->withDetails()->findAll(5),
        ]);
    }
}
