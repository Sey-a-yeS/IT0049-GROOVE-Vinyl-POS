<?php

namespace App\Services;

use CodeIgniter\Database\BaseConnection;
use DomainException;
use Throwable;

class SaleService
{
    public function __construct(private ?BaseConnection $db = null)
    {
        $this->db ??= db_connect();
    }

    public function record(int $productId, ?int $customerId, int $quantity, int $staffId): int
    {
        if ($productId < 1) {
            throw new DomainException('Please select a valid product.');
        }
        if ($quantity < 1) {
            throw new DomainException('Quantity must be a positive whole number.');
        }
        if ($staffId < 1) {
            throw new DomainException('Your staff session is no longer valid.');
        }

        $this->db->transBegin();

        try {
            $staff = $this->db->table('users')
                ->select('id')
                ->where(['id' => $staffId, 'is_archived' => false])
                ->get()->getRowArray();
            if (! $staff) {
                throw new DomainException('Your staff account is no longer active.');
            }

            if ($customerId !== null) {
                $customer = $this->db->table('customers')
                    ->select('id')
                    ->where(['id' => $customerId, 'is_archived' => false])
                    ->get()->getRowArray();
                if (! $customer) {
                    throw new DomainException('The selected customer is not available.');
                }
            }

            $product = $this->db->query(
                'SELECT id, name, price, stock_quantity FROM products WHERE id = ? AND is_archived = 0 FOR UPDATE',
                [$productId]
            )->getRowArray();

            if (! $product) {
                throw new DomainException('The selected product is not available.');
            }
            if ((int) $product['stock_quantity'] < $quantity) {
                throw new DomainException(sprintf(
                    'Insufficient stock. Only %d unit%s available.',
                    $product['stock_quantity'],
                    ((int) $product['stock_quantity'] === 1 ? ' is' : 's are')
                ));
            }

            $updated = $this->db->table('products')
                ->where('id', $productId)
                ->where('is_archived', false)
                ->where('stock_quantity >=', $quantity)
                ->set('stock_quantity', 'stock_quantity - ' . $quantity, false)
                ->update();

            if (! $updated || $this->db->affectedRows() !== 1) {
                throw new DomainException('Stock changed before the sale could be completed. Please try again.');
            }

            $total = $this->multiplyMoney((string) $product['price'], $quantity);
            $inserted = $this->db->table('sales')->insert([
                'product_id'  => $productId,
                'customer_id' => $customerId,
                'sold_by'     => $staffId,
                'quantity'    => $quantity,
                'total_price' => $total,
                'created_at'  => date('Y-m-d H:i:s'),
            ]);

            if (! $inserted) {
                throw new DomainException('The sale could not be recorded.');
            }

            $saleId = (int) $this->db->insertID();
            if (! $this->db->transCommit()) {
                throw new DomainException('The sale transaction could not be committed.');
            }

            return $saleId;
        } catch (Throwable $exception) {
            $this->db->transRollback();
            throw $exception;
        }
    }

    private function multiplyMoney(string $price, int $quantity): string
    {
        $parts = explode('.', $price, 2);
        $whole = preg_replace('/\D/', '', $parts[0]) ?: '0';
        $fraction = str_pad(substr(preg_replace('/\D/', '', $parts[1] ?? ''), 0, 2), 2, '0');
        $cents = (((int) $whole * 100) + (int) $fraction) * $quantity;

        return intdiv($cents, 100) . '.' . str_pad((string) ($cents % 100), 2, '0', STR_PAD_LEFT);
    }
}
