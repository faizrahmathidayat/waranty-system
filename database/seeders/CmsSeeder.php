<?php

namespace Database\Seeders;

use App\Models\Article;
use App\Models\CatalogItem;
use App\Models\PortfolioItem;
use Illuminate\Database\Seeder;

/**
 * Demo CMS content for both consumer sites — 15 published rows per site for
 * each content type (Article, CatalogItem "Sorotan Produk", PortfolioItem).
 * No media is attached; upload cover images via the dashboard afterwards.
 *
 * Run standalone: php artisan db:seed --class=CmsSeeder
 */
class CmsSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedArticles();
        $this->seedCatalogItems();
        $this->seedPortfolioItems();
    }

    private function seedArticles(): void
    {
        $rows = array_merge(
            $this->tagRows($this->glossproArticles(), 'glosspro'),
            $this->tagRows($this->lexentArticles(), 'lexent')
        );

        foreach ($rows as $row) {
            Article::updateOrCreate(['slug' => $row['slug']], [
                'title' => $row['title'],
                'excerpt' => $row['excerpt'],
                'body' => $row['body'],
                'category' => $row['category'],
                'status' => 'published',
                'published_at' => now()->subDays($row['days_ago']),
                'show_on_glosspro' => $row['site'] === 'glosspro',
                'show_on_lexent' => $row['site'] === 'lexent',
            ]);
        }
    }

    private function seedCatalogItems(): void
    {
        $rows = array_merge(
            $this->tagRows($this->glossproCatalog(), 'glosspro'),
            $this->tagRows($this->lexentCatalog(), 'lexent')
        );

        foreach ($rows as $row) {
            CatalogItem::updateOrCreate(['slug' => $row['slug']], [
                'title' => $row['title'],
                'excerpt' => $row['excerpt'],
                'body' => $row['body'],
                'category' => $row['category'],
                'status' => 'published',
                'published_at' => now()->subDays($row['days_ago']),
                'show_on_glosspro' => $row['site'] === 'glosspro',
                'show_on_lexent' => $row['site'] === 'lexent',
                'spec_highlights' => $row['spec_highlights'],
            ]);
        }
    }

    private function seedPortfolioItems(): void
    {
        $rows = array_merge(
            $this->tagRows($this->glossproPortfolio(), 'glosspro'),
            $this->tagRows($this->lexentPortfolio(), 'lexent')
        );

        foreach ($rows as $row) {
            PortfolioItem::updateOrCreate(['slug' => $row['slug']], [
                'title' => $row['title'],
                'excerpt' => $row['excerpt'],
                'body' => $row['body'],
                'category' => $row['category'],
                'status' => 'published',
                'published_at' => now()->subDays($row['days_ago']),
                'show_on_glosspro' => $row['site'] === 'glosspro',
                'show_on_lexent' => $row['site'] === 'lexent',
                'location' => $row['location'],
            ]);
        }
    }

    private function tagRows(array $rows, string $site): array
    {
        foreach ($rows as &$row) {
            $row['site'] = $site;
        }

        return $rows;
    }

    /**
     * ---------------------------------------------------------------------
     * GlossPro (compro-1) — Car Coating, Detailing, Window Film, PPF.
     * ---------------------------------------------------------------------
     */
    private function glossproArticles(): array
    {
        return [
            [
                'slug' => 'perbedaan-nano-ceramic-dan-graphene-coating',
                'title' => 'Kenali Perbedaan Nano Ceramic Coating dan Graphene Coating',
                'category' => 'Car Coating',
                'excerpt' => 'Nano Ceramic dan Graphene sama-sama melapisi cat mobil, namun berbeda dari sisi ketahanan panas, masa pakai, dan harga.',
                'body' => '<p>Nano Ceramic Coating adalah lapisan pelindung berbahan dasar silika yang membentuk ikatan kimia dengan cat mobil, menghasilkan kilau tinggi dan sifat hydrophobic yang membuat air serta debu sulit menempel. Daya tahannya berkisar 2-5 tahun tergantung varian dan perawatan harian.</p><p>Graphene Coating adalah pengembangan dari ceramic dengan tambahan struktur graphene yang lebih stabil terhadap panas dan bersifat anti-statis, sehingga debu lebih sulit menempel dan lapisan bertahan hingga 5-7 tahun. Untuk kendaraan yang sering terpapar sinar matahari langsung, Graphene Coating umumnya lebih direkomendasikan.</p>',
                'days_ago' => 12,
            ],
            [
                'slug' => 'berapa-lama-ceramic-coating-bertahan',
                'title' => 'Berapa Lama Ceramic Coating Bertahan? Ini Faktor yang Mempengaruhi',
                'category' => 'Car Coating',
                'excerpt' => 'Daya tahan ceramic coating tidak hanya ditentukan oleh jenis produk, tapi juga kebiasaan mencuci dan kondisi parkir kendaraan sehari-hari.',
                'body' => '<p>Secara teori, coating berbasis Nano Ceramic bertahan 2-3 tahun dan varian HD bisa mencapai 3-5 tahun, tetapi angka ini sangat dipengaruhi oleh kondisi pemakaian nyata di jalan. Mobil yang lebih sering diparkir di bawah terik matahari atau jarang dicuci dengan benar akan kehilangan efek hydrophobic lebih cepat.</p><p>Faktor lain yang memperpendek usia coating adalah pencucian dengan sabun yang tidak ramah coating, penggunaan lap kasar, serta paparan kontaminan seperti getah pohon dan kotoran burung yang dibiarkan terlalu lama menempel.</p>',
                'days_ago' => 25,
            ],
            [
                'slug' => 'mitos-dan-fakta-coating-anti-gores',
                'title' => 'Mitos vs Fakta Seputar Coating Anti Gores',
                'category' => 'Car Coating',
                'excerpt' => 'Banyak yang mengira ceramic coating membuat cat mobil kebal dari segala jenis goresan. Berikut fakta sebenarnya di balik klaim tersebut.',
                'body' => '<p>Mitos yang paling sering beredar adalah coating membuat cat mobil sama sekali tidak bisa tergores. Faktanya, coating dengan hardness 9H hanya menambah lapisan pelindung tipis di atas cat yang menahan swirl mark dan baret halus akibat pencucian, bukan proteksi terhadap benturan kerikil atau goresan dalam.</p><p>Untuk proteksi terhadap chip dan baret dari kerikil di jalan tol, kombinasi coating dengan Paint Protection Film (PPF) pada area rawan seperti bumper depan dan kap mesin adalah pendekatan yang lebih tepat.</p>',
                'days_ago' => 40,
            ],
            [
                'slug' => 'tips-merawat-mobil-ber-coating',
                'title' => '5 Tips Merawat Mobil Ber-Coating Agar Kilau Tahan Lama',
                'category' => 'Car Coating',
                'excerpt' => 'Perawatan yang tepat setelah coating menentukan seberapa lama kilau dan efek hydrophobic bisa bertahan pada kendaraan Anda.',
                'body' => '<p>Gunakan sabun khusus dengan pH seimbang, hindari mencuci di bawah terik matahari langsung, dan selalu gunakan teknik dua ember (two-bucket method) agar kotoran tidak tergores ulang ke permukaan cat.</p><p>Lakukan pencucian rutin minimal dua minggu sekali, segera bersihkan kotoran burung atau getah pohon yang menempel, dan hindari mesin cuci mobil otomatis dengan sikat kasar yang berisiko merusak lapisan coating.</p>',
                'days_ago' => 55,
            ],
            [
                'slug' => 'kesalahan-mencuci-mobil-merusak-coating',
                'title' => '5 Kesalahan Umum Saat Mencuci Mobil yang Merusak Lapisan Coating',
                'category' => 'Car Coating',
                'excerpt' => 'Teknik mencuci yang keliru bisa mempercepat munculnya swirl mark dan mengikis lapisan pelindung coating lebih cepat dari seharusnya.',
                'body' => '<p>Mencuci mobil dengan spons yang sama untuk seluruh bodi tanpa membilas kotoran, menggunakan sabun cuci piring, dan mengelap bodi dalam keadaan kering adalah beberapa kebiasaan yang mempercepat munculnya baret halus di atas lapisan coating.</p><p>Kesalahan lain adalah membiarkan air sabun mengering sendiri di bawah matahari serta menggunakan lap microfiber yang sudah kotor dan tidak dicuci setelah pemakaian sebelumnya.</p>',
                'days_ago' => 70,
            ],
            [
                'slug' => 'waktu-tepat-detailing-interior-mobil',
                'title' => 'Kapan Waktu yang Tepat untuk Detailing Interior Mobil?',
                'category' => 'Detailing',
                'excerpt' => 'Detailing interior bukan hanya soal kebersihan, tapi juga menjaga nilai jual dan kenyamanan kabin dalam jangka panjang.',
                'body' => '<p>Idealnya, detailing interior menyeluruh dilakukan setiap 3-6 bulan sekali, atau lebih sering bila kendaraan sering mengangkut anak kecil, hewan peliharaan, maupun digunakan untuk aktivitas luar ruangan yang membawa banyak debu dan kotoran ke dalam kabin.</p><p>Tanda-tanda kabin sudah butuh detailing antara lain jok yang mulai kusam, karpet berdebu meski sudah divakum, serta bau apek yang tidak hilang dengan pengharum biasa.</p>',
                'days_ago' => 8,
            ],
            [
                'slug' => 'menghilangkan-bau-apek-kabin-mobil',
                'title' => 'Cara Menghilangkan Bau Apek di Kabin Mobil Secara Tuntas',
                'category' => 'Detailing',
                'excerpt' => 'Steam sanitizing dan ozone treatment menjadi solusi menyeluruh untuk menetralkan bau apek yang membandel di jok dan karpet mobil.',
                'body' => '<p>Bau apek pada kabin umumnya berasal dari kelembapan yang terperangkap di jok, karpet, dan sistem AC — pengharum mobil biasa hanya menutupi bau tanpa membunuh sumbernya.</p><p>Steam sanitizing membersihkan sela-sela jok dan karpet dari kotoran serta bakteri penyebab bau, sementara ozone treatment menetralkan udara di seluruh kabin termasuk saluran AC, memberi hasil yang jauh lebih tahan lama dibanding pengharum semprot biasa.</p>',
                'days_ago' => 20,
            ],
            [
                'slug' => 'pentingnya-engine-bay-detailing-sebelum-jual-mobil',
                'title' => 'Pentingnya Engine Bay Detailing Sebelum Mobil Dijual',
                'category' => 'Detailing',
                'excerpt' => 'Ruang mesin yang bersih memberi kesan mobil terawat dan bisa meningkatkan daya tawar saat proses jual-beli kendaraan bekas.',
                'body' => '<p>Calon pembeli mobil bekas kerap membuka kap mesin sebagai salah satu indikator seberapa terawat kendaraan tersebut. Ruang mesin yang berdebu dan berminyak bisa menimbulkan kesan kendaraan kurang terawat, meski kondisi mekanisnya sebenarnya baik.</p><p>Proses engine bay detailing meliputi degreasing untuk mengangkat oli dan debu membandel, dilanjutkan dressing anti debu yang membuat ruang mesin terlihat rapi lebih lama tanpa meninggalkan residu berlebih.</p>',
                'days_ago' => 33,
            ],
            [
                'slug' => 'musim-hujan-lindungi-cat-mobil-dari-jamur-air',
                'title' => 'Musim Hujan Tiba, Begini Cara Melindungi Cat Mobil dari Jamur Air',
                'category' => 'Detailing',
                'excerpt' => 'Air hujan yang dibiarkan mengering di permukaan cat bisa meninggalkan water spot dan jamur. Kenali cara mencegahnya sejak dini.',
                'body' => '<p>Air hujan mengandung mineral dan polutan udara yang, jika dibiarkan mengering di bawah sinar matahari, dapat meninggalkan bercak water spot bahkan jamur air pada permukaan cat, terutama di kaca dan panel horizontal seperti kap mesin dan atap.</p><p>Membilas mobil sesegera mungkin setelah terkena hujan, serta lapisan coating dengan efek hydrophobic, membantu air meluncur turun lebih cepat sehingga risiko water spot dan jamur bisa ditekan secara signifikan.</p>',
                'days_ago' => 48,
            ],
            [
                'slug' => 'panduan-memilih-vlt-kaca-film-sesuai-regulasi',
                'title' => 'Panduan Memilih VLT Kaca Film Mobil yang Sesuai Regulasi',
                'category' => 'Window Film',
                'excerpt' => 'Tingkat kegelapan kaca film tidak boleh sembarangan dipilih — ada aturan lalu lintas yang perlu diperhatikan sebelum pemasangan.',
                'body' => '<p>VLT (Visible Light Transmission) menunjukkan seberapa banyak cahaya yang bisa masuk melalui kaca film — semakin kecil angkanya, semakin gelap tampilannya. Kaca depan umumnya memiliki batas VLT minimum yang lebih longgar dibanding kaca samping dan belakang menurut regulasi lalu lintas yang berlaku.</p><p>Tim GlossPro membantu merekomendasikan VLT yang sesuai ketentuan sekaligus kebutuhan kenyamanan, misalnya VLT 60% untuk kaca depan yang tetap terang namun tetap menolak panas dan UV secara signifikan.</p>',
                'days_ago' => 15,
            ],
            [
                'slug' => 'kaca-film-ceramic-vs-carbon',
                'title' => 'Kaca Film Ceramic vs Carbon, Mana yang Lebih Cocok untuk Mobil Anda?',
                'category' => 'Window Film',
                'excerpt' => 'Ceramic dan carbon sama-sama populer, tapi keduanya punya karakter penolakan panas dan stabilitas warna yang berbeda.',
                'body' => '<p>Kaca film Ceramic menggunakan partikel keramik non-konduktif yang mampu menolak panas dan sinar UV hingga 99% tanpa mengganggu sinyal elektronik di dalam kendaraan, cocok untuk kegelapan rendah hingga tinggi.</p><p>Kaca film Carbon memiliki karakter warna yang lebih stabil dan tidak memudar menjadi keunguan seiring waktu seperti film dye biasa, dengan penampilan matte yang elegan, meski penolakan inframerahnya umumnya sedikit di bawah varian ceramic.</p>',
                'days_ago' => 28,
            ],
            [
                'slug' => 'kenapa-kaca-film-mobil-bisa-menggelembung',
                'title' => 'Kenapa Kaca Film Mobil Bisa Menggelembung? Begini Cara Mencegahnya',
                'category' => 'Window Film',
                'excerpt' => 'Gelembung pada kaca film umumnya muncul akibat kualitas material atau proses pemasangan yang kurang tepat.',
                'body' => '<p>Gelembung pada kaca film biasanya disebabkan oleh lapisan lem yang mulai terurai akibat kualitas material rendah, atau proses pemasangan yang tidak menggunakan larutan aplikasi khusus sehingga udara terjebak di bawah film.</p><p>Pemasangan yang presisi — mulai dari pembersihan kaca, pemotongan pola mengikuti bentuk kaca, hingga masa curing yang cukup sebelum kaca dibuka — sangat menentukan hasil akhir yang bebas gelembung dan tahan lama hingga masa garansi berakhir.</p>',
                'days_ago' => 62,
            ],
            [
                'slug' => 'self-healing-ppf-bagaimana-cara-kerjanya',
                'title' => 'Self-Healing PPF, Bagaimana Cara Kerjanya?',
                'category' => 'Paint Protection Film',
                'excerpt' => 'Teknologi self-healing pada PPF memungkinkan baret halus menghilang dengan sendirinya. Simak penjelasan ilmiah di baliknya.',
                'body' => '<p>PPF berbahan TPU (Thermoplastic Polyurethane) memiliki lapisan top coat elastomer yang bersifat fleksibel. Saat terkena baret halus, struktur molekul lapisan ini dapat kembali ke bentuk semula ketika dipanaskan, baik oleh sinar matahari maupun air hangat.</p><p>Proses inilah yang membuat baret ringan akibat pencucian atau ranting pohon perlahan "menutup" sendiri dalam hitungan menit hingga jam, menjaga tampilan film tetap mulus tanpa perlu poles ulang seperti pada cat biasa.</p>',
                'days_ago' => 10,
            ],
            [
                'slug' => 'ppf-matte-vs-gloss-perbedaan-dan-perawatan',
                'title' => 'PPF Matte vs Gloss, Kenali Perbedaan dan Cara Merawatnya',
                'category' => 'Paint Protection Film',
                'excerpt' => 'Selain tampilan akhir yang berbeda, PPF Matte dan Gloss juga membutuhkan pendekatan perawatan yang sedikit berbeda.',
                'body' => '<p>PPF Gloss mempertahankan tampilan mengkilap asli cat mobil sekaligus menambah kedalaman warna, sementara PPF Matte mengubah tampilan kendaraan menjadi doff premium tanpa mengorbankan proteksi self-healing di baliknya.</p><p>Perawatan PPF Matte perlu lebih berhati-hati terhadap produk wax atau sealant berbahan silikon yang bisa meninggalkan bercak mengkilap pada permukaan doff, sedangkan PPF Gloss lebih fleksibel terhadap berbagai produk perawatan cat pada umumnya.</p>',
                'days_ago' => 37,
            ],
            [
                'slug' => 'waktu-ideal-pasang-ppf-mobil-baru',
                'title' => 'Waktu Ideal Pemasangan PPF Setelah Mobil Baru Dibeli',
                'category' => 'Paint Protection Film',
                'excerpt' => 'Semakin cepat PPF terpasang setelah mobil keluar dari diler, semakin optimal cat asli terlindungi dari risiko baret harian.',
                'body' => '<p>Cat mobil baru dari pabrik umumnya belum pernah terpapar kontaminan jalan seperti kerikil, aspal, atau baret ringan saat proses pengiriman dan penyimpanan di diler. Memasang PPF sesegera mungkin membantu menjaga kondisi cat tetap orisinal sejak awal masa pakai kendaraan.</p><p>Sebelum instalasi, tim GlossPro tetap melakukan paint correction ringan untuk memastikan permukaan benar-benar bersih dari kontaminan halus, sehingga film dapat menempel sempurna tanpa menjebak kotoran di baliknya.</p>',
                'days_ago' => 5,
            ],
        ];
    }

    private function glossproCatalog(): array
    {
        return [
            [
                'slug' => 'sorotan-nano-ceramic-coating',
                'title' => 'Nano Ceramic Coating',
                'category' => 'Car Coating',
                'excerpt' => 'Kilau Maksimal, Proteksi Harian — lapisan ceramic dasar dengan kejernihan tinggi dan efek hydrophobic untuk pemakaian harian.',
                'body' => '<p>Lapisan ceramic dasar dengan kejernihan tinggi dan efek hydrophobic untuk pemakaian harian, cocok untuk pemilik kendaraan yang menginginkan proteksi dasar dengan kilau yang terjaga.</p>',
                'spec_highlights' => [
                    ['label' => 'Hardness', 'value' => '9H'],
                    ['label' => 'Hydrophobic Angle', 'value' => '100°'],
                    ['label' => 'Durability', 'value' => '2-3 Thn'],
                    ['label' => 'Gloss Enhancement', 'value' => 'Tinggi'],
                ],
                'days_ago' => 3,
            ],
            [
                'slug' => 'sorotan-nano-ceramic-hd',
                'title' => 'Nano Ceramic HD',
                'category' => 'Car Coating',
                'excerpt' => 'Ultra Clarity, Proteksi Menengah — formula HD dengan kejernihan optik lebih tinggi, cocok untuk warna cat solid maupun metalik.',
                'body' => '<p>Formula HD dengan kejernihan optik lebih tinggi, cocok untuk warna cat solid maupun metalik, memberikan hasil akhir yang lebih dalam dan tajam dibanding varian dasar.</p>',
                'spec_highlights' => [
                    ['label' => 'Hardness', 'value' => '9H'],
                    ['label' => 'Hydrophobic Angle', 'value' => '105°'],
                    ['label' => 'Durability', 'value' => '3-5 Thn'],
                    ['label' => 'Gloss Enhancement', 'value' => 'Sangat Tinggi'],
                ],
                'days_ago' => 9,
            ],
            [
                'slug' => 'sorotan-graphene-coating',
                'title' => 'Graphene Coating',
                'category' => 'Car Coating',
                'excerpt' => 'Proteksi Maksimal, Tahan Panas — struktur graphene menambah resistansi panas dan sifat anti-statis sehingga debu lebih sulit menempel.',
                'body' => '<p>Struktur graphene menambah resistansi panas dan sifat anti-statis sehingga debu lebih sulit menempel, menjadikannya pilihan terbaik untuk kendaraan yang sering terpapar sinar matahari langsung.</p>',
                'spec_highlights' => [
                    ['label' => 'Hardness', 'value' => '9H+'],
                    ['label' => 'Hydrophobic Angle', 'value' => '>110°'],
                    ['label' => 'Durability', 'value' => '5-7 Thn'],
                    ['label' => 'Thermal Resistance', 'value' => 'Tinggi'],
                ],
                'days_ago' => 16,
            ],
            [
                'slug' => 'sorotan-exterior-detailing',
                'title' => 'Exterior Detailing',
                'category' => 'Detailing',
                'excerpt' => 'Clay Bar, Machine Polish, Swirl Removal — decontamination dan koreksi cat ringan-menengah untuk tampilan luar mengkilap.',
                'body' => '<p>Decontamination, koreksi cat ringan-menengah, dan proteksi wax/sealant untuk tampilan luar mengkilap, cocok sebagai perawatan berkala menjaga kondisi cat tetap prima.</p>',
                'spec_highlights' => [
                    ['label' => 'Durasi', 'value' => '3-5 Jam'],
                    ['label' => 'Termasuk', 'value' => 'Clay + Polish'],
                    ['label' => 'Cocok Untuk', 'value' => 'Perawatan Berkala'],
                ],
                'days_ago' => 22,
            ],
            [
                'slug' => 'sorotan-interior-detailing',
                'title' => 'Interior Detailing',
                'category' => 'Detailing',
                'excerpt' => 'Steam Sanitizing & Leather Conditioning — pembersihan dalam kabin dengan sanitasi uap dan ozone treatment untuk kabin higienis.',
                'body' => '<p>Pembersihan dalam kabin, jok, karpet, hingga dashboard, dengan sanitasi uap dan ozone treatment, ideal untuk kabin yang berbau apek atau kotor akibat pemakaian harian.</p>',
                'spec_highlights' => [
                    ['label' => 'Durasi', 'value' => '3-4 Jam'],
                    ['label' => 'Termasuk', 'value' => 'Steam + Ozone'],
                    ['label' => 'Cocok Untuk', 'value' => 'Kabin Berbau/Kotor'],
                ],
                'days_ago' => 30,
            ],
            [
                'slug' => 'sorotan-engine-bay-detailing',
                'title' => 'Engine Bay Detailing',
                'category' => 'Detailing',
                'excerpt' => 'Degreasing & Dressing — pembersihan ruang mesin dari debu dan oli membandel, dilanjut dressing anti debu.',
                'body' => '<p>Pembersihan ruang mesin dari debu dan oli membandel, dilanjut dressing anti debu, cocok dilakukan sebelum servis berkala maupun sebelum kendaraan dijual.</p>',
                'spec_highlights' => [
                    ['label' => 'Durasi', 'value' => '1-2 Jam'],
                    ['label' => 'Termasuk', 'value' => 'Degrease + Dress'],
                    ['label' => 'Cocok Untuk', 'value' => 'Sebelum Servis/Jual'],
                ],
                'days_ago' => 44,
            ],
            [
                'slug' => 'sorotan-glass-detailing',
                'title' => 'Glass Detailing',
                'category' => 'Detailing',
                'excerpt' => 'Water Spot Removal & Hydrophobic Coat — menghilangkan water spot dan baret halus di kaca, ditutup lapisan hydrophobic tipis.',
                'body' => '<p>Menghilangkan water spot dan baret halus di kaca, ditutup lapisan hydrophobic tipis, memberikan visibilitas lebih jernih terutama saat berkendara di musim hujan.</p>',
                'spec_highlights' => [
                    ['label' => 'Durasi', 'value' => '1-2 Jam'],
                    ['label' => 'Termasuk', 'value' => 'Polish + Coat'],
                    ['label' => 'Cocok Untuk', 'value' => 'Kaca Buram/Berkerak'],
                ],
                'days_ago' => 58,
            ],
            [
                'slug' => 'sorotan-ceramic-film-vlt-5',
                'title' => 'Ceramic Film VLT 5%',
                'category' => 'Window Film',
                'excerpt' => 'Privasi Maksimal — tingkat kegelapan tertinggi untuk privasi maksimal pada kaca belakang dan samping belakang.',
                'body' => '<p>Tingkat kegelapan tertinggi untuk privasi maksimal pada kaca belakang dan samping belakang, tetap menolak panas dan UV secara signifikan berkat teknologi ceramic.</p>',
                'spec_highlights' => [
                    ['label' => 'VLT', 'value' => '5%'],
                    ['label' => 'UV Rejection', 'value' => '99%'],
                    ['label' => 'IR Rejection', 'value' => '95%'],
                    ['label' => 'Tipe', 'value' => 'Ceramic'],
                ],
                'days_ago' => 6,
            ],
            [
                'slug' => 'sorotan-ceramic-film-vlt-20',
                'title' => 'Ceramic Film VLT 20%',
                'category' => 'Window Film',
                'excerpt' => 'Gelap Seimbang — kombinasi privasi dan visibilitas yang seimbang, favorit untuk kaca samping depan.',
                'body' => '<p>Kombinasi privasi dan visibilitas yang seimbang, favorit untuk kaca samping depan, cocok untuk pengendara yang tetap ingin visibilitas baik saat malam hari.</p>',
                'spec_highlights' => [
                    ['label' => 'VLT', 'value' => '20%'],
                    ['label' => 'UV Rejection', 'value' => '99%'],
                    ['label' => 'IR Rejection', 'value' => '93%'],
                    ['label' => 'Tipe', 'value' => 'Ceramic'],
                ],
                'days_ago' => 19,
            ],
            [
                'slug' => 'sorotan-carbon-film-vlt-40',
                'title' => 'Carbon Film VLT 40%',
                'category' => 'Window Film',
                'excerpt' => 'Terang & Anti Silau — karakter carbon yang stabil warnanya dengan VLT lebih terang.',
                'body' => '<p>Karakter carbon yang stabil warnanya (tidak memudar/berubah ungu) dengan VLT lebih terang, cocok untuk pemilik kendaraan yang mengutamakan visibilitas tinggi.</p>',
                'spec_highlights' => [
                    ['label' => 'VLT', 'value' => '40%'],
                    ['label' => 'UV Rejection', 'value' => '99%'],
                    ['label' => 'IR Rejection', 'value' => '85%'],
                    ['label' => 'Tipe', 'value' => 'Carbon'],
                ],
                'days_ago' => 34,
            ],
            [
                'slug' => 'sorotan-ceramic-film-vlt-60',
                'title' => 'Ceramic Film VLT 60%',
                'category' => 'Window Film',
                'excerpt' => 'Cahaya Alami Maksimal — VLT tinggi untuk kaca depan, proteksi UV & panas tanpa mengurangi cahaya alami secara signifikan.',
                'body' => '<p>VLT tinggi untuk kaca depan — proteksi UV & panas tanpa mengurangi cahaya alami secara signifikan, ideal dipasang pada kaca depan sesuai batas regulasi lalu lintas.</p>',
                'spec_highlights' => [
                    ['label' => 'VLT', 'value' => '60%'],
                    ['label' => 'UV Rejection', 'value' => '99%'],
                    ['label' => 'IR Rejection', 'value' => '80%'],
                    ['label' => 'Tipe', 'value' => 'Ceramic'],
                ],
                'days_ago' => 51,
            ],
            [
                'slug' => 'sorotan-ppf-full-body-gloss',
                'title' => 'PPF Full Body Gloss',
                'category' => 'Paint Protection Film',
                'excerpt' => 'Proteksi Total, Kilau Natural — menutupi seluruh panel bodi dengan finish gloss yang mempertahankan tampilan cat asli.',
                'body' => '<p>Menutupi seluruh panel bodi dengan finish gloss yang mempertahankan tampilan cat asli, memberikan proteksi menyeluruh dari baret halus dan chip di jalan tol.</p>',
                'spec_highlights' => [
                    ['label' => 'Cakupan', 'value' => 'Full Body'],
                    ['label' => 'Finish', 'value' => 'Gloss'],
                    ['label' => 'Ketebalan', 'value' => '180 mic'],
                    ['label' => 'Garansi', 'value' => '10 Thn'],
                ],
                'days_ago' => 4,
            ],
            [
                'slug' => 'sorotan-ppf-full-body-matte',
                'title' => 'PPF Full Body Matte',
                'category' => 'Paint Protection Film',
                'excerpt' => 'Proteksi Total, Tampilan Doff — mengubah tampilan kendaraan menjadi matte sekaligus melindungi cat asli di baliknya.',
                'body' => '<p>Mengubah tampilan kendaraan menjadi matte sekaligus melindungi cat asli di baliknya, pilihan populer bagi pemilik kendaraan yang menginginkan tampilan berbeda tanpa mengecat ulang.</p>',
                'spec_highlights' => [
                    ['label' => 'Cakupan', 'value' => 'Full Body'],
                    ['label' => 'Finish', 'value' => 'Matte'],
                    ['label' => 'Ketebalan', 'value' => '180 mic'],
                    ['label' => 'Garansi', 'value' => '10 Thn'],
                ],
                'days_ago' => 27,
            ],
            [
                'slug' => 'sorotan-ppf-partial-front-kit',
                'title' => 'PPF Partial Front Kit',
                'category' => 'Paint Protection Film',
                'excerpt' => 'Proteksi Area Rawan Chip — melindungi bumper depan, kap mesin, fender, dan spion.',
                'body' => '<p>Melindungi bumper depan, kap mesin, fender, dan spion — area paling rawan baret kerikil, menjadi opsi hemat bagi yang ingin proteksi fokus di area kritis.</p>',
                'spec_highlights' => [
                    ['label' => 'Cakupan', 'value' => 'Bumper, Hood, Fender'],
                    ['label' => 'Finish', 'value' => 'Gloss/Matte'],
                    ['label' => 'Ketebalan', 'value' => '150 mic'],
                    ['label' => 'Garansi', 'value' => '7 Thn'],
                ],
                'days_ago' => 41,
            ],
            [
                'slug' => 'sorotan-ppf-satin-full-body',
                'title' => 'PPF Satin Full Body',
                'category' => 'Paint Protection Film',
                'excerpt' => 'Efek Semi-Doff Premium — finish satin di antara gloss dan matte, memberi kesan premium yang lebih jarang ditemui.',
                'body' => '<p>Finish satin di antara gloss dan matte, memberi kesan premium yang lebih jarang ditemui, sekaligus tetap membawa proteksi self-healing penuh dari cat asli kendaraan.</p>',
                'spec_highlights' => [
                    ['label' => 'Cakupan', 'value' => 'Full Body'],
                    ['label' => 'Finish', 'value' => 'Satin'],
                    ['label' => 'Ketebalan', 'value' => '180 mic'],
                    ['label' => 'Garansi', 'value' => '10 Thn'],
                ],
                'days_ago' => 65,
            ],
        ];
    }

    private function glossproPortfolio(): array
    {
        return [
            [
                'slug' => 'ceramic-coating-toyota-alphard-jakarta',
                'title' => 'Ceramic Coating Toyota Alphard — Kilau Tahan Lama',
                'category' => 'Car Coating',
                'location' => 'Jakarta Pusat',
                'excerpt' => 'Nano Ceramic HD dikerjakan penuh pada Toyota Alphard untuk mempertahankan kilau cat hitam solid milik pelanggan di Jakarta.',
                'body' => '<p>Toyota Alphard hitam solid ini menjalani proses Nano Ceramic HD lengkap, mulai dari decontamination, paint correction ringan, hingga aplikasi coating lapis demi lapis di ruang bebas debu.</p><p>Hasil akhirnya menghadirkan kilau dalam yang lebih tajam serta efek hydrophobic yang membuat perawatan harian jauh lebih mudah bagi pemilik kendaraan.</p>',
                'days_ago' => 6,
            ],
            [
                'slug' => 'graphene-coating-mercedes-benz-c300-jakarta',
                'title' => 'Graphene Coating Mercedes-Benz C300',
                'category' => 'Car Coating',
                'location' => 'Jakarta Selatan',
                'excerpt' => 'Graphene Coating dipilih pemilik Mercedes-Benz C300 untuk proteksi maksimal terhadap panas dan paparan matahari harian.',
                'body' => '<p>Mengingat kendaraan sering diparkir outdoor, pemilik memilih Graphene Coating yang menawarkan resistansi panas lebih tinggi dibanding varian ceramic biasa.</p><p>Proses pengerjaan mencakup paint correction menyeluruh sebelum aplikasi coating, menghasilkan tampilan cat metalik yang lebih dalam dan tahan hingga 5-7 tahun ke depan.</p>',
                'days_ago' => 18,
            ],
            [
                'slug' => 'full-detailing-honda-jazz-bandung',
                'title' => 'Full Detailing Restorasi Honda Jazz Lawas',
                'category' => 'Detailing',
                'location' => 'Bandung',
                'excerpt' => 'Honda Jazz keluaran lama direstorasi tampilannya melalui paket exterior dan interior detailing lengkap di workshop Bandung.',
                'body' => '<p>Kendaraan berusia lebih dari 8 tahun ini menjalani clay bar, machine polish, dan swirl removal untuk mengembalikan kilau cat yang sudah kusam akibat pemakaian harian.</p><p>Bagian interior turut dibersihkan menyeluruh dengan steam sanitizing, memberikan hasil akhir yang membuat kabin terasa jauh lebih segar dan higienis.</p>',
                'days_ago' => 31,
            ],
            [
                'slug' => 'interior-detailing-toyota-innova-zenix-surabaya',
                'title' => 'Interior Detailing & Sanitizing Toyota Innova Zenix',
                'category' => 'Detailing',
                'location' => 'Surabaya',
                'excerpt' => 'Kabin Toyota Innova Zenix disanitasi menyeluruh menggunakan steam sanitizing dan ozone treatment untuk menghilangkan bau apek.',
                'body' => '<p>Pemilik kendaraan yang sering digunakan untuk perjalanan keluarga mengeluhkan bau apek di kabin, terutama pada bagian karpet dan jok baris kedua.</p><p>Setelah steam sanitizing dan ozone treatment, kabin kembali segar tanpa bau, dengan jok dan dashboard yang terlihat seperti baru.</p>',
                'days_ago' => 46,
            ],
            [
                'slug' => 'kaca-film-ceramic-vlt-20-range-rover-sport-denpasar',
                'title' => 'Kaca Film Ceramic VLT 20% Range Rover Sport',
                'category' => 'Window Film',
                'location' => 'Denpasar',
                'excerpt' => 'Range Rover Sport dipasangi kaca film Ceramic VLT 20% untuk keseimbangan privasi dan penolakan panas tropis Bali.',
                'body' => '<p>Mengingat cuaca Denpasar yang panas sepanjang tahun, pemilik memilih Ceramic Film VLT 20% yang menghadirkan penolakan UV hingga 99% tanpa membuat kabin terasa terlalu gelap.</p><p>Pemasangan dilakukan dengan pola potong presisi mengikuti bentuk kaca asli kendaraan, memastikan hasil akhir tanpa gelembung.</p>',
                'days_ago' => 60,
            ],
            [
                'slug' => 'carbon-film-suzuki-ertiga-jakarta',
                'title' => 'Pemasangan Carbon Film Suzuki Ertiga',
                'category' => 'Window Film',
                'location' => 'Jakarta Timur',
                'excerpt' => 'Suzuki Ertiga keluarga dipasangi Carbon Film VLT 40% untuk visibilitas tinggi namun tetap sejuk saat perjalanan jauh.',
                'body' => '<p>Sebagai kendaraan keluarga yang sering digunakan untuk perjalanan luar kota, pemilik memilih Carbon Film VLT 40% yang tetap terang namun mampu menolak panas secara signifikan.</p><p>Karakter warna carbon yang stabil membuat tampilan kaca film tidak akan memudar keunguan meski terpapar matahari bertahun-tahun.</p>',
                'days_ago' => 75,
            ],
            [
                'slug' => 'ppf-full-body-gloss-porsche-911-jakarta',
                'title' => 'PPF Full Body Gloss Porsche 911',
                'category' => 'Paint Protection Film',
                'location' => 'Jakarta Selatan',
                'excerpt' => 'Porsche 911 baru menjalani instalasi PPF Full Body Gloss guna menjaga cat orisinal tetap sempurna sejak awal masa pakai.',
                'body' => '<p>Sebagai kendaraan sport premium, pemilik ingin memastikan cat asli terlindungi sejak kendaraan baru keluar dari diler, sebelum sempat terpapar baret jalanan.</p><p>Instalasi dilakukan panel demi panel dengan pola potong digital presisi, menghasilkan tampilan yang nyaris tidak terlihat sambungan filmnya.</p>',
                'days_ago' => 13,
            ],
            [
                'slug' => 'ppf-partial-front-kit-pajero-sport-bandung',
                'title' => 'PPF Partial Front Kit Mitsubishi Pajero Sport',
                'category' => 'Paint Protection Film',
                'location' => 'Bandung',
                'excerpt' => 'Area bumper depan, kap mesin, dan fender Pajero Sport dilindungi PPF Partial Front Kit dari risiko chip perjalanan luar kota.',
                'body' => '<p>Pemilik kendaraan yang sering bepergian ke luar kota memilih paket Partial Front Kit untuk melindungi area paling rawan terkena kerikil dari kendaraan di depannya.</p><p>Setelah pemasangan, area kritis tersebut kini terlindungi film TPU self-healing yang mampu menyamarkan baret halus akibat perjalanan jarak jauh.</p>',
                'days_ago' => 39,
            ],
            [
                'slug' => 'nano-ceramic-coating-mazda-cx5-surabaya',
                'title' => 'Nano Ceramic Coating Mazda CX-5',
                'category' => 'Car Coating',
                'location' => 'Surabaya',
                'excerpt' => 'Mazda CX-5 dengan warna Soul Red Crystal mendapat Nano Ceramic HD untuk mempertahankan kedalaman warna khas Mazda.',
                'body' => '<p>Warna Soul Red Crystal yang menjadi ciri khas Mazda memerlukan perlakuan khusus agar kedalaman warnanya tetap terjaga — Nano Ceramic HD dipilih karena kejernihan optiknya yang lebih tinggi.</p><p>Hasil akhir menunjukkan warna merah yang semakin hidup dengan efek hydrophobic yang memudahkan perawatan harian di tengah cuaca Surabaya yang panas.</p>',
                'days_ago' => 53,
            ],
            [
                'slug' => 'engine-bay-detailing-fortuner-denpasar',
                'title' => 'Engine Bay Detailing Toyota Fortuner Sebelum Dijual',
                'category' => 'Detailing',
                'location' => 'Denpasar',
                'excerpt' => 'Ruang mesin Toyota Fortuner dibersihkan menyeluruh sebagai bagian dari persiapan kendaraan sebelum dijual kembali.',
                'body' => '<p>Pemilik kendaraan yang berencana menjual Fortuner miliknya ingin memastikan seluruh bagian kendaraan, termasuk ruang mesin, terlihat terawat saat diperiksa calon pembeli.</p><p>Proses degreasing mengangkat debu dan oli membandel di sekitar mesin, dilanjutkan dressing anti debu yang membuat ruang mesin tampak rapi lebih lama.</p>',
                'days_ago' => 67,
            ],
            [
                'slug' => 'ppf-matte-honda-civic-type-r-jakarta',
                'title' => 'PPF Matte Wrap Look Honda Civic Type R',
                'category' => 'Paint Protection Film',
                'location' => 'Jakarta Utara',
                'excerpt' => 'Honda Civic Type R tampil beda dengan PPF Full Body Matte, memberi tampilan doff premium tanpa mengecat ulang bodi.',
                'body' => '<p>Pemilik kendaraan menginginkan tampilan matte tanpa harus melakukan pengecatan ulang yang berisiko menurunkan nilai jual kembali kendaraan performa tinggi ini.</p><p>PPF Full Body Matte menjadi solusi ideal — mengubah tampilan sekaligus tetap melindungi cat asli dengan teknologi self-healing di baliknya.</p>',
                'days_ago' => 21,
            ],
            [
                'slug' => 'kaca-film-ceramic-vlt-60-bmw-x3-bandung',
                'title' => 'Kaca Film Ceramic VLT 60% BMW X3',
                'category' => 'Window Film',
                'location' => 'Bandung',
                'excerpt' => 'Kaca depan BMW X3 dipasangi Ceramic Film VLT 60% untuk menjaga visibilitas malam hari tanpa mengorbankan proteksi panas.',
                'body' => '<p>Pemilik kendaraan yang sering berkendara malam hari memprioritaskan visibilitas tinggi, sehingga Ceramic Film VLT 60% dipilih untuk kaca depan sesuai regulasi lalu lintas yang berlaku.</p><p>Meski VLT tergolong terang, penolakan UV tetap mencapai 99% dengan IR rejection yang signifikan, menjaga kabin tetap sejuk di siang hari.</p>',
                'days_ago' => 82,
            ],
            [
                'slug' => 'ceramic-coating-wuling-almaz-hybrid-surabaya',
                'title' => 'Ceramic Coating Wuling Almaz Hybrid',
                'category' => 'Car Coating',
                'location' => 'Surabaya',
                'excerpt' => 'Wuling Almaz Hybrid mendapat Nano Ceramic Coating dasar untuk menjaga kilau cat kendaraan yang baru dibeli.',
                'body' => '<p>Sebagai kendaraan yang baru dibeli, pemilik ingin segera melindungi cat orisinal dari paparan matahari dan hujan sejak dini dengan lapisan ceramic dasar.</p><p>Proses decontamination dan aplikasi Nano Ceramic Coating memberikan efek hydrophobic yang memudahkan perawatan harian di tengah aktivitas perkotaan.</p>',
                'days_ago' => 90,
            ],
            [
                'slug' => 'glass-detailing-hyundai-palisade-denpasar',
                'title' => 'Glass Detailing & Water Spot Removal Hyundai Palisade',
                'category' => 'Detailing',
                'location' => 'Denpasar',
                'excerpt' => 'Water spot membandel pada kaca Hyundai Palisade berhasil dihilangkan melalui layanan glass detailing.',
                'body' => '<p>Kaca depan dan kaca samping kendaraan menunjukkan bercak water spot akibat air hujan yang dibiarkan mengering, mengurangi kejernihan pandangan saat berkendara.</p><p>Setelah proses polish dan aplikasi lapisan hydrophobic tipis, kaca kembali jernih dan air lebih mudah meluncur turun saat hujan turun kembali.</p>',
                'days_ago' => 98,
            ],
            [
                'slug' => 'ppf-satin-lexus-rx-jakarta',
                'title' => 'PPF Satin Full Body Lexus RX',
                'category' => 'Paint Protection Film',
                'location' => 'Jakarta Selatan',
                'excerpt' => 'Lexus RX tampil premium dengan PPF Satin Full Body, memberi efek semi-doff yang jarang ditemui di jalanan.',
                'body' => '<p>Pemilik kendaraan menginginkan tampilan yang berbeda dari mayoritas kendaraan sejenis di jalan, tanpa kehilangan proteksi menyeluruh terhadap cat asli kendaraan premium ini.</p><p>PPF Satin Full Body dipilih karena menghadirkan kesan mewah di antara gloss dan matte, lengkap dengan garansi hingga 10 tahun.</p>',
                'days_ago' => 105,
            ],
        ];
    }

    /**
     * ---------------------------------------------------------------------
     * LEXENT (compro-2) — Window Film Automotive & Building, Paint Protection Film.
     * ---------------------------------------------------------------------
     */
    private function lexentArticles(): array
    {
        return [
            [
                'slug' => 'apa-itu-infrared-rejection-kaca-film-mobil',
                'title' => 'Apa Itu Infrared Rejection dan Kenapa Penting untuk Kaca Film Mobil?',
                'category' => 'Automotive',
                'excerpt' => 'Infrared Rejection menentukan seberapa efektif kaca film menahan panas matahari masuk ke dalam kabin kendaraan.',
                'body' => '<p>Infrared Rejection (IRR) mengukur kemampuan kaca film menolak sinar inframerah, komponen sinar matahari yang paling bertanggung jawab atas panas yang dirasakan di dalam kabin, berbeda dari cahaya tampak yang diukur oleh VLT.</p><p>Seri LEXENT MK dan IR99 menonjol dengan IRR hingga 99%, menjadikan kabin terasa jauh lebih sejuk meski kendaraan terparkir di bawah terik matahari dalam waktu lama.</p>',
                'days_ago' => 7,
            ],
            [
                'slug' => 'kaca-film-non-metal-bebas-gangguan-sinyal',
                'title' => 'Kaca Film Non-Metal, Solusi Bebas Gangguan Sinyal HP dan GPS',
                'category' => 'Automotive',
                'excerpt' => 'Kaca film berbasis metal kerap mengganggu sinyal HP dan GPS di dalam kabin. Kenali solusi non-metal berteknologi Magnetron Sputter.',
                'body' => '<p>Kaca film generasi lama umumnya menggunakan partikel metal untuk menolak panas, namun partikel ini berpotensi memantulkan gelombang sinyal HP, GPS, hingga radio di dalam kabin kendaraan.</p><p>LEXENT MK menggunakan teknologi Magnetron Sputter dengan material non-metal berkualitas tinggi, tetap menghadirkan penolakan panas signifikan tanpa mengganggu perangkat elektronik di dalam mobil.</p>',
                'days_ago' => 21,
            ],
            [
                'slug' => 'panduan-memilih-vlt-kaca-film-mobil',
                'title' => 'Panduan Memilih VLT Kaca Film Mobil Sesuai Kebutuhan',
                'category' => 'Automotive',
                'excerpt' => 'Pemilihan VLT kaca film sebaiknya mempertimbangkan kebutuhan privasi, visibilitas malam hari, dan regulasi lalu lintas.',
                'body' => '<p>VLT yang lebih rendah seperti 5-8% memberikan privasi maksimal, cocok untuk kaca belakang dan samping belakang, sementara VLT lebih tinggi seperti 60-70% lebih cocok untuk kaca depan agar visibilitas tetap optimal saat malam hari.</p><p>Seluruh seri LEXENT automotive — BP, HT, MK, dan IR99 — tersedia dalam beberapa pilihan VLT sehingga dapat disesuaikan per posisi kaca dan preferensi pengendara.</p>',
                'days_ago' => 35,
            ],
            [
                'slug' => 'magnetron-sputter-vs-nano-ceramic',
                'title' => 'Magnetron Sputter vs Nano Ceramic, Apa Bedanya?',
                'category' => 'Automotive',
                'excerpt' => 'Dua teknologi andalan LEXENT ini punya keunggulan berbeda — kenali karakteristik masing-masing sebelum memilih.',
                'body' => '<p>Teknologi Magnetron Sputter pada seri MK menghasilkan lapisan non-metal presisi tinggi yang stabil dan bebas gangguan sinyal, sementara Nano Ceramic pada seri HT dan IR99 mengandalkan partikel keramik berukuran nano untuk kejernihan optik maksimal.</p><p>Keduanya menolak UV hingga 99%, namun Magnetron Sputter unggul di stabilitas jangka panjang sedangkan Nano Ceramic HD unggul di kejernihan pandangan tanpa distorsi warna.</p>',
                'days_ago' => 49,
            ],
            [
                'slug' => 'kenapa-kaca-film-mobil-berubah-warna-ungu',
                'title' => 'Kenapa Kaca Film Mobil Bisa Berubah Warna Ungu?',
                'category' => 'Automotive',
                'excerpt' => 'Kaca film berkualitas rendah rentan berubah warna keunguan setelah beberapa tahun terpapar sinar matahari.',
                'body' => '<p>Perubahan warna menjadi keunguan umumnya terjadi pada kaca film berbahan dye yang kualitas pewarnanya rendah, di mana pigmen warna terurai akibat paparan sinar UV terus-menerus.</p><p>Seri LEXENT menggunakan teknologi ceramic dan sputter non-dye yang jauh lebih stabil terhadap paparan UV jangka panjang, sehingga warna film tetap konsisten sepanjang masa garansi hingga 7 tahun.</p>',
                'days_ago' => 63,
            ],
            [
                'slug' => 'manfaat-kaca-film-uv400-kesehatan-kulit',
                'title' => 'Manfaat Kaca Film UV400 untuk Kesehatan Kulit Pengemudi',
                'category' => 'Automotive',
                'excerpt' => 'Paparan sinar UV dalam kabin kendaraan jangka panjang dapat memengaruhi kesehatan kulit. Kaca film UV400 hadir sebagai solusi.',
                'body' => '<p>Sinar UV yang masuk melalui kaca kendaraan dapat menyebabkan penuaan dini pada kulit, terutama bagi pengendara yang sering menghabiskan waktu lama di jalan, seperti supir profesional atau pengguna mobil harian jarak jauh.</p><p>LEXENT IR99 mengusung teknologi UV400 Nano Ceramic HD yang menolak sinar UV hingga 99%, membantu melindungi kulit penumpang sekaligus menjaga interior kendaraan dari efek pemudaran akibat sinar matahari.</p>',
                'days_ago' => 4,
            ],
            [
                'slug' => 'kaca-film-gedung-investasi-efisiensi-energi',
                'title' => 'Kaca Film Gedung: Investasi Efisiensi Energi Jangka Panjang',
                'category' => 'Building',
                'excerpt' => 'Kaca film gedung bukan sekadar estetika, tapi juga investasi yang menekan biaya operasional pendinginan ruangan.',
                'body' => '<p>Beban pendinginan menjadi salah satu komponen biaya operasional terbesar pada gedung komersial, terutama di iklim tropis. Kaca film dengan TSER (Total Solar Energy Rejected) tinggi membantu mengurangi panas yang masuk melalui fasad kaca.</p><p>Seri LEXENT Ultra Protect misalnya mampu menolak hingga 76% panas matahari, yang secara langsung menurunkan beban kerja sistem AC gedung dan berdampak pada efisiensi konsumsi energi jangka panjang.</p>',
                'days_ago' => 11,
            ],
            [
                'slug' => 'black-vision-vs-reflective-fasad-kantor',
                'title' => 'Black Vision vs Reflective, Mana yang Cocok untuk Fasad Kantor Anda?',
                'category' => 'Building',
                'excerpt' => 'Dua seri kaca film gedung LEXENT ini punya karakter visual dan performa berbeda untuk kebutuhan fasad bangunan.',
                'body' => '<p>LEXENT Black Vision menghadirkan privasi tinggi dengan tampilan gelap solid, cocok untuk gedung yang mengutamakan kerahasiaan aktivitas di dalam ruangan sekaligus estetika minimalis modern.</p><p>LEXENT Reflective Series memberikan karakter reflektif yang memantulkan cahaya matahari, menciptakan kesan mewah dan elegan pada fasad, sekaligus menolak infrared hingga 92% untuk kenyamanan suhu dalam ruangan.</p>',
                'days_ago' => 26,
            ],
            [
                'slug' => 'cara-kerja-kaca-film-kurangi-beban-ac-gedung',
                'title' => 'Cara Kerja Kaca Film Mengurangi Beban Pendinginan AC Gedung',
                'category' => 'Building',
                'excerpt' => 'Kaca film gedung bekerja dengan menolak sebagian besar energi panas matahari sebelum masuk ke dalam ruangan.',
                'body' => '<p>Tanpa kaca film, sinar matahari yang menembus kaca akan diserap oleh permukaan dalam ruangan dan dilepaskan kembali sebagai panas, memaksa sistem AC bekerja lebih keras untuk menjaga suhu ruangan tetap nyaman.</p><p>Lapisan kaca film LEXENT menolak sebagian besar energi panas ini sebelum sempat masuk, sehingga suhu ruangan lebih stabil dan konsumsi listrik untuk pendinginan dapat ditekan secara signifikan.</p>',
                'days_ago' => 42,
            ],
            [
                'slug' => 'pentingnya-sertifikasi-tser-kaca-film-gedung',
                'title' => 'Pentingnya Sertifikasi TSER pada Kaca Film Gedung Komersial',
                'category' => 'Building',
                'excerpt' => 'TSER menjadi salah satu indikator utama performa kaca film gedung yang perlu diperhatikan sebelum memilih produk.',
                'body' => '<p>TSER (Total Solar Energy Rejected) mengukur seberapa banyak total energi matahari — termasuk cahaya tampak, inframerah, dan UV — yang berhasil ditolak oleh kaca film, menjadikannya indikator performa yang lebih menyeluruh dibanding VLT saja.</p><p>Seluruh seri kaca film gedung LEXENT, dari Black Vision hingga Ultra Protect, memiliki data TSER yang terukur dan konsisten, membantu konsultan maupun kontraktor bangunan menghitung proyeksi efisiensi energi secara akurat.</p>',
                'days_ago' => 57,
            ],
            [
                'slug' => 'tren-fasad-kaca-reflektif-gedung-perkantoran',
                'title' => 'Tren Fasad Kaca Reflektif pada Gedung Perkantoran Modern',
                'category' => 'Building',
                'excerpt' => 'Fasad kaca reflektif semakin populer sebagai identitas visual gedung perkantoran modern di kota-kota besar.',
                'body' => '<p>Selain fungsi teknis menolak panas, kaca film reflektif turut memberi karakter visual yang berbeda pada eksterior gedung — memantulkan langit dan lingkungan sekitar, menciptakan kesan modern dan premium.</p><p>LEXENT Reflective Series banyak dipilih pengembang gedung perkantoran dan pusat perbelanjaan yang ingin menonjolkan identitas visual sekaligus tetap mendapatkan manfaat penolakan panas dan UV.</p>',
                'days_ago' => 71,
            ],
            [
                'slug' => 'perawatan-kaca-film-gedung-agar-awet',
                'title' => 'Perawatan Kaca Film Gedung Agar Awet hingga 8 Tahun',
                'category' => 'Building',
                'excerpt' => 'Perawatan sederhana dan rutin membantu kaca film gedung mencapai masa pakai maksimal sesuai garansi resmi.',
                'body' => '<p>Membersihkan kaca film dengan kain lembut dan cairan pembersih kaca berbahan dasar air, tanpa bahan kimia keras atau alat pembersih kasar, membantu menjaga lapisan film tetap dalam kondisi optimal.</p><p>Inspeksi berkala pada tepi film juga disarankan untuk memastikan tidak ada bagian yang mulai terkelupas, sehingga performa penolakan panas dan UV tetap terjaga hingga masa garansi 8 tahun berakhir.</p>',
                'days_ago' => 88,
            ],
            [
                'slug' => 'perbedaan-kaca-film-automotive-dan-building',
                'title' => 'Perbedaan Kaca Film Automotive dan Building, Bisa Saling Tukar?',
                'category' => 'Automotive',
                'excerpt' => 'Meski sama-sama kaca film, seri automotive dan building dirancang untuk kebutuhan dan permukaan kaca yang berbeda.',
                'body' => '<p>Kaca film automotive dirancang lentur agar mengikuti lekukan kaca kendaraan yang melengkung, sementara kaca film building umumnya dipasang pada permukaan kaca datar berukuran besar dengan ketebalan dan metode aplikasi yang berbeda.</p><p>Karena perbedaan karakter permukaan dan tuntutan performa ini, seri automotive seperti BP/HT/MK/IR99 dan seri building seperti Black Vision/Reflective/High Performance/Ultra Protect tidak direkomendasikan untuk saling dipertukarkan penggunaannya.</p>',
                'days_ago' => 14,
            ],
            [
                'slug' => 'mengenal-teknologi-ultra-hd-nano-ceramic',
                'title' => 'Mengenal Teknologi Ultra HD Nano Ceramic pada Kaca Film',
                'category' => 'Building',
                'excerpt' => 'Teknologi Ultra HD Nano Ceramic menjadi andalan seri LEXENT High Performance untuk kejernihan dan proteksi panas.',
                'body' => '<p>Ultra HD Nano Ceramic menggunakan partikel keramik berukuran sangat halus yang tersebar merata pada lapisan film, menghasilkan kejernihan visual tinggi tanpa distorsi warna maupun efek berkabut.</p><p>Pada seri LEXENT High Performance, teknologi ini dipadukan dengan penolakan panas hingga 72% dan infrared rejection hingga 90%, menjadikannya pilihan seimbang antara estetika dan performa untuk gedung komersial maupun residensial.</p>',
                'days_ago' => 78,
            ],
            [
                'slug' => 'tanda-kaca-film-mobil-harus-diganti',
                'title' => '5 Tanda Kaca Film Mobil Anda Harus Segera Diganti',
                'category' => 'Automotive',
                'excerpt' => 'Kaca film yang sudah menurun performanya perlu segera diganti agar proteksi panas dan UV tetap optimal.',
                'body' => '<p>Beberapa tanda kaca film perlu diganti antara lain warna yang mulai memudar atau berubah keunguan, muncul gelembung di beberapa titik, tepi film yang mulai terkelupas, kabin yang terasa lebih panas dari biasanya, dan usia pemakaian yang sudah melewati masa garansi.</p><p>Mengganti dengan seri LEXENT yang lebih baru memastikan kendaraan kembali mendapat proteksi UV dan panas sesuai spesifikasi terkini, sekaligus tampilan kaca yang lebih jernih dari sebelumnya.</p>',
                'days_ago' => 1,
            ],
        ];
    }

    private function lexentCatalog(): array
    {
        return [
            [
                'slug' => 'sorotan-bp-05',
                'title' => 'LEXENT BP 05',
                'category' => 'BP Series',
                'excerpt' => 'Privasi Tinggi, Low Haze. Sangat gelap — privasi maksimal, dengan UV Rejection 99% dan Heat Rejection 62%.',
                'body' => '<p>LEXENT BP hadir dengan teknologi kaca film yang dirancang untuk memberikan perlindungan optimal dari panas &amp; sinar UV, sekaligus menghadirkan privasi tinggi dan kenyamanan berkendara setiap saat. Varian VLT 5% ini cocok untuk kaca belakang dan samping belakang yang mengutamakan privasi maksimal.</p>',
                'spec_highlights' => [
                    ['label' => 'VLT', 'value' => '5%'],
                    ['label' => 'Heat Rejection (TSER)', 'value' => '62%'],
                    ['label' => 'UV Rejection', 'value' => '99%'],
                    ['label' => 'Infrared Rejection', 'value' => '75%'],
                    ['label' => 'Ketebalan', 'value' => '1,8 mil'],
                    ['label' => 'Garansi', 'value' => '7 Tahun'],
                ],
                'days_ago' => 5,
            ],
            [
                'slug' => 'sorotan-bp-35',
                'title' => 'LEXENT BP 35',
                'category' => 'BP Series',
                'excerpt' => 'Privasi Tinggi, Low Haze. Sangat gelap — privasi maksimal, dengan UV Rejection 99% dan Heat Rejection 53%.',
                'body' => '<p>LEXENT BP hadir dengan teknologi kaca film yang dirancang untuk memberikan perlindungan optimal dari panas &amp; sinar UV, sekaligus menghadirkan privasi tinggi dan kenyamanan berkendara setiap saat. Varian ini tetap mempertahankan karakter privasi tinggi khas seri BP.</p>',
                'spec_highlights' => [
                    ['label' => 'VLT', 'value' => '5%'],
                    ['label' => 'Heat Rejection (TSER)', 'value' => '53%'],
                    ['label' => 'UV Rejection', 'value' => '99%'],
                    ['label' => 'Infrared Rejection', 'value' => '63%'],
                    ['label' => 'Ketebalan', 'value' => '1,8 mil'],
                    ['label' => 'Garansi', 'value' => '7 Tahun'],
                ],
                'days_ago' => 17,
            ],
            [
                'slug' => 'sorotan-ht-08',
                'title' => 'LEXENT HT 08',
                'category' => 'HT Series',
                'excerpt' => 'Nano Ceramic HD, Heat Insulation. Sangat gelap — privasi maksimal, dengan Infrared Rejection 93%.',
                'body' => '<p>LEXENT HT hadir dengan teknologi nano ceramic terkini yang dirancang untuk memberikan perlindungan maksimal dari panas &amp; sinar UV, tanpa mengurangi kejernihan pandangan. Varian VLT 8% menghadirkan infrared rejection tertinggi di seri HT.</p>',
                'spec_highlights' => [
                    ['label' => 'VLT', 'value' => '8%'],
                    ['label' => 'Heat Rejection (TSER)', 'value' => '70%'],
                    ['label' => 'UV Rejection', 'value' => '99%'],
                    ['label' => 'Infrared Rejection', 'value' => '93%'],
                    ['label' => 'Ketebalan', 'value' => '2 mil'],
                    ['label' => 'Garansi', 'value' => '7 Tahun'],
                ],
                'days_ago' => 29,
            ],
            [
                'slug' => 'sorotan-ht-70',
                'title' => 'LEXENT HT 70',
                'category' => 'HT Series',
                'excerpt' => 'Nano Ceramic HD, Heat Insulation. Terang — visibilitas tinggi, cocok untuk kaca depan.',
                'body' => '<p>LEXENT HT hadir dengan teknologi nano ceramic terkini yang dirancang untuk memberikan perlindungan maksimal dari panas &amp; sinar UV, tanpa mengurangi kejernihan pandangan. Varian VLT 70% ideal dipasang di kaca depan sesuai regulasi lalu lintas.</p>',
                'spec_highlights' => [
                    ['label' => 'VLT', 'value' => '70%'],
                    ['label' => 'Heat Rejection (TSER)', 'value' => '63%'],
                    ['label' => 'UV Rejection', 'value' => '99%'],
                    ['label' => 'Infrared Rejection', 'value' => '91%'],
                    ['label' => 'Ketebalan', 'value' => '2 mil'],
                    ['label' => 'Garansi', 'value' => '7 Tahun'],
                ],
                'days_ago' => 45,
            ],
            [
                'slug' => 'sorotan-mk-08',
                'title' => 'LEXENT MK 08',
                'category' => 'MK Series',
                'excerpt' => 'Magnetron Sputter, Non-Metal. Sangat gelap — privasi maksimal, tanpa gangguan sinyal HP maupun GPS.',
                'body' => '<p>LEXENT MK hadir dengan teknologi Magnetron Sputter yang menggunakan material berkualitas tinggi untuk memberikan perlindungan optimal dari panas &amp; sinar UV, dengan tetap menjaga kejernihan pandangan serta tidak mengganggu sinyal HP, GPS, maupun perangkat elektronik di dalam kendaraan.</p>',
                'spec_highlights' => [
                    ['label' => 'VLT', 'value' => '8%'],
                    ['label' => 'Heat Rejection (TSER)', 'value' => '76%'],
                    ['label' => 'UV Rejection', 'value' => '99%'],
                    ['label' => 'Infrared Rejection', 'value' => '99%'],
                    ['label' => 'Ketebalan', 'value' => '2 mil'],
                    ['label' => 'Garansi', 'value' => '7 Tahun'],
                ],
                'days_ago' => 2,
            ],
            [
                'slug' => 'sorotan-mk-75',
                'title' => 'LEXENT MK 75',
                'category' => 'MK Series',
                'excerpt' => 'Magnetron Sputter, Non-Metal. Terang — visibilitas tinggi, tetap bebas gangguan sinyal elektronik.',
                'body' => '<p>LEXENT MK hadir dengan teknologi Magnetron Sputter yang menggunakan material berkualitas tinggi untuk memberikan perlindungan optimal dari panas &amp; sinar UV. Varian VLT 68% cocok untuk kaca depan yang tetap membutuhkan infrared rejection tinggi.</p>',
                'spec_highlights' => [
                    ['label' => 'VLT', 'value' => '68%'],
                    ['label' => 'Heat Rejection (TSER)', 'value' => '71%'],
                    ['label' => 'UV Rejection', 'value' => '99%'],
                    ['label' => 'Infrared Rejection', 'value' => '99%'],
                    ['label' => 'Ketebalan', 'value' => '2 mil'],
                    ['label' => 'Garansi', 'value' => '7 Tahun'],
                ],
                'days_ago' => 23,
            ],
            [
                'slug' => 'sorotan-ir99-08',
                'title' => 'LEXENT IR99 08',
                'category' => 'IR99 Series',
                'excerpt' => 'UV400 Nano Ceramic HD. Sangat gelap — privasi maksimal, dengan Heat Rejection tertinggi di seri IR99.',
                'body' => '<p>LEXENT IR99 mengusung teknologi UV400 Nano Ceramic HD yang memberikan perlindungan maksimal terhadap sinar UV dan panas, dengan kejernihan visual tinggi untuk pengalaman berkendara yang lebih nyaman dan terlindungi.</p>',
                'spec_highlights' => [
                    ['label' => 'VLT', 'value' => '8%'],
                    ['label' => 'Heat Rejection (TSER)', 'value' => '81%'],
                    ['label' => 'UV Rejection', 'value' => '99%'],
                    ['label' => 'Infrared Rejection', 'value' => '99%'],
                    ['label' => 'Ketebalan', 'value' => '2,2 mil'],
                    ['label' => 'Garansi', 'value' => '7 Tahun'],
                ],
                'days_ago' => 38,
            ],
            [
                'slug' => 'sorotan-ir99-70',
                'title' => 'LEXENT IR99 70',
                'category' => 'IR99 Series',
                'excerpt' => 'UV400 Nano Ceramic HD. Terang — visibilitas tinggi, tetap dengan Infrared Rejection 99%.',
                'body' => '<p>LEXENT IR99 mengusung teknologi UV400 Nano Ceramic HD yang memberikan perlindungan maksimal terhadap sinar UV dan panas. Varian VLT 72% cocok untuk kaca depan dengan tetap mempertahankan infrared rejection tertinggi di kelasnya.</p>',
                'spec_highlights' => [
                    ['label' => 'VLT', 'value' => '72%'],
                    ['label' => 'Heat Rejection (TSER)', 'value' => '72%'],
                    ['label' => 'UV Rejection', 'value' => '99%'],
                    ['label' => 'Infrared Rejection', 'value' => '99%'],
                    ['label' => 'Ketebalan', 'value' => '2,2 mil'],
                    ['label' => 'Garansi', 'value' => '7 Tahun'],
                ],
                'days_ago' => 54,
            ],
            [
                'slug' => 'sorotan-bv-05',
                'title' => 'LEXENT Black Vision 05',
                'category' => 'Black Vision',
                'excerpt' => 'Privasi Tinggi & Kontrol Panas. Cocok untuk fasad gedung yang mengutamakan privasi dan tampilan solid gelap.',
                'body' => '<p>LEXENT Black Series hadir dengan teknologi kaca film yang dirancang untuk memberikan perlindungan optimal dari panas &amp; sinar UV, sekaligus menghadirkan privasi tinggi dan kenyamanan pada bangunan Anda.</p>',
                'spec_highlights' => [
                    ['label' => 'VLT', 'value' => '5%'],
                    ['label' => 'Heat Rejection (TSER)', 'value' => '62%'],
                    ['label' => 'UV Rejection', 'value' => '99%'],
                    ['label' => 'Infrared Rejection', 'value' => '75%'],
                    ['label' => 'Ketebalan', 'value' => '1,8 mil'],
                    ['label' => 'Garansi', 'value' => '8 Tahun'],
                ],
                'days_ago' => 9,
            ],
            [
                'slug' => 'sorotan-bv-35',
                'title' => 'LEXENT Black Vision 35',
                'category' => 'Black Vision',
                'excerpt' => 'Privasi Tinggi & Kontrol Panas. Varian dengan karakter privasi tinggi khas seri Black Vision.',
                'body' => '<p>LEXENT Black Series hadir dengan teknologi kaca film yang dirancang untuk memberikan perlindungan optimal dari panas &amp; sinar UV, sekaligus menghadirkan privasi tinggi dan kenyamanan pada bangunan Anda.</p>',
                'spec_highlights' => [
                    ['label' => 'VLT', 'value' => '5%'],
                    ['label' => 'Heat Rejection (TSER)', 'value' => '53%'],
                    ['label' => 'UV Rejection', 'value' => '99%'],
                    ['label' => 'Infrared Rejection', 'value' => '63%'],
                    ['label' => 'Ketebalan', 'value' => '1,8 mil'],
                    ['label' => 'Garansi', 'value' => '8 Tahun'],
                ],
                'days_ago' => 32,
            ],
            [
                'slug' => 'sorotan-rf-05',
                'title' => 'LEXENT Reflective 05',
                'category' => 'Reflective Series',
                'excerpt' => 'Reflektif, Modern & Elegan. Menghadirkan tampilan fasad kaca yang memantulkan cahaya sekaligus menolak panas.',
                'body' => '<p>LEXENT Reflective Series menghadirkan solusi kaca film dengan karakter reflektif yang dirancang untuk meningkatkan perlindungan dari panas matahari, memberikan privasi yang lebih baik, serta menciptakan tampilan modern dan elegan pada bangunan Anda.</p>',
                'spec_highlights' => [
                    ['label' => 'VLT', 'value' => '5%'],
                    ['label' => 'Heat Rejection (TSER)', 'value' => '55%'],
                    ['label' => 'UV Rejection', 'value' => '90%'],
                    ['label' => 'Infrared Rejection', 'value' => '92%'],
                    ['label' => 'Ketebalan', 'value' => '2 mil'],
                    ['label' => 'Garansi', 'value' => '8 Tahun'],
                ],
                'days_ago' => 47,
            ],
            [
                'slug' => 'sorotan-hp-08',
                'title' => 'LEXENT High Performance 08',
                'category' => 'High Performance',
                'excerpt' => 'Ultra HD Nano Ceramic. Sangat gelap — privasi maksimal dengan kejernihan pandangan tinggi.',
                'body' => '<p>LEXENT High Performance hadir dengan teknologi Ultra HD Nano Ceramic terbaru yang dirancang untuk memberikan perlindungan optimal dari panas &amp; sinar UV, dengan kejernihan tinggi untuk menghadirkan kenyamanan dan visibilitas yang lebih baik.</p>',
                'spec_highlights' => [
                    ['label' => 'VLT', 'value' => '8%'],
                    ['label' => 'Heat Rejection (TSER)', 'value' => '72%'],
                    ['label' => 'UV Rejection', 'value' => '99%'],
                    ['label' => 'Infrared Rejection', 'value' => '90%'],
                    ['label' => 'Ketebalan', 'value' => '2 mil'],
                    ['label' => 'Garansi', 'value' => '8 Tahun'],
                ],
                'days_ago' => 61,
            ],
            [
                'slug' => 'sorotan-hp-70',
                'title' => 'LEXENT High Performance 70',
                'category' => 'High Performance',
                'excerpt' => 'Ultra HD Nano Ceramic. Terang — visibilitas tinggi, cocok untuk fasad kaca besar gedung modern.',
                'body' => '<p>LEXENT High Performance hadir dengan teknologi Ultra HD Nano Ceramic terbaru yang dirancang untuk memberikan perlindungan optimal dari panas &amp; sinar UV. Varian VLT 70% cocok untuk fasad kaca yang tetap mengutamakan cahaya alami.</p>',
                'spec_highlights' => [
                    ['label' => 'VLT', 'value' => '70%'],
                    ['label' => 'Heat Rejection (TSER)', 'value' => '71%'],
                    ['label' => 'UV Rejection', 'value' => '99%'],
                    ['label' => 'Infrared Rejection', 'value' => '90%'],
                    ['label' => 'Ketebalan', 'value' => '2 mil'],
                    ['label' => 'Garansi', 'value' => '8 Tahun'],
                ],
                'days_ago' => 76,
            ],
            [
                'slug' => 'sorotan-up-08',
                'title' => 'LEXENT Ultra Protect 08',
                'category' => 'Ultra Protect',
                'excerpt' => 'Sputter Magnetron. Sangat gelap — privasi maksimal dengan Heat Rejection tertinggi di seri Ultra Protect.',
                'body' => '<p>LEXENT Ultra Protect hadir dengan teknologi Sputter Magnetron yang dirancang untuk memberikan perlindungan optimal dari panas dan sinar UV, sekaligus membantu mengurangi paparan sinar matahari dan meningkatkan kenyamanan serta privasi pada bangunan Anda.</p>',
                'spec_highlights' => [
                    ['label' => 'VLT', 'value' => '8%'],
                    ['label' => 'Heat Rejection (TSER)', 'value' => '76%'],
                    ['label' => 'UV Rejection', 'value' => '99%'],
                    ['label' => 'Infrared Rejection', 'value' => '99%'],
                    ['label' => 'Ketebalan', 'value' => '2 mil'],
                    ['label' => 'Garansi', 'value' => '8 Tahun'],
                ],
                'days_ago' => 3,
            ],
            [
                'slug' => 'sorotan-up-75',
                'title' => 'LEXENT Ultra Protect 75',
                'category' => 'Ultra Protect',
                'excerpt' => 'Sputter Magnetron. Terang — visibilitas tinggi, tetap dengan Infrared Rejection 99%.',
                'body' => '<p>LEXENT Ultra Protect hadir dengan teknologi Sputter Magnetron yang dirancang untuk memberikan perlindungan optimal dari panas dan sinar UV. Varian VLT 69% cocok untuk area kaca gedung yang membutuhkan cahaya alami lebih banyak.</p>',
                'spec_highlights' => [
                    ['label' => 'VLT', 'value' => '69%'],
                    ['label' => 'Heat Rejection (TSER)', 'value' => '72%'],
                    ['label' => 'UV Rejection', 'value' => '99%'],
                    ['label' => 'Infrared Rejection', 'value' => '99%'],
                    ['label' => 'Ketebalan', 'value' => '2 mil'],
                    ['label' => 'Garansi', 'value' => '8 Tahun'],
                ],
                'days_ago' => 24,
            ],
        ];
    }

    private function lexentPortfolio(): array
    {
        return [
            [
                'slug' => 'lexent-ht-15-toyota-alphard-jakarta-selatan',
                'title' => 'Pemasangan LEXENT HT 15 Toyota Alphard',
                'category' => 'Automotive Windowfilm',
                'location' => 'Jakarta Selatan',
                'excerpt' => 'Toyota Alphard milik pelanggan korporat dipasangi LEXENT HT 15 untuk kenyamanan penumpang dengan visibilitas tetap terjaga.',
                'body' => '<p>Sebagai kendaraan operasional korporat yang sering mengangkut tamu penting, pemilik memprioritaskan keseimbangan antara privasi dan kejernihan pandangan dari dalam kabin.</p><p>LEXENT HT 15 dipilih karena teknologi nano ceramic HD-nya memberikan penolakan panas signifikan tanpa membuat kabin terasa terlalu gelap di siang hari.</p>',
                'days_ago' => 8,
            ],
            [
                'slug' => 'lexent-mk-08-armada-taksi-eksekutif-tangerang',
                'title' => 'LEXENT MK 08 Non-Metal untuk Armada Taksi Eksekutif',
                'category' => 'Automotive Windowfilm',
                'location' => 'Tangerang',
                'excerpt' => 'Puluhan unit armada taksi eksekutif dipasangi LEXENT MK 08 agar sinyal aplikasi pemesanan tidak terganggu selama beroperasi.',
                'body' => '<p>Perusahaan penyedia layanan taksi eksekutif membutuhkan kaca film yang tidak mengganggu sinyal GPS dan aplikasi pemesanan pada perangkat di dalam kendaraan, mengingat seluruh armada sangat bergantung pada konektivitas real-time.</p><p>LEXENT MK 08 dengan teknologi Magnetron Sputter non-metal menjadi solusi tepat, memberikan privasi tinggi bagi penumpang tanpa mengorbankan performa sinyal elektronik di dalam kabin.</p>',
                'days_ago' => 24,
            ],
            [
                'slug' => 'lexent-ir99-08-range-rover-velar-bandung',
                'title' => 'LEXENT IR99 08 pada Range Rover Velar',
                'category' => 'Automotive Windowfilm',
                'location' => 'Bandung',
                'excerpt' => 'Range Rover Velar dipasangi LEXENT IR99 08 untuk penolakan panas maksimal saat digunakan berkendara jarak jauh.',
                'body' => '<p>Pemilik kendaraan yang sering melakukan perjalanan jarak jauh antar kota menginginkan kabin yang tetap sejuk tanpa terlalu bergantung pada AC agar konsumsi bahan bakar lebih efisien.</p><p>LEXENT IR99 08 dengan teknologi UV400 Nano Ceramic HD memberikan heat rejection hingga 81%, salah satu yang tertinggi di seluruh lini produk automotive LEXENT.</p>',
                'days_ago' => 40,
            ],
            [
                'slug' => 'lexent-bp-05-alphard-executive-lounge-surabaya',
                'title' => 'LEXENT BP 05 Privasi Maksimal Alphard Executive Lounge',
                'category' => 'Automotive Windowfilm',
                'location' => 'Surabaya',
                'excerpt' => 'Toyota Alphard konfigurasi Executive Lounge dipasangi LEXENT BP 05 pada kaca belakang untuk privasi penumpang VIP.',
                'body' => '<p>Kendaraan dengan konfigurasi kabin VIP ini digunakan untuk menjemput tamu penting perusahaan yang membutuhkan privasi tinggi selama perjalanan.</p><p>LEXENT BP 05 dengan VLT 5% dipasang pada kaca belakang dan samping belakang, memberikan privasi maksimal sekaligus penolakan UV hingga 99% untuk kenyamanan penumpang.</p>',
                'days_ago' => 56,
            ],
            [
                'slug' => 'lexent-ht-70-toyota-fortuner-semarang',
                'title' => 'Upgrade Kaca Film LEXENT HT 70 Toyota Fortuner',
                'category' => 'Automotive Windowfilm',
                'location' => 'Semarang',
                'excerpt' => 'Kaca depan Toyota Fortuner di-upgrade menggunakan LEXENT HT 70 untuk visibilitas malam hari yang lebih baik.',
                'body' => '<p>Pemilik kendaraan yang sering berkendara malam hari untuk perjalanan dinas mengeluhkan visibilitas yang kurang optimal dengan kaca film lamanya yang terlalu gelap di bagian depan.</p><p>LEXENT HT 70 dipilih sebagai solusi karena tetap menghadirkan visibilitas tinggi di kaca depan tanpa mengorbankan penolakan panas dan UV sesuai standar seri HT.</p>',
                'days_ago' => 72,
            ],
            [
                'slug' => 'lexent-black-vision-gedung-perkantoran-jakarta-pusat',
                'title' => 'LEXENT Black Vision Fasad Gedung Perkantoran 20 Lantai',
                'category' => 'Building Windowfilm',
                'location' => 'Jakarta Pusat',
                'excerpt' => 'Fasad kaca gedung perkantoran 20 lantai dilapisi LEXENT Black Vision untuk privasi ruang kerja dan efisiensi energi.',
                'body' => '<p>Pengelola gedung perkantoran ingin meningkatkan privasi visual dari luar bangunan tanpa mengurangi cahaya alami yang masuk ke dalam ruang kerja karyawan di setiap lantai.</p><p>LEXENT Black Vision dipilih karena karakter privasi tingginya yang konsisten di seluruh fasad, sekaligus membantu menekan beban pendinginan gedung berkat penolakan panas yang signifikan.</p>',
                'days_ago' => 15,
            ],
            [
                'slug' => 'lexent-reflective-mal-bsd-tangerang-selatan',
                'title' => 'Kaca Film Reflective untuk Mal di Kawasan BSD',
                'category' => 'Building Windowfilm',
                'location' => 'Tangerang Selatan',
                'excerpt' => 'Fasad kaca pusat perbelanjaan di kawasan BSD tampil lebih modern dengan LEXENT Reflective Series.',
                'body' => '<p>Pengembang pusat perbelanjaan menginginkan tampilan fasad yang lebih premium dan modern sebagai bagian dari identitas visual properti komersial baru di kawasan BSD.</p><p>LEXENT Reflective Series menghadirkan karakter reflektif yang memantulkan langit dan lingkungan sekitar, sekaligus menolak infrared hingga 92% untuk menjaga kenyamanan suhu di dalam area belanja.</p>',
                'days_ago' => 31,
            ],
            [
                'slug' => 'lexent-high-performance-rumah-sakit-surabaya',
                'title' => 'LEXENT High Performance Rumah Sakit Modern',
                'category' => 'Building Windowfilm',
                'location' => 'Surabaya',
                'excerpt' => 'Fasad kaca rumah sakit modern dilapisi LEXENT High Performance untuk kenyamanan pasien dan efisiensi energi.',
                'body' => '<p>Pengelola rumah sakit membutuhkan solusi kaca film yang menjaga ruangan tetap terang secara alami namun tidak menyebabkan silau berlebih maupun panas berlebih bagi pasien dan tenaga medis.</p><p>LEXENT High Performance dengan teknologi Ultra HD Nano Ceramic memberikan kejernihan tinggi sekaligus penolakan panas hingga 72%, cocok untuk fasilitas kesehatan dengan kebutuhan kenyamanan visual dan termal.</p>',
                'days_ago' => 50,
            ],
            [
                'slug' => 'lexent-ultra-protect-menara-perkantoran-jakarta-selatan',
                'title' => 'Ultra Protect untuk Menara Perkantoran Grade A',
                'category' => 'Building Windowfilm',
                'location' => 'Jakarta Selatan',
                'excerpt' => 'Menara perkantoran grade A menggunakan LEXENT Ultra Protect untuk performa penolakan panas dan UV terbaik di kelasnya.',
                'body' => '<p>Sebagai gedung perkantoran grade A yang mengutamakan efisiensi operasional, pengelola gedung memilih kaca film dengan performa penolakan panas dan UV tertinggi yang tersedia di lini produk LEXENT.</p><p>LEXENT Ultra Protect dengan teknologi Sputter Magnetron memberikan infrared rejection hingga 99%, membantu menekan beban pendinginan gedung secara signifikan sepanjang tahun.</p>',
                'days_ago' => 65,
            ],
            [
                'slug' => 'lexent-black-vision-hotel-bintang-lima-denpasar',
                'title' => 'LEXENT Black Vision Hotel Bintang Lima',
                'category' => 'Building Windowfilm',
                'location' => 'Denpasar',
                'excerpt' => 'Hotel bintang lima di Denpasar memilih LEXENT Black Vision untuk fasad kaca kamar tamu yang menghadap langsung ke area publik.',
                'body' => '<p>Kamar-kamar tamu yang menghadap langsung ke kolam renang dan area publik hotel membutuhkan solusi privasi visual tanpa mengorbankan pemandangan dari dalam kamar.</p><p>LEXENT Black Vision dipasang pada seluruh fasad kamar yang menghadap area publik, memberikan privasi tinggi bagi tamu sekaligus menjaga kesejukan ruangan di tengah iklim tropis Bali.</p>',
                'days_ago' => 84,
            ],
            [
                'slug' => 'lexent-up-65-gedung-universitas-bandung',
                'title' => 'Kaca Film Ultra Protect 65 Gedung Universitas',
                'category' => 'Building Windowfilm',
                'location' => 'Bandung',
                'excerpt' => 'Gedung perkuliahan baru sebuah universitas di Bandung menggunakan LEXENT Ultra Protect untuk kenyamanan ruang kelas.',
                'body' => '<p>Ruang kelas dengan fasad kaca besar sering menghadapi masalah panas berlebih di siang hari, mengganggu kenyamanan belajar mengajar terutama pada jam-jam dengan intensitas matahari tinggi.</p><p>LEXENT Ultra Protect varian VLT 58% dipilih untuk tetap mempertahankan cahaya alami yang cukup di ruang kelas, sambil menekan panas yang masuk melalui infrared rejection tinggi.</p>',
                'days_ago' => 96,
            ],
            [
                'slug' => 'lexent-mk-20-fleet-mobil-dinas-korporat-jakarta',
                'title' => 'LEXENT MK 20 untuk Fleet Mobil Dinas Korporat',
                'category' => 'Automotive Windowfilm',
                'location' => 'Jakarta',
                'excerpt' => 'Seluruh armada mobil dinas sebuah perusahaan korporat dipasangi LEXENT MK 20 secara serentak.',
                'body' => '<p>Divisi General Affair perusahaan ingin menstandardisasi kaca film pada seluruh armada mobil dinas, dengan syarat utama tidak mengganggu perangkat komunikasi dan navigasi yang digunakan tim lapangan.</p><p>LEXENT MK 20 dipilih sebagai standar armada karena konsistensi performanya — non-metal, bebas gangguan sinyal, dengan penolakan panas dan UV yang seragam di seluruh unit kendaraan.</p>',
                'days_ago' => 19,
            ],
            [
                'slug' => 'lexent-ir99-35-mercedes-benz-s-class-medan',
                'title' => 'LEXENT IR99 35 Mercedes-Benz S-Class',
                'category' => 'Automotive Windowfilm',
                'location' => 'Medan',
                'excerpt' => 'Mercedes-Benz S-Class dipasangi LEXENT IR99 35 untuk kenyamanan penumpang dengan visibilitas seimbang.',
                'body' => '<p>Sebagai sedan mewah yang sering digunakan untuk perjalanan bisnis dengan penumpang di kursi belakang, pemilik menginginkan kaca film dengan kejernihan tinggi namun tetap nyaman dari sisi panas dan silau.</p><p>LEXENT IR99 35 dengan VLT 36% memberikan keseimbangan antara privasi, visibilitas, dan infrared rejection hingga 99%, sesuai karakter kendaraan premium ini.</p>',
                'days_ago' => 68,
            ],
            [
                'slug' => 'lexent-hp-35-apartemen-mewah-makassar',
                'title' => 'Kaca Film Gedung High Performance 35 Apartemen Mewah',
                'category' => 'Building Windowfilm',
                'location' => 'Makassar',
                'excerpt' => 'Unit-unit apartemen mewah di Makassar menggunakan LEXENT High Performance untuk kenyamanan penghuni sehari-hari.',
                'body' => '<p>Pengembang apartemen mewah ingin memberikan nilai tambah bagi penghuni berupa kenyamanan termal tanpa mengurangi pemandangan kota yang menjadi daya tarik utama unit-unit di lantai atas.</p><p>LEXENT High Performance varian VLT 35% dipilih karena tetap mempertahankan cahaya alami yang cukup terang, sambil menekan panas matahari yang masuk ke dalam unit hunian.</p>',
                'days_ago' => 102,
            ],
            [
                'slug' => 'lexent-up-08-kantor-cabang-bank-jakarta-barat',
                'title' => 'LEXENT Ultra Protect Kantor Cabang Bank Nasional',
                'category' => 'Building Windowfilm',
                'location' => 'Jakarta Barat',
                'excerpt' => 'Kantor cabang sebuah bank nasional melapisi fasad kacanya dengan LEXENT Ultra Protect 08 untuk privasi dan efisiensi energi.',
                'body' => '<p>Sebagai kantor layanan nasabah, bangunan ini membutuhkan privasi visual yang tinggi terhadap area publik di luar, tanpa mengorbankan kenyamanan suhu ruangan bagi nasabah dan staf.</p><p>LEXENT Ultra Protect 08 dipilih karena kombinasi privasi tinggi dan penolakan panas maksimal, sejalan dengan standar efisiensi energi yang diterapkan bank pada seluruh jaringan kantor cabangnya.</p>',
                'days_ago' => 110,
            ],
        ];
    }
}
