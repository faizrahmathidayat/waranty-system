# CMS untuk Artikel, Katalog, dan Portofolio

**Status:** Disetujui — siap masuk tahap `writing-plans`.
**Tanggal:** 2026-09-18

## Latar Belakang & Tujuan

`dashboard` (Laravel 8, AdminLTE) saat ini adalah aplikasi internal untuk order/invoice/warranty. `compro-1` (brand **GlossPro**, port 8030) dan `compro-2` (brand **LEXENT**, port 8020) adalah dua situs company-profile terpisah yang katalog produknya saat ini 100% hardcode di `PageController.php` masing-masing (rich data: specs, FAQ, proses instalasi, matrix perbandingan).

Milestone ini menambahkan sebuah CMS ringan di `dashboard` untuk tiga jenis konten baru — **Artikel**, **Katalog** (sorotan produk/promo), **Portofolio** — yang dikonsumsi oleh `compro-1` dan `compro-2` lewat API bertoken.

## Non-Goals (eksplisit, disepakati bersama user)

- **TIDAK** mengganti katalog utama yang sudah hardcode (pillar/series, varian VLT, FAQ, proses instalasi, matrix perbandingan compro-2). Itu tetap seperti sekarang.
- CMS Katalog adalah *content type baru yang terpisah* ("Sorotan Produk"), bukan pengganti grid katalog utama.
- Tidak menyentuh domain Warranty/Order/Invoice/Product(SKU) yang sudah ada di `dashboard` — itu domain berbeda, tidak ada overlap tabel maupun route.

## Arsitektur

Tiga repo Laravel terpisah, tidak ada shared code:

- **`dashboard`** — pemilik data (source of truth). Punya UI admin CMS (AdminLTE, di balik login yang sudah ada) dan API publik bertoken untuk dikonsumsi situs lain.
- **`compro-1`** dan **`compro-2`** — masing-masing punya controller/route/view baru yang memanggil API `dashboard` **dari sisi server** (Laravel `Http` facade, bukan `fetch()` di browser) lalu merender halaman publik sendiri, mengikuti tema visual masing-masing situs yang sudah ada.

## Model Data (di `dashboard`)

Tiga tabel konten, bentuknya sengaja dibuat mirip:

### `cms_articles`
| kolom | tipe | catatan |
|---|---|---|
| id | bigint pk | |
| title | string | |
| slug | string, unique | auto-generate dari title (`Str::slug`), bisa diedit manual, unique di seluruh tabel |
| excerpt | string, nullable | ringkasan manual; kalau kosong saat dikirim lewat API, `dashboard` otomatis mengisi dari `body` (strip tag HTML, potong ~160 karakter, tambah "…") sebelum dikirim — consumer selalu menerima `excerpt` terisi, tidak perlu logic fallback sendiri |
| body | longtext (HTML) | dari WYSIWYG |
| category | string, nullable | freeform, bukan tabel master terpisah (YAGNI untuk v1) |
| status | enum('draft','published') default 'draft' | |
| published_at | timestamp, nullable | dipakai untuk urutan + filter "sudah tayang" |
| show_on_glosspro | boolean default false | target compro-1 |
| show_on_lexent | boolean default false | target compro-2 |
| created_by | bigint, nullable | FK ke user admin dashboard |
| timestamps + softDeletes | | |

### `cms_catalog_items`
Sama seperti `cms_articles`, ditambah:
| kolom | tipe | catatan |
|---|---|---|
| spec_highlights | json, nullable | array pasangan key-value bebas, mis. `[{"label":"VLT","value":"20%"},{"label":"Garansi","value":"5 Thn"}]` |

### `cms_portfolio_items`
Sama seperti `cms_articles`, ditambah:
| kolom | tipe | catatan |
|---|---|---|
| location | string, nullable | opsional, mis. nama kota/klien |

### `cms_media` (polymorphic, dipakai ketiga tabel di atas)
| kolom | tipe | catatan |
|---|---|---|
| id | bigint pk | |
| mediable_type | string | `'article'` \| `'catalog'` \| `'portfolio'` (bukan FQCN penuh, biar rapi) |
| mediable_id | bigint | |
| path | string | path relatif ke disk `public`, sudah `.webp`, versi full |
| thumbnail_path | string | versi thumbnail, sudah `.webp` |
| width / height | int | dimensi versi full, untuk `<img width height>` (cegah layout shift) |
| sort_order | int default 0 | urutan tampil di galeri |
| alt_text | string, nullable | aksesibilitas |
| timestamps | | |

Model Eloquent tiap content type punya `media()` → `morphMany(Media::class, 'mediable')->orderBy('sort_order')`, pakai `MorphMany` bawaan Laravel seperti biasa, tapi didaftarkan lewat `Relation::morphMap(['article' => Article::class, 'catalog' => CatalogItem::class, 'portfolio' => PortfolioItem::class])` di sebuah service provider — supaya kolom `mediable_type` di DB tetap berisi string pendek (`'article'`, dst) alih-alih FQCN penuh, tanpa perlu menulis ulang logic relasi sendiri.

**Query situs:** API/consumer query berdasar `show_on_glosspro` / `show_on_lexent`, bukan tabel pivot — cukup untuk 2 situs, gampang di-extend jadi pivot table kalau situs bertambah nanti.

## API (`dashboard`)

### Autentikasi
- `.env` (di `dashboard`): `CMS_API_KEY=<secret acak panjang>`
- `config/services.php`: tambah entry `'cms' => ['api_key' => env('CMS_API_KEY')]`
- Middleware baru `App\Http\Middleware\VerifyCmsApiKey`: cek header request `X-API-Key` terhadap `config('services.cms.api_key')`, `abort(401)` kalau tidak cocok/tidak ada. **Tidak ada pemanggilan `env()` di luar `config/services.php`.**
- Di `compro-1` dan `compro-2`: `.env` masing-masing punya `CMS_BASE_URL` + `CMS_API_KEY` (nilai key harus sama dengan yang di `dashboard`), diekspos lewat `config/services.php` masing-masing situs (`services.cms.base_url`, `services.cms.api_key`) — pola yang sama persis, tidak ada `env()` di luar config di sisi manapun.

### Routes (`routes/api.php` di `dashboard`, grup dengan middleware di atas, prefix `api/cms`)
```
GET /api/cms/articles?site=glosspro|lexent&page=1&per_page=9
GET /api/cms/articles/{slug}
GET /api/cms/catalog?site=glosspro|lexent&page=1&per_page=9
GET /api/cms/catalog/{slug}
GET /api/cms/portfolio?site=glosspro|lexent&page=1&per_page=9
GET /api/cms/portfolio/{slug}
```
- List endpoint: hanya `status='published'` dan `published_at <= now()`, filter `show_on_{site}=true` sesuai parameter `site` (wajib, 400 kalau tidak dikirim atau bukan `glosspro`/`lexent`), urut `published_at desc`, pakai Laravel paginator bawaan (`->paginate($perPage)`). `per_page` opsional, default 9, dibatasi maksimum 30 (dipaksa turun kalau consumer minta lebih).
- Response list: struktur standar Laravel resource collection — `data: [...]`, `meta: {current_page, last_page, per_page, total}`.
- Response detail: object tunggal + `media: [{url, thumbnail_url, width, height, alt_text}, ...]` (path disk diubah jadi URL absolut lewat `asset()`/`Storage::url()` sebelum dikirim).
- 404 kalau slug tidak ada / tidak published / tidak untuk situs itu (tidak boleh bocor konten draft/situs lain lewat detail endpoint — filter site & status tetap berlaku di endpoint detail juga, bukan cuma list).

## Pipeline Gambar (upload di admin `dashboard`)

Saat admin upload gambar (multi-file) di form CMS:
1. Tiap file diproses `intervention/image` (sudah ada di `composer.json` dashboard).
2. **Versi full**: resize supaya lebar maksimum 1920px *kalau* originalnya lebih besar (tidak pernah upscale gambar kecil), encode ke `.webp` kualitas ~80.
3. **Versi thumbnail**: resize lebar maksimum 480px, encode `.webp` kualitas ~75.
4. Simpan ke `storage/app/public/cms/{type}/{id}/{uuid}.webp` dan `.../{uuid}-thumb.webp`, disk `public` (sudah dikonfigurasi — lihat `noted untuk settingan upload gambar.txt`, `storage:link` sudah pernah dijalankan).
5. Baris baru di `cms_media` dengan `path`, `thumbnail_path`, `width`, `height` (dari gambar hasil resize), `sort_order` (index urutan upload, admin bisa reorder manual di form).

## Admin UI (`dashboard`)

Grup sidebar baru **"CMS"** dengan 3 menu: Artikel, Katalog, Portofolio. Tiap menu:
- **Index**: DataTable (pola sama seperti module lain di dashboard — `yajra/laravel-datatables-oracle` sudah dipakai di tempat lain), kolom: thumbnail pertama, title, category, status, situs (badge Glosspro/Lexent), published_at, aksi (edit/hapus).
- **Form create/edit**: title, slug (auto dari title, field terpisah yang bisa di-override), excerpt (textarea), body (WYSIWYG — **Quill.js** via CDN, dependency ringan, hanya dimuat di halaman admin ini, tidak memengaruhi bundle publik), category (text input bebas), checkbox situs (Glosspro / Lexent — minimal satu harus dicentang untuk publish), status (draft/published) + published_at (date picker, default sekarang saat pertama kali diset published), khusus Katalog: repeatable key-value input untuk `spec_highlights`, khusus Portofolio: input `location`. Multi-image uploader: input file multiple + daftar gambar existing dengan tombol hapus dan drag-to-reorder (pakai library drag-sort ringan atau fallback input angka "urutan" kalau drag-drop makan waktu implementasi).

## Sisi Consumer (`compro-1` dan `compro-2`, masing-masing independen)

Pola identik di kedua situs (menyesuaikan tema masing-masing):

- **Artikel**: route baru `/artikel` (list, pagination nomor halaman dari `meta` API, tiap card ada tombol "Baca Selengkapnya") dan `/artikel/{slug}` (detail: judul, tanggal, body HTML, galeri gambar kalau >1 foto).
- **Katalog (Sorotan Produk)**: route baru `/sorotan` (list) dan `/sorotan/{slug}` (detail dengan `spec_highlights` ditampilkan sebagai daftar spek + galeri gambar). Nama tampilan di nav: **"Sorotan Produk"** — sengaja beda dari "Produk"/"Layanan" (katalog utama yang sudah ada) supaya tidak tertukar secara UX.
- **Portofolio**:
  - `compro-1`: route `/portfolio` yang **sudah ada** — sumber datanya diganti dari `PageController::portfolioItems()` (hardcode) ke API CMS. URL & posisi di nav tidak berubah. View-nya didesain ulang mengikuti field CMS (title, excerpt/body, `location`, galeri) — field lama (`pillar`, `car_type`) tidak punya padanan langsung dan tidak dipertahankan; `category` bebas dipakai admin sebagai pengganti kasar "pillar" kalau perlu pengelompokan.
  - `compro-2`: route `/portfolio` + link nav **baru sepenuhnya** (belum pernah ada).
  - Detail portofolio: judul, location (kalau ada), body, galeri gambar.
- **Galeri/slider di halaman detail**: lightbox buatan sendiri (vanilla JS/CSS) — strip thumbnail + gambar utama + tombol prev/next + klik untuk perbesar (overlay fullscreen) — dibangun mengikuti pola hero-slider yang sudah ada di masing-masing situs (tanpa dependency JS baru di sisi publik), supaya konsisten dengan codebase yang sudah ada.
- **Resilience**: pemanggilan API dibungkus try/catch; kalau `dashboard` tidak bisa diakses/timeout, section terkait tampil sebagai empty-state ("Konten belum tersedia") alih-alih meng-crash halaman.
- **Tanpa caching** (keputusan user, 2026-09-19, membatalkan draf awal yang sempat memakai `Cache::remember` 5 menit di `compro-1`): setiap request ke halaman CMS consumer memanggil `dashboard` secara langsung, supaya konten yang baru di-publish/di-edit di admin langsung tampak di situs publik tanpa delay. Trade-off yang disadari: setiap kunjungan ke halaman ber-CMS membebani `dashboard` dengan satu API call; kalau traffic publik nanti jadi masalah performa, opsi seperti cache berdurasi pendek atau tombol "purge cache" manual di admin bisa dipertimbangkan lagi saat itu — bukan sekarang.

## Urutan Implementasi (fase, lewat satu plan)

Karena ketiga content type berbagi hampir semua infrastruktur (tabel media, middleware API key, pipeline gambar, pola pagination, lightbox), ini tetap SATU spec, tapi dieksekusi bertahap:

1. **Fase 1 — Infrastruktur + Artikel**: migrasi tabel (`cms_articles`, `cms_media` dulu — tabel lain menyusul di fase masing-masing), middleware API key, pipeline gambar, admin CRUD Artikel, endpoint API Artikel, integrasi consumer di kedua situs (`/artikel`, `/artikel/{slug}`, lightbox). Ini yang membuktikan seluruh pipa (upload→resize→webp→API→render→galeri) bekerja end-to-end.
2. **Fase 2 — Katalog (Sorotan Produk)**: tabel `cms_catalog_items`, admin CRUD + `spec_highlights`, endpoint API, integrasi `/sorotan` di kedua situs. Reuse infrastruktur Fase 1.
3. **Fase 3 — Portofolio**: tabel `cms_portfolio_items`, admin CRUD, endpoint API, **rewire** `compro-1` `/portfolio` ke API (ganti data source, bukan bikin route baru) + route `/portfolio` baru penuh di `compro-2`.

## Testing

- `dashboard`: feature test untuk middleware API key (401 tanpa/-dengan key salah, 200 dengan key benar), feature test tiap endpoint list (filter site, filter published-only, shape pagination) dan detail (404 untuk draft/situs lain/slug salah), unit test untuk service resize+webp (pastikan tidak upscale, output `.webp`).
- `compro-1` / `compro-2`: feature test controller (mock `Http::fake()` untuk response API dashboard) memastikan halaman render, pagination jalan, empty-state muncul saat API gagal/timeout.

## Keputusan yang sudah dikonfirmasi user (jangan diubah tanpa tanya lagi)

1. Katalog CMS = konten tambahan baru ("Sorotan Produk"), **bukan** pengganti katalog utama.
2. Tiap konten punya target situs sendiri (`show_on_glosspro` / `show_on_lexent`), bisa dicentang salah satu atau keduanya.
3. Nama kolom pakai nama brand (`glosspro`/`lexent`), bukan nama folder project (`compro1`/`compro2`).
