<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateGrooveCoreTables extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id'             => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'name'           => ['type' => 'VARCHAR', 'constraint' => 100],
            'price'          => ['type' => 'DECIMAL', 'constraint' => '10,2'],
            'stock_quantity' => ['type' => 'INT', 'unsigned' => true, 'default' => 0],
            'image'          => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'is_archived'    => ['type' => 'BOOLEAN', 'default' => false],
            'created_at'     => ['type' => 'DATETIME'],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey(['is_archived', 'name']);
        $this->forge->createTable('products', true, ['ENGINE' => 'InnoDB']);

        $this->forge->addField([
            'id'          => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'full_name'   => ['type' => 'VARCHAR', 'constraint' => 100],
            'email'       => ['type' => 'VARCHAR', 'constraint' => 100],
            'phone'       => ['type' => 'VARCHAR', 'constraint' => 20, 'null' => true],
            'is_archived' => ['type' => 'BOOLEAN', 'default' => false],
            'created_at'  => ['type' => 'DATETIME'],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey(['is_archived', 'full_name']);
        $this->forge->addKey('email');
        $this->forge->createTable('customers', true, ['ENGINE' => 'InnoDB']);

        $this->forge->addField([
            'id'          => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'username'    => ['type' => 'VARCHAR', 'constraint' => 50],
            'full_name'   => ['type' => 'VARCHAR', 'constraint' => 100],
            'password'    => ['type' => 'VARCHAR', 'constraint' => 255],
            'avatar'      => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'is_archived' => ['type' => 'BOOLEAN', 'default' => false],
            'created_at'  => ['type' => 'DATETIME'],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('username');
        $this->forge->addKey(['is_archived', 'full_name']);
        $this->forge->createTable('users', true, ['ENGINE' => 'InnoDB']);

        $this->db->query('ALTER TABLE products ADD CONSTRAINT chk_products_price_nonnegative CHECK (price >= 0)');
        $this->db->query('ALTER TABLE products ADD CONSTRAINT chk_products_stock_nonnegative CHECK (stock_quantity >= 0)');
    }

    public function down(): void
    {
        $this->forge->dropTable('users', true);
        $this->forge->dropTable('customers', true);
        $this->forge->dropTable('products', true);
    }
}
