<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Digital Warranty - {{ $warranty->kode_warranty }}</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
    <style>
        :root { --gold:#e5a828; --gold-bright:#f6c85a; --ink:#0b0b0c; --line:rgba(255,255,255,.14); --active:#18b45b; --expired:#e53935; --void:#6c757d; --claim:#f0a11a; }
        * { box-sizing:border-box; }
        body { margin:0; min-height:100vh; padding:40px 24px; color:#fff; font-family:Poppins,sans-serif; background:radial-gradient(circle at top left,rgba(229,168,40,.3) 0,transparent 35%),radial-gradient(circle at bottom right,rgba(229,168,40,.12) 0,transparent 40%),linear-gradient(135deg,#050506,var(--ink)); }
        .warranty-layout { width:min(1320px,100%); margin:0 auto; display:grid; grid-template-columns:minmax(380px,.86fr) minmax(420px,1.14fr); gap:28px; align-items:start; }
        .warranty-card,.details-panel,.item-card { background:linear-gradient(135deg,rgba(255,255,255,.12),rgba(255,255,255,.035)); border:1px solid var(--line); box-shadow:0 22px 55px rgba(0,0,0,.3),inset 0 1px 1px rgba(255,255,255,.15); backdrop-filter:blur(12px); }
        .warranty-card { position:sticky; top:24px; min-height:620px; overflow:hidden; border-radius:28px; }
        .warranty-card:before,.warranty-card:after { content:""; position:absolute; border-radius:50%; background:rgba(255,255,255,.05); }
        .warranty-card:before { width:330px;height:330px;top:-145px;right:-120px; }.warranty-card:after { width:250px;height:250px;bottom:-115px;left:-100px; }
        .watermark { position:absolute; right:-24px;bottom:-72px;font-size:255px;color:rgba(255,255,255,.04); }
        .card-content { position:relative;z-index:1;padding:36px;min-height:620px;display:flex;flex-direction:column; }
        .header { display:flex;justify-content:space-between;gap:18px;align-items:flex-start; }.logo { display:flex;gap:13px;align-items:center; }.logo-circle { flex:0 0 62px;width:62px;height:62px;border-radius:50%;display:grid;place-items:center;background:rgba(255,255,255,.18);font-size:25px; }.logo h1 { margin:0;font-size:20px;letter-spacing:1px; }.logo span,.code small,.customer small,.info label { color:rgba(255,255,255,.72);font-size:12px; }.code { text-align:right; }.code strong { display:block;margin-top:5px;font-size:23px;letter-spacing:2px;word-break:break-word; }
        .customer { margin-top:37px; }.customer h2 { margin:6px 0 0;font-size:29px;line-height:1.25;word-break:break-word; }.info-grid { display:grid;grid-template-columns:1fr 1fr;gap:20px 32px;margin-top:26px; }.info { min-width:0;padding-bottom:10px;border-bottom:1px solid var(--line); }.info label { display:block; }.info h3 { margin:7px 0 0;font-size:16px;line-height:1.45;word-break:break-word; }.footer { margin-top:auto;padding-top:35px; }.status { display:inline-flex;align-items:center;gap:8px;padding:11px 18px;border-radius:999px;font-size:13px;font-weight:700;letter-spacing:.3px;background:var(--active);box-shadow:0 8px 25px rgba(0,0,0,.22); }.status.expired{background:var(--expired)}.status.claim{background:var(--claim)}.status.void{background:var(--void)}
        .details-panel { border-radius:28px;padding:26px; }.details-heading { display:flex;justify-content:space-between;align-items:center;gap:12px;margin-bottom:19px; }.details-heading h2 { margin:0;font-size:20px; }.details-heading span { color:rgba(255,255,255,.72);font-size:12px; }.item-list { display:flex;flex-direction:column;gap:15px; }.item-card { border-radius:18px;padding:19px; }.item-top { display:flex;justify-content:space-between;gap:12px;align-items:flex-start; }.item-title { display:flex;align-items:center;gap:10px;min-width:0; }.item-title i { color:var(--gold-bright);font-size:18px; }.item-title h3 { margin:0;font-size:15px;letter-spacing:.4px;word-break:break-word; }.item-status { flex:0 0 auto;display:inline-flex;align-items:center;gap:5px;border-radius:999px;padding:5px 9px;font-size:10px;font-weight:700;background:rgba(24,180,91,.18);color:#78e8a5;border:1px solid rgba(120,232,165,.3); }.item-status.expired { background:rgba(229,57,53,.18);color:#ff9a97;border-color:rgba(255,154,151,.3); }.item-status.claim { background:rgba(240,161,26,.18);color:#ffd580;border-color:rgba(255,213,128,.3); }.item-status.void { background:rgba(160,170,180,.18);color:#d3d8de;border-color:rgba(211,216,222,.25); }.product-name { margin:15px 0 17px;font-size:18px;font-weight:600;word-break:break-word; }.item-data { display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:12px 18px;padding-top:14px;border-top:1px solid var(--line); }.item-data div { min-width:0; }.item-data label { display:block;color:rgba(255,255,255,.66);font-size:11px; }.item-data strong { display:block;margin-top:3px;font-size:13px;line-height:1.45;word-break:break-word; }.item-footer { margin-top:16px;padding-top:13px;border-top:1px solid var(--line);font-size:12px;font-weight:700;color:#78e8a5; }.item-footer.expired{color:#ff9a97}.item-footer.claim{color:#ffd580}.item-footer.void{color:#d3d8de}.empty-items { color:rgba(255,255,255,.72);font-size:13px;padding:20px 0;text-align:center; }
        @media (max-width: 900px) { body { padding:25px 18px; }.warranty-layout { grid-template-columns:1fr;max-width:700px; }.warranty-card { position:relative;top:auto;min-height:0; }.card-content { min-height:0; }.details-panel { padding:22px; } }
        @media (max-width: 768px) { body { padding:12px; }.warranty-layout { gap:18px; }.warranty-card,.details-panel { border-radius:21px; }.card-content { padding:24px 20px; }.header { flex-direction:column; }.code { text-align:left; }.customer { margin-top:26px; }.customer h2 { font-size:24px; }.info-grid { grid-template-columns:1fr;gap:15px;margin-top:20px; }.footer { margin-top:29px;padding-top:25px; }.status { width:100%;justify-content:center;text-align:center; }.details-panel { padding:20px 15px; }.details-heading { align-items:flex-start;flex-direction:column;margin-bottom:16px; }.item-card { padding:16px; }.item-top { gap:8px; }.item-title h3 { font-size:13px; }.item-status { font-size:9px;padding:5px 7px; }.product-name { font-size:16px;margin:14px 0; }.item-data { grid-template-columns:1fr;gap:10px;padding-top:12px; }.watermark { font-size:190px;right:-50px;bottom:-45px; } }
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
    $headerStatus = $warranty->status === 'Void' ? 'Void' : ($warranty->status === 'Claim' ? 'Claim' : (Carbon::parse($warranty->tanggal_expired)->startOfDay()->lt($today) ? 'Expired' : 'Active'));
    $statusIcon = ['Active' => 'fa-circle-check', 'Expired' => 'fa-circle-xmark', 'Claim' => 'fa-triangle-exclamation', 'Void' => 'fa-ban'];
@endphp
<main class="warranty-layout">
    <section class="warranty-card" id="warrantyCard">
        <div class="watermark"><i class="fa-solid fa-shield-halved"></i></div>
        <div class="card-content">
            <header class="header"><div class="logo"><div class="logo-circle"><i class="fa-solid fa-shield-halved"></i></div><div><h1>DIGITAL WARRANTY</h1><span>Premium Digital Warranty Card</span></div></div><div class="code"><small>Warranty Code</small><strong>{{ $warranty->kode_warranty }}</strong></div></header>
            <div class="customer"><small>Customer Name</small><h2>{{ optional($warranty->customer)->nama_customer }}</h2></div>
            <div class="info-grid">
                <div class="info"><label><i class="fa-solid fa-layer-group"></i> Product</label><h3>{{ optional($warranty->product)->nama_produk }}</h3></div>
                @if($type === 'BUILDING')
                    <div class="info"><label><i class="fa-solid fa-building"></i> Building Name</label><h3>{{ optional($identity)->nama_bangunan }}</h3></div>
                    <div class="info"><label><i class="fa-solid fa-location-dot"></i> Address</label><h3>{{ optional($identity)->alamat }}</h3></div>
                @else
                    <div class="info"><label><i class="fa-solid fa-car"></i> Vehicle</label><h3>{{ optional($identity)->merk ?: optional($identity)->merk_mobil ?: $warranty->merk_mobil }} {{ optional($identity)->model ?: optional($identity)->tipe_mobil ?: $warranty->tipe_mobil }}</h3></div>
                    <div class="info"><label><i class="fa-solid fa-id-card"></i> Plate Number</label><h3>{{ optional($identity)->no_polisi ?: $warranty->no_polisi }}</h3></div>
                @endif
                <div class="info"><label><i class="fa-solid fa-calendar-days"></i> Installation</label><h3>{{ Carbon::parse($warranty->tanggal_pasang)->format('d M Y') }}</h3></div>
                <div class="info"><label><i class="fa-solid fa-hourglass-end"></i> Valid Until</label><h3>{{ Carbon::parse($warranty->tanggal_expired)->format('d M Y') }}</h3></div>
                <div class="info"><label><i class="fa-solid fa-file-invoice"></i> Invoice</label><h3>{{ $warranty->no_invoice ?: '-' }}</h3></div>
                <div class="info"><label><i class="fa-solid fa-user-gear"></i> Teknisi</label><h3>{{ $warranty->installer ?: '-' }}</h3></div>
                <div class="info"><label><i class="fa-solid fa-note-sticky"></i> Catatan</label><h3>{{ $warranty->catatan ?: '-' }}</h3></div>
            </div>
            <footer class="footer"><div class="status {{ strtolower($headerStatus) }}"><i class="fa-solid {{ $statusIcon[$headerStatus] }}"></i> {{ strtoupper($headerStatus) }} WARRANTY</div></footer>
        </div>
    </section>
    <section class="details-panel">
        <div class="details-heading"><h2>Detail Warranty</h2><span>{{ optional($warranty->warrantyType)->name ?: 'Warranty Items' }}</span></div>
        <div class="item-list">
            @forelse($items as $item)
                @php
                    $itemStatus = $warranty->status === 'Void' ? 'Void' : ($item->status === 'Claim' || $warranty->status === 'Claim' ? 'Claim' : (Carbon::parse($item->tanggal_expired)->startOfDay()->lt($today) ? 'Expired' : 'Active'));
                    $title = $isTransactionWarranty ? $item->area : ($type === 'CAR' ? $item->posisi_kaca : $item->area_pekerjaan);
                    $icon = $type === 'BUILDING' ? 'fa-building' : ($type === 'PPF' ? 'fa-shield-halved' : 'fa-car');
                @endphp
                <article class="item-card">
                    <div class="item-top"><div class="item-title"><i class="fa-solid {{ $icon }}"></i><h3>{{ strtoupper($title) }}</h3></div><span class="item-status {{ strtolower($itemStatus) }}"><i class="fa-solid {{ $statusIcon[$itemStatus] }}"></i> {{ strtoupper($itemStatus) }}</span></div>
                    <div class="product-name">{{ $isTransactionWarranty ? $item->product_name_snapshot : optional($item->product)->nama_produk }}{{ $isTransactionWarranty && $item->variant_name_snapshot ? ' - '.$item->variant_name_snapshot : '' }}</div>
                    <div class="item-data">
                        @if($type === 'BUILDING' || ($isTransactionWarranty && $item->item_type === 'BUILDING'))
                            @if($item->panjang && $item->lebar)<div><label>Ukuran</label><strong>{{ number_format($item->panjang, 2) }} m × {{ number_format($item->lebar, 2) }} m</strong></div>@endif
                            <div><label>Jumlah</label><strong>{{ $isTransactionWarranty ? $item->quantity : $item->jumlah }} {{ $isTransactionWarranty ? $item->unit : 'kaca' }}</strong></div>
                            <div><label>Total Luas</label><strong>{{ number_format($item->total_luas, 2) }} m²</strong></div>
                        @endif
                        <div><label>Installed</label><strong>{{ Carbon::parse($item->tanggal_pasang)->format('d M Y') }}</strong></div>
                        <div><label>Valid Until</label><strong>{{ Carbon::parse($item->tanggal_expired)->format('d M Y') }}</strong></div>
                    </div>
                    <div class="item-footer {{ strtolower($itemStatus) }}"><i class="fa-solid {{ $statusIcon[$itemStatus] }}"></i> {{ $itemStatus === 'Active' ? 'ACTIVE WARRANTY' : strtoupper($itemStatus) }}</div>
                </article>
            @empty
                <p class="empty-items">Belum ada detail item warranty.</p>
            @endforelse
        </div>
    </section>
</main>
</body>
</html>
