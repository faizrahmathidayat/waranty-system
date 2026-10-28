<?php

namespace Database\Seeders;

use App\Models\CatalogItem;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

/**
 * Replaces everything the CMS catalog holds for LEXENT (compro-2) with the
 * LEXENT product line: 32 window-film variants (Kaca Film Mobil / Kaca Film
 * Bangunan, one category per series) and 4 PPF types.
 *
 *   php artisan db:seed --class=LexentCatalogSeeder
 *
 * Destructive on purpose: first every LEXENT-only catalog item is deleted
 * together with its images. An item that is also shown on GlossPro is kept and
 * only loses its LEXENT flag and product. Safe to run again, the result is the
 * same every time.
 */
class LexentCatalogSeeder extends Seeder
{
    /** Window-film series, keyed by the code used in VARIANTS. */
    private const SERIES = [
        'BP' => [
            'product' => 'Kaca Film Mobil',
            'name' => 'LEXENT BP',
            'label' => 'BP Series',
            'warranty_years' => 7,
            'tagline' => 'Privasi Tinggi, Low Haze',
            'description' => 'LEXENT BP hadir dengan teknologi kaca film yang dirancang untuk memberikan perlindungan optimal dari panas & sinar UV, sekaligus menghadirkan privasi tinggi dan kenyamanan berkendara setiap saat.',
            'attributes' => ['High Privacy', 'Low Haze', 'UV Protection'],
        ],
        'HT' => [
            'product' => 'Kaca Film Mobil',
            'name' => 'LEXENT HT',
            'label' => 'HT Series',
            'warranty_years' => 7,
            'tagline' => 'Nano Ceramic HD, Heat Insulation',
            'description' => 'LEXENT HT hadir dengan teknologi nano ceramic terkini yang dirancang untuk memberikan perlindungan maksimal dari panas & sinar UV, tanpa mengurangi kejernihan pandangan.',
            'attributes' => ['Ultra High Definition', 'Ultra Low Haze', 'High Level Heat Insulation', 'UV Protection'],
        ],
        'MK' => [
            'product' => 'Kaca Film Mobil',
            'name' => 'LEXENT MK',
            'label' => 'MK Series',
            'warranty_years' => 7,
            'tagline' => 'Magnetron Sputter, Non-Metal',
            'description' => 'LEXENT MK hadir dengan teknologi Magnetron Sputter yang menggunakan material berkualitas tinggi untuk memberikan perlindungan optimal dari panas & sinar UV, dengan tetap menjaga kejernihan pandangan serta tidak mengganggu sinyal HP, GPS, maupun perangkat elektronik di dalam kendaraan.',
            'attributes' => ['Extraordinary Clarity', 'Extraordinary Heat Insulation', 'Ultra-Low Haze', 'No Signal Interference'],
        ],
        'IR99' => [
            'product' => 'Kaca Film Mobil',
            'name' => 'LEXENT IR99',
            'label' => 'IR99 Series',
            'warranty_years' => 7,
            'tagline' => 'UV400 Nano Ceramic HD',
            'description' => 'LEXENT IR99 mengusung teknologi UV400 Nano Ceramic HD yang memberikan perlindungan maksimal terhadap sinar UV dan panas, dengan kejernihan visual tinggi untuk pengalaman berkendara yang lebih nyaman dan terlindungi.',
            'attributes' => ['Clear & High Transparency', 'Cooler & More Comfortable'],
        ],
        'BV' => [
            'product' => 'Kaca Film Bangunan',
            'name' => 'LEXENT Black Vision',
            'label' => 'Black Vision',
            'warranty_years' => 8,
            'tagline' => 'Privasi Tinggi & Kontrol Panas',
            'description' => 'LEXENT Black Series hadir dengan teknologi kaca film yang dirancang untuk memberikan perlindungan optimal dari panas & sinar UV, sekaligus menghadirkan privasi tinggi dan kenyamanan pada bangunan Anda.',
            'attributes' => ['High Privacy', 'Low Haze', 'UV Protection'],
        ],
        'RF' => [
            'product' => 'Kaca Film Bangunan',
            'name' => 'LEXENT Reflective',
            'label' => 'Reflective Series',
            'warranty_years' => 8,
            'tagline' => 'Reflektif, Modern & Elegan',
            'description' => 'LEXENT Reflective Series menghadirkan solusi kaca film dengan karakter reflektif yang dirancang untuk meningkatkan perlindungan dari panas matahari, memberikan privasi yang lebih baik, serta menciptakan tampilan modern dan elegan pada bangunan Anda.',
            'attributes' => ['Karakter Reflektif', 'Privasi Lebih Baik', 'Tampilan Modern'],
        ],
        'HP' => [
            'product' => 'Kaca Film Bangunan',
            'name' => 'LEXENT High Performance',
            'label' => 'High Performance',
            'warranty_years' => 8,
            'tagline' => 'Ultra HD Nano Ceramic',
            'description' => 'LEXENT High Performance hadir dengan teknologi Ultra HD Nano Ceramic terbaru yang dirancang untuk memberikan perlindungan optimal dari panas & sinar UV, dengan kejernihan tinggi untuk menghadirkan kenyamanan dan visibilitas yang lebih baik.',
            'attributes' => ['Ultra HD Clarity', 'High Visibility', 'UV Protection'],
        ],
        'UP' => [
            'product' => 'Kaca Film Bangunan',
            'name' => 'LEXENT Ultra Protect',
            'label' => 'Ultra Protect',
            'warranty_years' => 8,
            'tagline' => 'Sputter Magnetron',
            'description' => 'LEXENT Ultra Protect hadir dengan teknologi Sputter Magnetron yang dirancang untuk memberikan perlindungan optimal dari panas dan sinar UV, sekaligus membantu mengurangi paparan sinar matahari dan meningkatkan kenyamanan serta privasi pada bangunan Anda.',
            'attributes' => ['Maximum Heat Protection', 'IR 99% Protection', 'Enhanced Privacy', 'High Visibility Clarity'],
        ],
    ];

    /** [series, number, VLT, VLR, TSER, UV rejection, IRR rejection, thickness] */
    private const VARIANTS = [
        ['BP', '05', '5%', '8%', '62%', '99%', '75%', '1,8 mil'],
        ['BP', '18', '5%', '8%', '58%', '99%', '65%', '1,8 mil'],
        ['BP', '35', '5%', '8%', '53%', '99%', '63%', '1,8 mil'],
        ['HT', '08', '8%', '5%', '70%', '99%', '93%', '2 mil'],
        ['HT', '15', '15%', '5%', '68%', '99%', '90%', '2 mil'],
        ['HT', '35', '34%', '6%', '67%', '99%', '91%', '2 mil'],
        ['HT', '70', '70%', '8%', '63%', '99%', '91%', '2 mil'],
        ['MK', '08', '8%', '6%', '76%', '99%', '99%', '2 mil'],
        ['MK', '20', '20%', '6%', '74%', '99%', '99%', '2 mil'],
        ['MK', '30', '28%', '6%', '73%', '99%', '99%', '2 mil'],
        ['MK', '50', '47%', '6%', '71%', '99%', '99%', '2 mil'],
        ['MK', '65', '57%', '6%', '71%', '99%', '99%', '2 mil'],
        ['MK', '75', '68%', '6%', '71%', '99%', '99%', '2 mil'],
        ['IR99', '08', '8%', '5%', '81%', '99%', '99%', '2,2 mil'],
        ['IR99', '18', '20%', '5%', '78%', '99%', '99%', '2,2 mil'],
        ['IR99', '35', '36%', '5%', '77%', '99%', '99%', '2,2 mil'],
        ['IR99', '50', '51%', '6%', '73%', '99%', '99%', '2,2 mil'],
        ['IR99', '70', '72%', '6%', '72%', '99%', '99%', '2,2 mil'],
        ['BV', '05', '5%', '8%', '62%', '99%', '75%', '1,8 mil'],
        ['BV', '18', '5%', '8%', '58%', '99%', '65%', '1,8 mil'],
        ['BV', '35', '5%', '8%', '53%', '99%', '63%', '1,8 mil'],
        ['RF', '05', '5%', '8%', '55%', '90%', '92%', '2 mil'],
        ['HP', '08', '8%', '5%', '72%', '99%', '90%', '2 mil'],
        ['HP', '15', '15%', '5%', '70%', '99%', '90%', '2 mil'],
        ['HP', '35', '35%', '5%', '71%', '99%', '90%', '2 mil'],
        ['HP', '70', '70%', '5%', '71%', '99%', '90%', '2 mil'],
        ['UP', '08', '8%', '6%', '76%', '99%', '99%', '2 mil'],
        ['UP', '20', '20%', '6%', '74%', '99%', '99%', '2 mil'],
        ['UP', '30', '28%', '6%', '73%', '99%', '99%', '2 mil'],
        ['UP', '50', '47%', '6%', '73%', '99%', '99%', '2 mil'],
        ['UP', '65', '58%', '6%', '72%', '99%', '99%', '2 mil'],
        ['UP', '75', '69%', '6%', '72%', '99%', '99%', '2 mil'],
    ];

    private const PPF = [
        [
            'slug' => 'type-s',
            'name' => 'LEXENT PPF Type S',
            'label' => 'Type S',
            'tagline' => 'Perlindungan Solid, Perawatan Mudah',
            'description' => 'LEXENT PPF Type S melapisi cat mobil dari goresan halus, kerikil, dan noda harian, menjaga tampilan tetap solid dan mengkilap dengan perawatan yang mudah.',
            'features' => ['Exclusive Top Coat Technology', 'Excellent Impact Resistance', 'Excellent Stain Resistance and Easy Maintenance'],
            'thickness' => '8 Mil',
            'warranty_years' => 10,
            'finish' => 'Glossy',
            'attributes' => [
                ['label' => 'TPU', 'rating' => 'Top Grade'],
                ['label' => 'Glossy', 'rating' => 'Excellent'],
                ['label' => 'Self-Healing', 'rating' => 'Excellent'],
                ['label' => 'Hydrophobic', 'rating' => 'Excellent'],
            ],
        ],
        [
            'slug' => 'type-t-plus',
            'name' => 'LEXENT PPF Type T Plus',
            'label' => 'Type T Plus',
            'tagline' => 'Self-Healing Coating, Proteksi Ekstra',
            'description' => 'LEXENT PPF Type T Plus menghadirkan lapisan top coat self-healing yang menyamarkan baret halus secara otomatis, memberi proteksi ekstra untuk cat mobil kesayangan Anda.',
            'features' => ['Excellent Top Coat Layer', 'High Protection', 'Self-Healing Coating'],
            'thickness' => '8,5 Mil',
            'warranty_years' => 10,
            'finish' => 'Glossy',
            'attributes' => [
                ['label' => 'TPU', 'rating' => 'High Grade'],
                ['label' => 'Glossy', 'rating' => 'Excellent'],
                ['label' => 'Self-Healing', 'rating' => 'Excellent'],
                ['label' => 'Hydrophobic', 'rating' => 'Excellent'],
            ],
        ],
        [
            'slug' => 'type-l',
            'name' => 'LEXENT PPF Type L',
            'label' => 'Type L',
            'tagline' => 'Super Gloss Look, Kilau Maksimal',
            'description' => 'LEXENT PPF Type L berbasis TPU kelas atas dengan hasil akhir super glossy, memberi tampilan cat mobil yang lebih hidup sekaligus perlindungan self-healing dari goresan ringan.',
            'features' => ['High Grade TPU Based', 'Self-Healing', 'Super Gloss Look'],
            'thickness' => '8 Mil',
            'warranty_years' => 7,
            'finish' => 'Glossy',
            'attributes' => [
                ['label' => 'TPU', 'rating' => 'High Grade'],
                ['label' => 'Glossy', 'rating' => 'Excellent'],
                ['label' => 'Self-Healing', 'rating' => 'Excellent'],
                ['label' => 'Hydrophobic', 'rating' => 'Excellent'],
            ],
        ],
        [
            'slug' => 'type-l-matte',
            'name' => 'LEXENT PPF Type L Matte',
            'label' => 'Type L Matte',
            'tagline' => 'Excellent Matte Look, Tampilan Doff Premium',
            'description' => 'LEXENT PPF Type L Matte menghadirkan tampilan doff premium dengan proteksi self-healing yang sama tangguhnya, untuk pemilik mobil yang menginginkan gaya matte tanpa mengorbankan perlindungan cat.',
            'features' => ['High Grade TPU Based', 'Self-Healing', 'Excellent Matte Look'],
            'thickness' => '8 Mil',
            'warranty_years' => 7,
            'finish' => 'Matte',
            'attributes' => [
                ['label' => 'TPU', 'rating' => 'High Grade'],
                ['label' => 'Matte', 'rating' => 'Excellent'],
                ['label' => 'Self-Healing', 'rating' => 'Excellent'],
                ['label' => 'Hydrophobic', 'rating' => 'Excellent'],
            ],
        ],
    ];

    public function run(): void
    {
        DB::transaction(function () {
            $this->clearLexentItems();

            foreach (self::VARIANTS as $row) {
                CatalogItem::create($this->windowFilmItem($row));
            }
            foreach (self::PPF as $type) {
                CatalogItem::create($this->ppfItem($type));
            }
        });

        if ($this->command) {
            $this->command->info('Katalog LEXENT diisi ulang: ' . count(self::VARIANTS) . ' varian kaca film + ' . count(self::PPF) . ' tipe PPF.');
        }
    }

    private function clearLexentItems(): void
    {
        foreach (CatalogItem::where('show_on_lexent', true)->with('media')->get() as $item) {
            if ($item->show_on_glosspro) {
                $item->update(['show_on_lexent' => false, 'product' => null]);
                continue;
            }

            foreach ($item->media as $media) {
                Storage::disk('public')->delete(array_filter([$media->path, $media->thumbnail_path]));
                $media->delete();
            }
            $item->delete();
        }
    }

    private function windowFilmItem(array $row): array
    {
        [$code, $number, $vlt, $vlr, $tser, $uv, $irr, $thickness] = $row;
        $series = self::SERIES[$code];

        $vltNumber = (int) $vlt;
        $darkness = $vltNumber <= 15 ? 'Sangat gelap — privasi maksimal'
            : ($vltNumber <= 40 ? 'Gelap sedang — seimbang' : 'Terang — visibilitas tinggi');

        return $this->base([
            'title' => $series['name'] . ' ' . $number,
            'slug' => 'lexent-' . strtolower($code) . '-' . $number,
            'excerpt' => $series['tagline'] . '. ' . $darkness . '.',
            'body' => '<p>' . e($series['description']) . '</p>'
                . '<p><strong>Karakter:</strong></p>' . $this->list($series['attributes'])
                . '<p>VLT ' . e($vlt) . ' dengan VLR ' . e($vlr) . ' — ' . e($darkness) . '.</p>',
            'category' => $series['label'],
            'product' => $series['product'],
            'spec_highlights' => [
                ['label' => 'VLT', 'value' => $vlt],
                ['label' => 'TSER', 'value' => $tser],
                ['label' => 'UV Rejection', 'value' => $uv],
                ['label' => 'IRR Rejection', 'value' => $irr],
                ['label' => 'Ketebalan', 'value' => $thickness],
                ['label' => 'Garansi', 'value' => $series['warranty_years'] . ' Tahun'],
            ],
        ]);
    }

    private function ppfItem(array $type): array
    {
        $attributes = array_map(fn ($a) => $a['label'] . ' — ' . $a['rating'], $type['attributes']);

        return $this->base([
            'title' => $type['name'],
            'slug' => 'lexent-ppf-' . $type['slug'],
            'excerpt' => $type['tagline'],
            'body' => '<p>' . e($type['description']) . '</p>'
                . '<p><strong>Keunggulan:</strong></p>' . $this->list($type['features'])
                . '<p><strong>Spesifikasi:</strong></p>' . $this->list($attributes),
            'category' => $type['label'],
            'product' => 'PPF',
            'spec_highlights' => [
                ['label' => 'Ketebalan', 'value' => $type['thickness']],
                ['label' => 'Finishing', 'value' => $type['finish']],
                ['label' => 'Garansi', 'value' => $type['warranty_years'] . ' Tahun'],
            ],
        ]);
    }

    private function base(array $item): array
    {
        return $item + [
            'status' => 'published',
            'published_at' => now(),
            'show_on_glosspro' => false,
            'show_on_lexent' => true,
        ];
    }

    private function list(array $items): string
    {
        return '<ul>' . implode('', array_map(fn ($i) => '<li>' . e($i) . '</li>', $items)) . '</ul>';
    }
}
