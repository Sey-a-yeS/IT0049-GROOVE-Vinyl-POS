<?php

namespace App\Controllers;

use App\Models\CustomerModel;
use App\Models\ProductModel;
use App\Models\SaleModel;
use App\Services\SaleService;
use DomainException;
use Throwable;

class Sales extends BaseController
{
    public function new(): string
    {
        return view('sales/form', [
            'title' => 'Record sale',
            'products' => (new ProductModel())->where('is_archived', false)->where('stock_quantity >', 0)->orderBy('name')->findAll(),
            'customers' => (new CustomerModel())->where('is_archived', false)->orderBy('full_name')->findAll(),
        ]);
    }

    public function create()
    {
        $rules = [
            'product_id' => 'required|integer|greater_than[0]',
            'customer_id' => 'permit_empty|integer|greater_than[0]',
            'quantity' => 'required|integer|greater_than[0]',
        ];
        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $customerValue = trim((string) $this->request->getPost('customer_id'));
        try {
            $saleId = (new SaleService())->record(
                (int) $this->request->getPost('product_id'),
                $customerValue === '' ? null : (int) $customerValue,
                (int) $this->request->getPost('quantity'),
                (int) session()->get('staff_id')
            );
        } catch (DomainException $exception) {
            return redirect()->back()->withInput()->with('error', $exception->getMessage());
        } catch (Throwable $exception) {
            log_message('error', 'Sale recording failed: {message}', ['message' => $exception->getMessage()]);
            return redirect()->back()->withInput()->with('error', 'The sale could not be completed. No inventory was changed.');
        }

        return redirect()->to('/sales/history')->with('success', 'Sale #' . $saleId . ' recorded and inventory updated.');
    }

    public function history(): string
    {
        $sales = new SaleModel();
        return view('sales/history', [
            'title' => 'Sales history',
            'sales' => $sales->withDetails()->paginate(20),
            'pager' => $sales->pager,
        ]);
    }
}
