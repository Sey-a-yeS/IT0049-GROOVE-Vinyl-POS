<?php

namespace App\Controllers;

use App\Models\ProductModel;
use App\Services\ImageUploadService;
use RuntimeException;

class Products extends BaseController
{
    private ProductModel $products;
    private ImageUploadService $images;

    public function __construct()
    {
        $this->products = new ProductModel();
        $this->images = new ImageUploadService();
    }

    public function index(): string
    {
        $query = trim((string) $this->request->getGet('q'));
        $this->products->where('is_archived', false);
        if ($query !== '') {
            $this->products->like('name', $query);
        }

        return view('products/index', [
            'title' => 'Products',
            'products' => $this->products->orderBy('name')->paginate(12),
            'pager' => $this->products->pager,
            'query' => $query,
        ]);
    }

    public function show(int $id)
    {
        $product = $this->products->find($id);
        if (! $product) {
            return redirect()->to('/products')->with('error', 'Product not found.');
        }

        return view('products/show', ['title' => 'Product details', 'product' => $product]);
    }

    public function new(): string
    {
        return view('products/form', ['title' => 'New product', 'product' => null]);
    }

    public function create()
    {
        $rules = $this->rules();
        if ($this->hasUpload('image')) {
            $rules['image'] = 'uploaded[image]|is_image[image]|mime_in[image,image/jpg,image/jpeg,image/png]|max_size[image,2048]';
        }

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $image = null;
        try {
            if ($this->hasUpload('image')) {
                $image = $this->images->store($this->request->getFile('image'), 'products');
            }

            $this->products->insert([
                'name' => trim((string) $this->request->getPost('name')),
                'price' => number_format((float) $this->request->getPost('price'), 2, '.', ''),
                'stock_quantity' => (int) $this->request->getPost('stock_quantity'),
                'image' => $image,
                'is_archived' => false,
                'created_at' => date('Y-m-d H:i:s'),
            ]);
        } catch (RuntimeException $exception) {
            return redirect()->back()->withInput()->with('error', $exception->getMessage());
        }

        return redirect()->to('/products')->with('success', 'Product created.');
    }

    public function edit(int $id)
    {
        $product = $this->products->find($id);
        if (! $product || $product['is_archived']) {
            return redirect()->to('/products')->with('error', 'Product not found.');
        }

        return view('products/form', ['title' => 'Edit product', 'product' => $product]);
    }

    public function update(int $id)
    {
        $product = $this->products->find($id);
        if (! $product || $product['is_archived']) {
            return redirect()->to('/products')->with('error', 'Product not found.');
        }

        $rules = $this->rules();
        if ($this->hasUpload('image')) {
            $rules['image'] = 'uploaded[image]|is_image[image]|mime_in[image,image/jpg,image/jpeg,image/png]|max_size[image,2048]';
        }
        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $newImage = null;
        try {
            if ($this->hasUpload('image')) {
                $newImage = $this->images->store($this->request->getFile('image'), 'products');
            }
            $this->products->update($id, [
                'name' => trim((string) $this->request->getPost('name')),
                'price' => number_format((float) $this->request->getPost('price'), 2, '.', ''),
                'stock_quantity' => (int) $this->request->getPost('stock_quantity'),
                'image' => $newImage ?? $product['image'],
            ]);
            if ($newImage) {
                $this->images->delete($product['image'], 'products');
            }
        } catch (RuntimeException $exception) {
            if ($newImage) {
                $this->images->delete($newImage, 'products');
            }
            return redirect()->back()->withInput()->with('error', $exception->getMessage());
        }

        return redirect()->to('/products/' . $id)->with('success', 'Product updated.');
    }

    public function archive(int $id)
    {
        $product = $this->products->find($id);
        if (! $product || $product['is_archived']) {
            return redirect()->to('/products')->with('error', 'Product not found.');
        }

        $this->products->update($id, ['is_archived' => true]);
        return redirect()->to('/products')->with('success', 'Product archived and removed from sale selection.');
    }

    private function rules(): array
    {
        return [
            'name' => 'required|max_length[100]',
            'price' => 'required|decimal|greater_than_equal_to[0]|max_length[11]',
            'stock_quantity' => 'required|integer|greater_than_equal_to[0]',
        ];
    }

    private function hasUpload(string $field): bool
    {
        $file = $this->request->getFile($field);
        return $file !== null && $file->getError() !== UPLOAD_ERR_NO_FILE;
    }
}
