<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ResetDevelopmentData extends Command
{
    protected $signature = 'app:reset-development-data {--force : Execute without interactive confirmation}';
    protected $description = 'Reset data bisnis development tanpa mengubah akun Login/User';

    public function handle(): int
    {
        if (! app()->environment(['local', 'development'])) {
            $this->error('ABORT: command hanya dapat dijalankan di environment local/development.');
            return self::FAILURE;
        }

        $database = DB::connection()->getDatabaseName();
        $this->warn("Database: {$database}");

        if (! $this->option('force') && ! $this->confirm('Kosongkan data bisnis/master/transaksi dan buat sample Customer, Vehicle, serta Building?')) {
            return self::SUCCESS;
        }

        // The list deliberately excludes users, tb_user, password_reset_tokens,
        // personal_access_tokens, migrations, and other Login infrastructure.
        $tables = [
            'payments', 'invoice_items', 'invoices',
            'warranty_vehicle_items', 'warranty_building_items', 'warranty_ppf_items', 'warranty_items',
            'warranty_vehicles', 'warranty_buildings', 'warranty_ppfs', 'warranties',
            'order_details', 'orders',
            'product_variants', 'products', 'product_types', 'treatments', 'technicians',
            'vehicles', 'buildings', 'customers',
        ];

        DB::statement('SET FOREIGN_KEY_CHECKS=0');

        try {
            DB::transaction(function () use ($tables) {
                foreach ($tables as $table) {
                    if (DB::getSchemaBuilder()->hasTable($table)) {
                        DB::table($table)->delete();
                    }
                }

                $this->seedCustomersAndAssets();
            });
        } finally {
            DB::statement('SET FOREIGN_KEY_CHECKS=1');
        }

        $this->info('Reset selesai. Akun Login/User tidak diubah.');
        $this->table(['Tabel', 'Jumlah Data'], collect([
            'customers', 'vehicles', 'buildings', 'product_types', 'products', 'product_variants',
            'treatments', 'technicians', 'orders', 'order_details', 'invoices', 'invoice_items',
            'payments', 'warranties', 'warranty_items',
        ])->map(fn (string $table) => [
            $table,
            DB::getSchemaBuilder()->hasTable($table) ? DB::table($table)->count() : '-',
        ])->all());

        return self::SUCCESS;
    }

    private function seedCustomersAndAssets(): void
    {
        $now = now();
        $customers = [
            ['Andi Wijaya', '0812-4501-8732', 'andi.wijaya@example.test', 'Jl. Melati Raya No. 18, Jakarta Selatan'],
            ['Budi Santoso', '0813-6720-1458', 'budi.santoso@example.test', 'Jl. Anggrek Barat No. 7, Jakarta Barat'],
            ['Rizky Pratama', '0812-9981-3624', 'rizky.pratama@example.test', 'Jl. Cendana Utama No. 25, Bandung'],
            ['Dimas Saputra', '0811-4827-6503', 'dimas.saputra@example.test', 'Jl. Kenanga Dalam No. 11, Tangerang'],
            ['Fajar Hidayat', '0813-5409-2186', 'fajar.hidayat@example.test', 'Jl. Pahlawan No. 42, Bekasi'],
            ['Agus Salim', '0812-7634-9051', 'agus.salim@example.test', 'Jl. Teratai Indah No. 9, Depok'],
            ['Indra Gunawan', '0811-8562-1740', 'indra.gunawan@example.test', 'Jl. Merpati Raya No. 31, Bogor'],
            ['Wahyu Hidayat', '0813-4298-6015', 'wahyu.hidayat@example.test', 'Jl. Cemara Asri No. 16, Jakarta Timur'],
            ['Vino Mahendra', '0812-3175-8429', 'vino.mahendra@example.test', 'Jl. Sutera Utama No. 28, Tangerang Selatan'],
            ['Yusuf Maulana', '0811-7340-2968', 'yusuf.maulana@example.test', 'Jl. Mawar Putih No. 5, Bekasi'],
        ];

        $customerIds = [];
        foreach ($customers as [$name, $phone, $email, $address]) {
            $customerIds[] = DB::table('customers')->insertGetId([
                'nama_customer' => $name,
                'no_hp' => $phone,
                'email' => $email,
                'alamat' => $address,
                'status' => 'enabled',
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        $vehicles = [
            [0, 'B 1842 AWI', 'Toyota', 'Fortuner', 'Hitam', 2023],
            [0, 'B 9127 WJ', 'Honda', 'Brio', 'Putih', 2022],
            [1, 'B 2716 BDS', 'Toyota', 'Avanza', 'Silver', 2021],
            [2, 'D 1488 RKP', 'Mitsubishi', 'Xpander', 'Abu-abu', 2024],
            [3, 'B 2135 DSP', 'Hyundai', 'Creta', 'Merah', 2023],
            [4, 'B 1753 FHD', 'Honda', 'HR-V', 'Putih', 2022],
            [5, 'B 2091 ASL', 'Toyota', 'Innova Zenix', 'Hitam', 2024],
            [6, 'F 1467 IGW', 'Suzuki', 'Ertiga', 'Cokelat', 2021],
            [7, 'B 2834 WHY', 'Toyota', 'Fortuner', 'Putih', 2020],
            [8, 'B 1968 VMH', 'Honda', 'Brio', 'Kuning', 2023],
            [9, 'B 3187 YML', 'Toyota', 'Avanza', 'Hitam', 2022],
        ];

        foreach ($vehicles as [$customerIndex, $plate, $brand, $model, $color, $year]) {
            DB::table('vehicles')->insert([
                'id_customer' => $customerIds[$customerIndex], 'no_polisi' => $plate,
                'merk' => $brand, 'model' => $model, 'warna' => $color, 'tahun' => $year,
                'status' => 'active', 'created_at' => $now, 'updated_at' => $now,
            ]);
        }

        $buildings = [
            [0, 'Rumah Wijaya', 'Jl. Melati Raya No. 18, Jakarta Selatan'],
            [1, 'Ruko Santoso', 'Jl. Anggrek Barat No. 7, Jakarta Barat'],
            [2, 'Kediaman Pratama', 'Jl. Cendana Utama No. 25, Bandung'],
            [3, 'Kantor Saputra', 'Jl. Kenanga Dalam No. 11, Tangerang'],
            [4, 'Rumah Hidayat', 'Jl. Pahlawan No. 42, Bekasi'],
            [5, 'Ruko Salim', 'Jl. Teratai Indah No. 9, Depok'],
            [6, 'Kediaman Gunawan', 'Jl. Merpati Raya No. 31, Bogor'],
            [7, 'Kantor Hidayat', 'Jl. Cemara Asri No. 16, Jakarta Timur'],
            [8, 'Rumah Mahendra', 'Jl. Sutera Utama No. 28, Tangerang Selatan'],
            [9, 'Ruko Maulana', 'Jl. Mawar Putih No. 5, Bekasi'],
        ];

        foreach ($buildings as [$customerIndex, $name, $address]) {
            DB::table('buildings')->insert([
                'id_customer' => $customerIds[$customerIndex], 'nama_bangunan' => $name,
                'alamat' => $address, 'status' => 'active',
                'created_at' => $now, 'updated_at' => $now,
            ]);
        }
    }
}
