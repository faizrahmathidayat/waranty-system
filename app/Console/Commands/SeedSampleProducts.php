<?php

namespace App\Console\Commands;

use App\Models\ProductType;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class SeedSampleProducts extends Command
{
    protected $signature = 'app:seed-sample-products';
    protected $description = 'Tambahkan sample Product dan Product Variant tanpa menghapus data existing';

    public function handle(): int
    {
        $types = $this->resolveProductTypes();

        if (! $types) {
            return self::FAILURE;
        }

        $products = [
            ['KACA_FILM_MOBIL', 'Crystal Black', 'SolarPro', 'Chip Dyed', 60, 'KFM-CB01', 1500000],
            ['KACA_FILM_MOBIL', 'Premium Dark', 'Optima', 'High Performance', 84, 'KFM-PD02', 2500000],
            ['KACA_FILM_MOBIL', 'Ceramic Shield', 'Vantage', 'High Performance', 96, 'KFM-CS03', 3250000],
            ['KACA_FILM_MOBIL', 'Magnetron Black', 'Lumina', 'Ultra Magnetron', 120, 'KFM-MB04', 4500000],
            ['KACA_FILM_BANGUNAN', 'Solar Reflect', 'SolarPro', 'Reflective', 84, 'KFB-SR01', 350000],
            ['KACA_FILM_BANGUNAN', 'Building Ceramic', 'Vantage', 'High Performance', 120, 'KFB-BC02', 550000],
            ['PPF', 'Signature Clear', 'NexGuard', 'Signature', 60, 'PPF-SC01', 8500000],
            ['PPF', 'Imperial Guard', 'NexGuard', 'Imperial Guard', 84, 'PPF-IG02', 12000000],
            ['COATING', 'Ceramic Coating', 'Vantage', 'Ceramic Coating', 36, 'CT-CC01', 3500000],
            ['COATING', 'Sapphire Coating', 'Vantage', 'Sapphire Coating', 60, 'CT-SC02', 5500000],
        ];

        $createdProducts = 0;
        $createdVariants = 0;

        DB::transaction(function () use ($products, $types, &$createdProducts, &$createdVariants) {
            $now = now();
            $mobileProductIds = [];

            foreach ($products as [$typeKey, $name, $brand, $kind, $months, $code, $price]) {
                $productId = DB::table('products')->where('kode_produk', $code)->value('id_product');

                if (! $productId) {
                    $productId = DB::table('products')->insertGetId([
                        'id_product_type' => $types[$typeKey]->id_product_type,
                        'kode_produk' => $code,
                        'nama_produk' => $name,
                        'brand' => $brand,
                        'jenis' => $kind,
                        'harga_default' => $price,
                        'is_warranty_eligible' => true,
                        'masa_garansi_bulan' => $months,
                        'status' => 'enabled',
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]);
                    $createdProducts++;
                }

                if ($typeKey === 'KACA_FILM_MOBIL') {
                    $mobileProductIds[] = $productId;
                }
            }

            foreach ($mobileProductIds as $productId) {
                foreach (['20%', '40%', '60%', '80%'] as $variant) {
                    $variantCode = str_replace('%', '', $variant);
                    if (DB::table('product_variants')->where('id_product', $productId)->where('code', $variantCode)->exists()) {
                        continue;
                    }

                    DB::table('product_variants')->insert([
                        'id_product' => $productId,
                        'code' => $variantCode,
                        'name' => $variant,
                        'value' => $variant,
                        'unit' => 'VLT',
                        'harga_tambahan' => 0,
                        'is_active' => true,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]);
                    $createdVariants++;
                }
            }
        });

        $this->info("Selesai. Product baru: {$createdProducts}; Product Variant baru: {$createdVariants}.");
        $this->table(['Tabel', 'Jumlah Data'], [
            ['products', DB::table('products')->count()],
            ['product_variants', DB::table('product_variants')->count()],
        ]);

        return self::SUCCESS;
    }

    private function resolveProductTypes(): ?array
    {
        $definitions = [
            'KACA_FILM_MOBIL' => ['Kaca Film Mobil', 'KACA_FILM'],
            'KACA_FILM_BANGUNAN' => ['Kaca Film Bangunan', 'KACA_FILM_GEDUNG'],
            'PPF' => ['PPF', 'PPF'],
            'COATING' => ['Coating', 'COATING'],
        ];

        $resolved = [];
        foreach ($definitions as $key => [$name, $code]) {
            $type = ProductType::query()
                ->where('is_active', true)
                ->where(function ($query) use ($name, $code) {
                    $query->where('code', $code)->orWhereRaw('LOWER(name) = ?', [mb_strtolower($name)]);
                })
                ->first();

            if (! $type) {
                $this->error("Product Type aktif '{$name}' tidak ditemukan. Tidak ada Product yang ditambahkan.");
                return null;
            }

            $resolved[$key] = $type;
        }

        return $resolved;
    }
}
