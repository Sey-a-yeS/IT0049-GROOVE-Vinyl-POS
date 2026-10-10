<?php

namespace App\Controllers;

use App\Models\CustomerModel;

class Customers extends BaseController
{
    private CustomerModel $customers;

    public function __construct()
    {
        $this->customers = new CustomerModel();
    }

    public function index(): string
    {
        return view('customers/index', [
            'title' => 'Customers',
            'customers' => $this->customers->where('is_archived', false)->orderBy('full_name')->paginate(15),
            'pager' => $this->customers->pager,
        ]);
    }

    public function show(int $id)
    {
        $customer = $this->customers->find($id);
        if (! $customer) {
            return redirect()->to('/customers')->with('error', 'Customer not found.');
        }
        return view('customers/show', ['title' => 'Customer details', 'customer' => $customer]);
    }

    public function new(): string
    {
        return view('customers/form', ['title' => 'New customer', 'customer' => null]);
    }

    public function create()
    {
        if (! $this->validate($this->rules())) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }
        $this->customers->insert($this->payload() + ['is_archived' => false, 'created_at' => date('Y-m-d H:i:s')]);
        return redirect()->to('/customers')->with('success', 'Customer created.');
    }

    public function edit(int $id)
    {
        $customer = $this->customers->find($id);
        if (! $customer || $customer['is_archived']) {
            return redirect()->to('/customers')->with('error', 'Customer not found.');
        }
        return view('customers/form', ['title' => 'Edit customer', 'customer' => $customer]);
    }

    public function update(int $id)
    {
        $customer = $this->customers->find($id);
        if (! $customer || $customer['is_archived']) {
            return redirect()->to('/customers')->with('error', 'Customer not found.');
        }
        if (! $this->validate($this->rules())) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }
        $this->customers->update($id, $this->payload());
        return redirect()->to('/customers/' . $id)->with('success', 'Customer updated.');
    }

    public function archive(int $id)
    {
        $customer = $this->customers->find($id);
        if (! $customer || $customer['is_archived']) {
            return redirect()->to('/customers')->with('error', 'Customer not found.');
        }
        $this->customers->update($id, ['is_archived' => true]);
        return redirect()->to('/customers')->with('success', 'Customer archived. Historical sales remain intact.');
    }

    private function rules(): array
    {
        return [
            'full_name' => 'required|max_length[100]',
            'email' => 'required|valid_email|max_length[100]',
            'phone' => 'permit_empty|max_length[20]|regex_match[/^[0-9+() .-]{7,20}$/]',
        ];
    }

    private function payload(): array
    {
        $phone = trim((string) $this->request->getPost('phone'));
        return [
            'full_name' => trim((string) $this->request->getPost('full_name')),
            'email' => strtolower(trim((string) $this->request->getPost('email'))),
            'phone' => $phone === '' ? null : $phone,
        ];
    }
}
