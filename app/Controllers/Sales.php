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
        $saleToken = bin2hex(random_bytes(24));
        session()->set('sale_form_token', $saleToken);

        return view('sales/form', [
            'title' => 'Record sale',
            'products' => (new ProductModel())->where('is_archived', false)->where('stock_quantity >', 0)->orderBy('name')->findAll(),
            'customers' => (new CustomerModel())->where('is_archived', false)->orderBy('full_name')->findAll(),
            'saleToken' => $saleToken,
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

        $submittedToken = (string) $this->request->getPost('sale_token');
        $sessionToken = (string) session()->get('sale_form_token');
        if ($sessionToken === '' || $submittedToken === '' || ! hash_equals($sessionToken, $submittedToken)) {
            return redirect()->to('/sales/new')->with('error', 'This sale form has expired or was already submitted. Please review and submit it again.');
        }
        session()->remove('sale_form_token');

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

        $receipt = (new SaleModel())->withDetails()->where('sales.id', $saleId)->first();

        return redirect()->to('/sales/history')
            ->with('success', 'Sale #' . $saleId . ' recorded and inventory updated.')
            ->with('sale_receipt', $receipt);
    }

    public function history(): string
    {
        $query = trim((string) $this->request->getGet('q'));
        $sales = new SaleModel();
        $sales->withDetails();
        if ($query !== '') {
            $sales->groupStart()
                ->like('products.name', $query)
                ->orLike('customers.full_name', $query)
                ->orLike('users.full_name', $query);
            if (ctype_digit($query)) {
                $sales->orWhere('sales.id', (int) $query);
            }
            $sales->groupEnd();
        }

        return view('sales/history', [
            'title' => 'Sales history',
            'sales' => $sales->paginate(20),
            'pager' => $sales->pager,
            'query' => $query,
        ]);
    }
}
