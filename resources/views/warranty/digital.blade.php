<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>E-Warranty - {{ $warranty->kode_warranty }}</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
    <style>
        :root { --navy:#0f172a; --ink:#111827; --muted:#5b6472; --faint:#8a93a3; --line:#dfe3ea; --paper:#f8f9fb; --gold:#e9b208; --active:#18b45b; --expired:#e53935; --claim:#f0a11a; --void:#6c757d; }
        * { box-sizing:border-box; }
        body { margin:0; min-height:100vh; padding:34px 22px; font-family:Poppins,sans-serif; color:var(--ink); background:linear-gradient(135deg,#e9ecf1,#f6f7f9); }
        .sr-only { position:absolute; width:1px; height:1px; overflow:hidden; clip:rect(0 0 0 0); white-space:nowrap; }
        .ew { width:min(1240px,100%); margin:0 auto; display:grid; grid-template-columns:minmax(360px,.9fr) minmax(480px,1.1fr); gap:28px; align-items:start; }

        /* ---------- Cover card: the supplied artwork is the whole design; only the
           dynamic values are laid over it. Positions are percentages of the artwork
           (1542 x 2000) and sizes use container units, so it scales as one piece. ---------- */
        .cover { position:sticky; top:24px; container-type:inline-size; overflow:hidden; border-radius:24px; background:#05070a; box-shadow:0 26px 60px rgba(15,23,42,.35); }
        .cover-art { display:block; width:100%; height:auto; }
        .cv { position:absolute; display:flex; flex-direction:column; justify-content:flex-start; color:#fff; }
        .cv span, .cv strong { display:-webkit-box; -webkit-box-orient:vertical; -webkit-line-clamp:2; overflow:hidden; word-break:break-word; }
        .cv-product, .cv-customer { top:65.9%; width:34%; font-size:12px; font-size:max(11px,1.95cqw); font-weight:600; line-height:1.25; }
        .cv-product { left:12%; text-align:right; }
        .cv-customer { left:54%; text-align:left; }
        .cv-id { left:20.3%; width:59.5%; top:76%; height:4.9%; align-items:center; justify-content:center; text-align:center; }
        .cv-id strong { font-size:22px; font-size:max(16px,5.1cqw); font-weight:600; letter-spacing:.06em; -webkit-line-clamp:1; }
        .cv-status { position:absolute; left:0; right:0; top:84.2%; display:flex; justify-content:center; align-items:center; gap:.7em; font-size:12px; font-size:max(11px,1.85cqw); font-weight:600; letter-spacing:.28em; color:#2fe283; }
        .cv-status i { font-size:1.5em; letter-spacing:0; }
        .cv-status.expired { color:#ff7a77; } .cv-status.claim { color:#ffc766; } .cv-status.void { color:#c3c9d2; }
        @container (max-width: 470px) { .cv-product, .cv-customer { font-size:max(10px,2.7cqw); } .cv span { -webkit-line-clamp:1; } .cv-status { letter-spacing:.18em; } }

        /* ---------- Detail (light) card ---------- */
        .detail { display:flex; flex-direction:column; padding:30px 32px 22px; border-radius:24px; background:var(--paper); border:1px solid #eceff4; box-shadow:0 20px 50px rgba(15,23,42,.12); }
        .d-head { display:flex; justify-content:space-between; align-items:flex-start; gap:18px; padding-bottom:18px; border-bottom:1px solid var(--line); }
        .d-head img { height:34px; width:auto; }
        .d-head .brand-tag { display:block; margin-top:8px; font-size:9px; letter-spacing:.42em; color:var(--muted); }
        .d-head .title { text-align:right; }
        .d-head .title strong { display:block; font-size:clamp(16px,1.7vw,21px); font-weight:600; letter-spacing:.2em; color:var(--navy); }
        .d-head .title span { display:block; margin-top:6px; font-size:10px; letter-spacing:.3em; color:var(--muted); line-height:1.7; }
        .sec { display:grid; grid-template-columns:30px 1fr; gap:6px 12px; padding:18px 0; border-bottom:1px solid var(--line); }
        .sec > i { font-size:21px; color:var(--navy); line-height:1.2; }
        .sec h2 { margin:0 0 8px; font-size:14px; font-weight:600; letter-spacing:.12em; color:var(--navy); text-transform:uppercase; }
        .rows { margin:0; display:grid; grid-template-columns:max-content 1fr; gap:5px 0; font-size:13px; }
        .rows dt { color:var(--muted); padding-right:34px; }
        .rows dd { margin:0; color:var(--ink); word-break:break-word; }
        .rows dd:before { content:":"; display:inline-block; width:18px; color:var(--muted); }
        .rows dt, .rows dd { line-height:1.55; }
        .sec-split { display:grid; grid-template-columns:1.25fr 1fr; }
        .sec-split .sec { border-bottom:0; }
        .sec-split > .sec + .sec { border-left:1px solid var(--line); padding-left:22px; }
        .sec-wrap { border-bottom:1px solid var(--line); }
        .tick { list-style:none; margin:8px 0 0; padding:0; font-size:12.5px; color:var(--ink); }
        .tick li { margin:5px 0; } .tick i { margin-right:8px; color:var(--navy); }
        .note { margin:0; font-size:12.5px; color:var(--muted); line-height:1.6; }
        .items { margin-top:12px; border-top:1px dashed var(--line); padding-top:10px; }
        .items h3 { margin:0 0 6px; font-size:11px; font-weight:600; letter-spacing:.14em; color:var(--muted); text-transform:uppercase; }
        .item { display:grid; grid-template-columns:1.1fr 1.4fr auto; gap:4px 14px; align-items:center; padding:7px 0; border-top:1px solid #eef0f4; font-size:12.5px; }
        .item:first-of-type { border-top:0; }
        .item b { font-weight:600; } .item small { display:block; color:var(--faint); font-size:11px; }
        .chip { display:inline-block; padding:3px 10px; border-radius:99px; font-size:10px; font-weight:700; letter-spacing:.06em; color:#0f7a3d; background:#dff5e8; }
        .chip.expired { color:#b3261e; background:#fde4e2; } .chip.claim { color:#a15c00; background:#fdeccc; } .chip.void { color:#4b5563; background:#e5e7eb; }
        .cover-cols { display:grid; grid-template-columns:1fr 1fr; gap:0 22px; margin-top:12px; }
        .cover-cols > div + div { border-left:1px solid var(--line); padding-left:20px; }
        .cover-cols h3 { display:flex; align-items:center; gap:9px; margin:0 0 8px; font-size:13px; font-weight:600; color:var(--navy); }
        .cover-cols h3 i { font-size:19px; }
        .cover-cols ul { margin:0; padding-left:18px; font-size:11.5px; color:var(--muted); line-height:1.6; }
        .penting { display:grid; grid-template-columns:1.6fr 1fr; gap:0; margin-top:18px; padding:16px 20px; border-radius:14px; background:#e6e9ef; }
        .penting h3 { display:flex; align-items:center; gap:9px; margin:0 0 6px; font-size:12.5px; font-weight:600; letter-spacing:.1em; color:var(--navy); }
        .penting p { margin:0; font-size:11px; line-height:1.6; color:var(--muted); }
        .penting p u { text-decoration:underline; }
        .cs { display:flex; flex-direction:column; justify-content:center; gap:10px; padding-left:22px; margin-left:20px; border-left:1px solid #cfd4dd; font-size:13px; }
        .cs div { display:flex; align-items:center; gap:12px; } .cs i { font-size:22px; color:var(--navy); }
        .cs small { display:block; font-size:10.5px; font-weight:600; color:var(--ink); }
        .d-foot { display:flex; justify-content:space-between; align-items:center; margin-top:auto; padding-top:20px; font-size:9px; letter-spacing:.34em; color:var(--muted); }
        .d-foot img { height:15px; width:auto; }

        @media (max-width:980px) { body { padding:18px 14px; } .ew { grid-template-columns:1fr; max-width:640px; gap:20px; } .cover { position:relative; top:auto; } }
        @media (max-width:560px) {
            body { padding:10px; }
            .cover { border-radius:20px; }
            .detail { padding:22px 18px 18px; border-radius:20px; }
            .d-head { flex-direction:column; } .d-head .title { text-align:left; }
            .rows { grid-template-columns:1fr; gap:0; } .rows dt { padding:7px 0 0; font-size:11.5px; } .rows dd:before { display:none; }
            .sec { grid-template-columns:26px 1fr; }
            .sec-split { grid-template-columns:1fr; } .sec-split > .sec + .sec { border-left:0; padding-left:0; border-top:1px solid var(--line); }
            .sec-split .sec:first-child { border-bottom:0; }
            .cover-cols { grid-template-columns:1fr; gap:16px; } .cover-cols > div + div { border-left:0; padding-left:0; }
            .penting { grid-template-columns:1fr; } .cs { margin:14px 0 0; padding:14px 0 0; border-left:0; border-top:1px solid #cfd4dd; }
            .item { grid-template-columns:1fr auto; } .item > :nth-child(2) { grid-column:1 / -1; order:3; }
        }
    </style>
</head>
<body>
@php
    use Carbon\Carbon;

    $type = optional($warranty->warrantyType)->code;
    $transactionItems = $warranty->warrantyItems;
    $isTransactionWarranty = $transactionItems->isNotEmpty();
    $identity = $isTransactionWarranty ? ($type === 'BUILDING' ? $warranty->assetBuilding : $warranty->assetVehicle) : ($type === 'BUILDING' ? $warranty->building : ($type === 'PPF' ? $warranty->ppf : $warranty->vehicle));
    $items = $transactionItems->isNotEmpty() ? $transactionItems : ($identity ? $identity->items : collect());
    $today = now()->startOfDay();
    $statusOf = function ($itemStatus, $expiredAt) use ($warranty, $today) {
        if ($warranty->status === 'Void') return 'Void';
        if ($itemStatus === 'Claim' || $warranty->status === 'Claim') return 'Claim';
        return Carbon::parse($expiredAt)->startOfDay()->lt($today) ? 'Expired' : 'Active';
    };
    $headerStatus = $statusOf(null, $warranty->tanggal_expired);
    $statusIcon = ['Active' => 'fa-circle-check', 'Expired' => 'fa-circle-xmark', 'Claim' => 'fa-triangle-exclamation', 'Void' => 'fa-ban'];

    $customer = $warranty->customer;
    $start = Carbon::parse($warranty->tanggal_pasang);
    $end = Carbon::parse($warranty->tanggal_expired);
    $diff = $start->diff($end);
    $period = [];
    if ($diff->y) $period[] = $diff->y . ' Tahun';
    if ($diff->m) $period[] = $diff->m . ' Bulan';
    if (!$period && $diff->d) $period[] = $diff->d . ' Hari';
    $period = $period ? implode(' ', $period) : '-';

    $itemProduct = fn ($item) => $isTransactionWarranty ? $item->product_name_snapshot : optional($item->product)->nama_produk;
    $itemVariant = fn ($item) => $isTransactionWarranty ? $item->variant_name_snapshot : null;
    $itemTitle = fn ($item) => $isTransactionWarranty ? $item->area : ($type === 'CAR' ? $item->posisi_kaca : $item->area_pekerjaan);
    $productNames = $items->map($itemProduct)->filter()->unique()->values();
    $variantNames = $items->map($itemVariant)->filter()->unique()->values();
    if ($productNames->isEmpty() && optional($warranty->product)->nama_produk) $productNames = collect([$warranty->product->nama_produk]);

    $vehicleName = trim((optional($identity)->merk ?: optional($identity)->merk_mobil ?: $warranty->merk_mobil) . ' ' . (optional($identity)->model ?: optional($identity)->tipe_mobil ?: $warranty->tipe_mobil));
    $plate = optional($identity)->no_polisi ?: $warranty->no_polisi;
    $fmt = fn ($date) => Carbon::parse($date)->format('d / m / Y');
    $typeName = optional($warranty->warrantyType)->name ?: 'Warranty';
    $customerName = optional($customer)->nama_customer ?: '-';
@endphp
<main class="ew">
    {{-- ============ Cover card (artwork + dynamic values) ============ --}}
    <section class="cover" id="warrantyCover">
        <h1 class="sr-only">Official E-Warranty LEXENT</h1>
        <img class="cover-art" src="{{ asset('images/warranty-cover.jpg') }}?v={{ @filemtime(public_path('images/warranty-cover.jpg')) }}" alt="" width="1542" height="2000">
        <div class="cv cv-product"><span title="{{ $typeName }}">{{ $typeName }}</span></div>
        <div class="cv cv-customer"><span title="{{ $customerName }}">{{ $customerName }}</span></div>
        <div class="cv cv-id"><strong>{{ $warranty->kode_warranty }}</strong></div>
        <div class="cv-status {{ strtolower($headerStatus) }}"><i class="fa-solid {{ $statusIcon[$headerStatus] }}"></i> WARRANTY {{ strtoupper($headerStatus) }}</div>
    </section>

    {{-- ============ Detail card ============ --}}
    <section class="detail" id="warrantyDetail">
        <header class="d-head">
            <div><img src="{{ asset('images/lexent-logo.png') }}" alt="LEXENT"><span class="brand-tag">WINDOW FILM &nbsp;•&nbsp; PPF</span></div>
            <div class="title"><strong>E-WARRANTY CARD</strong><span>AUTOMOTIVE &amp; BUILDING<br>PROTECTION FILM</span></div>
        </header>

        <div class="sec">
            <i class="fa-regular fa-user"></i>
            <div>
                <h2>Data Customer</h2>
                <dl class="rows">
                    <dt>Nama</dt><dd>{{ $customerName }}</dd>
                    @if(optional($customer)->no_hp)<dt>No. WhatsApp</dt><dd>{{ $customer->no_hp }}</dd>@endif
                    @if(optional($customer)->alamat)<dt>Alamat</dt><dd>{{ $customer->alamat }}</dd>@endif
                    <dt>Tanggal Pemasangan</dt><dd>{{ $fmt($warranty->tanggal_pasang) }}</dd>
                </dl>
            </div>
        </div>

        <div class="sec">
            <i class="fa-solid fa-cube"></i>
            <div>
                <h2>Informasi Produk</h2>
                <dl class="rows">
                    <dt>Jenis Produk</dt><dd>{{ $typeName }}</dd>
                    @if($productNames->isNotEmpty())<dt>Nama Produk / Series</dt><dd>{{ $productNames->implode(', ') }}</dd>@endif
                    @if($variantNames->isNotEmpty())<dt>Warna / Shade</dt><dd>{{ $variantNames->implode(', ') }}</dd>@endif
                    @if($type === 'BUILDING')
                        @if(optional($identity)->nama_bangunan)<dt>Properti</dt><dd>{{ $identity->nama_bangunan }}</dd>@endif
                        @if(optional($identity)->alamat)<dt>Lokasi Pemasangan</dt><dd>{{ $identity->alamat }}</dd>@endif
                    @else
                        @if($vehicleName !== '')<dt>Kendaraan</dt><dd>{{ $vehicleName }}</dd>@endif
                        @if($plate)<dt>No. Plat Kendaraan</dt><dd>{{ $plate }}</dd>@endif
                    @endif
                    @if($warranty->installer)<dt>Teknisi</dt><dd>{{ $warranty->installer }}</dd>@endif
                    @if($warranty->no_invoice)<dt>No. Invoice</dt><dd>{{ $warranty->no_invoice }}</dd>@endif
                    @if($warranty->catatan)<dt>Catatan</dt><dd>{{ $warranty->catatan }}</dd>@endif
                </dl>

                @if($items->isNotEmpty())
                    <div class="items">
                        <h3>Detail Item</h3>
                        @foreach($items as $item)
                            @php $itemStatus = $statusOf($item->status, $item->tanggal_expired); @endphp
                            <div class="item">
                                <div><b>{{ $itemTitle($item) ?: 'Item ' . $loop->iteration }}</b></div>
                                <div>{{ $itemProduct($item) }}@if($itemVariant($item)) - {{ $itemVariant($item) }}@endif<small>Berlaku sampai {{ $fmt($item->tanggal_expired) }}</small></div>
                                <div><span class="chip {{ strtolower($itemStatus) }}">{{ strtoupper($itemStatus) }}</span></div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>

        <div class="sec-wrap">
            <div class="sec-split">
                <div class="sec">
                    <i class="fa-solid fa-shield-halved"></i>
                    <div>
                        <h2>Informasi Garansi</h2>
                        <dl class="rows">
                            <dt>Nomor E-Warranty</dt><dd><b>{{ $warranty->kode_warranty }}</b></dd>
                            <dt>Masa Garansi</dt><dd><b>{{ $period }}</b></dd>
                            <dt>Berlaku Mulai</dt><dd><b>{{ $fmt($warranty->tanggal_pasang) }}</b></dd>
                            <dt>Berlaku Sampai</dt><dd><b>{{ $fmt($warranty->tanggal_expired) }}</b></dd>
                        </dl>
                    </div>
                </div>
                <div class="sec">
                    <i class="fa-regular fa-calendar-check"></i>
                    <div>
                        <h2>Jenis Produk Lainnya</h2>
                        <p class="note">Kartu garansi ini berlaku untuk semua produk Lexent, meliputi:</p>
                        <ul class="tick">
                            <li><i class="fa-solid fa-check"></i>Kaca Film Mobil</li>
                            <li><i class="fa-solid fa-check"></i>Kaca Film Bangunan</li>
                            <li><i class="fa-solid fa-check"></i>Paint Protection Film (PPF)</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <div class="sec" style="border-bottom:0;padding-bottom:0">
            <i class="fa-regular fa-file-lines"></i>
            <div>
                <h2>Garansi Meliputi</h2>
                <p class="note">Garansi Lexent berlaku terhadap cacat produk dan/atau masalah yang termasuk dalam ketentuan garansi produk yang bersangkutan selama masa garansi.</p>
                <div class="cover-cols">
                    <div>
                        <h3><i class="fa-solid fa-circle-check"></i>Garansi dapat mencakup:</h3>
                        <ul>
                            <li>Cacat material / produk</li>
                            <li>Perubahan kondisi produk sesuai ketentuan</li>
                            <li>Adhesive failure sesuai ketentuan produk</li>
                            <li>Masalah lain yang dinyatakan dalam warranty policy Lexent.</li>
                        </ul>
                    </div>
                    <div>
                        <h3><i class="fa-solid fa-circle-xmark"></i>Garansi tidak mencakup:</h3>
                        <ul>
                            <li>Kerusakan akibat kecelakaan, benturan, atau goresan</li>
                            <li>Kerusakan akibat penggunaan yang tidak sesuai</li>
                            <li>Kerusakan akibat modifikasi atau pemasangan pihak lain</li>
                            <li>Kerusakan akibat faktor eksternal</li>
                            <li>Kerusakan karena perawatan / bahan kimia yang tidak sesuai</li>
                            <li>Kerusakan di luar ketentuan garansi Lexent.</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <div class="penting">
            <div>
                <h3><i class="fa-solid fa-circle-exclamation"></i>PENTING</h3>
                <p>Garansi hanya berlaku untuk produk yang terdaftar pada sistem <u>Lexent E-Warranty</u>. Untuk melakukan klaim garansi, customer wajib menunjukkan <u>Nomor E-Warranty</u> atau melakukan verifikasi melalui sistem <u>e-Warranty Lexent</u>.</p>
            </div>
            <div class="cs">
                <div><i class="fa-brands fa-whatsapp"></i><span><small>Customer Service</small>{{ config('warranty_card.customer_service_whatsapp') }}</span></div>
                <div><i class="fa-solid fa-globe"></i><span>{{ config('warranty_card.website') }}</span></div>
            </div>
        </div>

        <footer class="d-foot"><span>DRIVE &nbsp;·&nbsp; BUILD &nbsp;·&nbsp; PROTECT</span><img src="{{ asset('images/lexent-logo.png') }}" alt="LEXENT"></footer>
    </section>
</main>
</body>
</html>
