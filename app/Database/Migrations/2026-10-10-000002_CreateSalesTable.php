<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateSalesTable extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id'          => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'product_id'  => ['type' => 'INT', 'unsigned' => true],
            'customer_id' => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'sold_by'     => ['type' => 'INT', 'unsigned' => true],
            'quantity'    => ['type' => 'INT', 'unsigned' => true],
            'total_price' => ['type' => 'DECIMAL', 'constraint' => '10,2'],
            'created_at'  => ['type' => 'DATETIME'],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('product_id');
        $this->forge->addKey('customer_id');
        $this->forge->addKey('sold_by');
        $this->forge->addKey('created_at');
        $this->forge->addForeignKey('product_id', 'products', 'id', 'RESTRICT', 'RESTRICT', 'fk_sales_product');
        $this->forge->addForeignKey('customer_id', 'customers', 'id', 'RESTRICT', 'RESTRICT', 'fk_sales_customer');
        $this->forge->addForeignKey('sold_by', 'users', 'id', 'RESTRICT', 'RESTRICT', 'fk_sales_staff');
        $this->forge->createTable('sales', true, ['ENGINE' => 'InnoDB']);

        $this->db->query('ALTER TABLE sales ADD CONSTRAINT chk_sales_quantity_positive CHECK (quantity > 0)');
        $this->db->query('ALTER TABLE sales ADD CONSTRAINT chk_sales_total_nonnegative CHECK (total_price >= 0)');
    }

    public function down(): void
    {
        $this->forge->dropTable('sales', true);
    }
}
