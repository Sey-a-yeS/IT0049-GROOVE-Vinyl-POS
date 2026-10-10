<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class GrooveSeeder extends Seeder
{
    public function run(): void
    {
        $now = date('Y-m-d H:i:s');

        if ($this->db->table('products')->countAllResults() === 0) {
            $products = [
                ['Blue Hour Sessions', '1250.00', 12],
                ['Midnight Brass Vol. 1', '1480.00', 4],
                ['Sunday Standards', '1180.00', 0],
                ['Electric Avenue Revisited', '1650.00', 9],
                ['Northern Lights LP', '1725.00', 3],
                ['Basement Frequencies', '1395.00', 7],
                ['Afterglow Indie Compilation', '1320.00', 14],
                ['Velvet Signals', '1540.00', 6],
                ['Soulful City Nights', '1450.00', 11],
                ['Golden Motown Echoes', '1580.00', 2],
                ['Neon Pulse EP', '980.00', 18],
                ['Analog Dreams', '1490.00', 5],
                ['GROOVE Record Cleaning Kit', '650.00', 20],
                ['Anti-Static Record Brush', '420.00', 16],
                ['Archival Inner Sleeves Pack of 25', '520.00', 24],
                ['Crystal Outer Sleeves Pack of 25', '460.00', 21],
                ['Cork Turntable Mat', '780.00', 8],
                ['Adjustable Stylus Gauge', '890.00', 4],
                ['Walnut Record Display Stand', '1150.00', 6],
                ['GROOVE Canvas Record Tote', '720.00', 10],
            ];
            $rows = array_map(static fn (array $p): array => [
                'name' => $p[0], 'price' => $p[1], 'stock_quantity' => $p[2],
                'image' => null, 'is_archived' => false, 'created_at' => $now,
            ], $products);
            $this->db->table('products')->insertBatch($rows);
        }

        if ($this->db->table('customers')->countAllResults() === 0) {
            $customers = [
                ['Mara Reyes', 'mara.reyes@example.com', '+63 917 555 0101'],
                ['Paolo Santos', 'paolo.santos@example.com', '+63 917 555 0102'],
                ['Bianca Cruz', 'bianca.cruz@example.com', '+63 917 555 0103'],
                ['Luis Navarro', 'luis.navarro@example.com', '+63 917 555 0104'],
                ['Camille Flores', 'camille.flores@example.com', '+63 917 555 0105'],
                ['Enzo Villanueva', 'enzo.villanueva@example.com', '+63 917 555 0106'],
                ['Nina Mendoza', 'nina.mendoza@example.com', '+63 917 555 0107'],
                ['Gabriel Lim', 'gabriel.lim@example.com', '+63 917 555 0108'],
                ['Sofia Aquino', 'sofia.aquino@example.com', '+63 917 555 0109'],
                ['Marco Dela Cruz', 'marco.delacruz@example.com', '+63 917 555 0110'],
            ];
            $rows = array_map(static fn (array $c): array => [
                'full_name' => $c[0], 'email' => $c[1], 'phone' => $c[2],
                'is_archived' => false, 'created_at' => $now,
            ], $customers);
            $this->db->table('customers')->insertBatch($rows);
        }

        if ($this->db->table('users')->countAllResults() === 0) {
            $staff = [
                ['grooveadmin', 'Avery Mercado'],
                ['vinylclerk', 'Jordan Pascual'],
                ['stockroom', 'Riley Domingo'],
                ['weekendstaff', 'Casey Bautista'],
            ];
            $rows = array_map(static fn (array $u): array => [
                'username' => $u[0], 'full_name' => $u[1],
                'password' => password_hash(bin2hex(random_bytes(32)), PASSWORD_DEFAULT),
                'avatar' => null, 'is_archived' => false, 'created_at' => $now,
            ], $staff);
            $this->db->table('users')->insertBatch($rows);
        }
    }
}
