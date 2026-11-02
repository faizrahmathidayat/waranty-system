<!doctype html>
@php
    $orderBrand = optional($invoice->order)->brand;
    $isLexent = $orderBrand === 'LEXENT';
    $isGlosspro = $orderBrand === 'GLOSSPRO';
    $hasLetterhead = $isLexent || $isGlosspro;
    $isAutomotive = optional($invoice->order)->order_type === 'AUTOMOTIVE';
    $brandLogo = $isGlosspro ? asset('images/glosspro-invoice-logo.png') : ($isLexent ? asset(\App\Models\Order::BRAND_LOGOS['LEXENT']) : null);
@endphp
<html><head><meta charset="utf-8"><title>{{ $invoice->invoice_number }}</title><style>
body{font-family:Arial;margin:0;font-size:12px;color:#000}
h1{margin:0}
table{width:100%;border-collapse:collapse;margin-top:18px}
th,td{border:1px solid #555;padding:7px;text-align:left}
.right{text-align:right}
.noborder td{border:0;padding:2px}
.pg{margin:0;border-collapse:collapse}
.pg>thead>tr>td,.pg>tbody>tr>td,.pg>tfoot>tr>td{border:0;padding:0}
.pg-body{padding:0 15mm}
.lx-head{display:flex;justify-content:space-between;align-items:center;border-bottom:3px solid #3b78c3;padding-bottom:14px;margin-bottom:22px}
.lx-head img{height:34px}
.lx-head.gp{border-bottom-color:#e10600}.lx-head.gp img{height:78px}
.lx-contact{text-align:right;line-height:1.55;color:#6b7280;font-size:13px}
.lx-contact b{display:block;color:#16233b;font-family:Cambria,Georgia,serif;font-size:13px;letter-spacing:.2px}
.terms-page{page-break-before:always;break-before:page}
.terms{page-break-inside:avoid;font-size:15px;line-height:1.55;text-align:justify}
.terms ol{margin:8px 0 16px;padding-left:22px}.terms li{margin-bottom:7px}
.lx-wm{position:fixed;top:0;left:0;right:0;bottom:0;overflow:hidden;z-index:-1;pointer-events:none}.lx-wm img{position:absolute;right:0;bottom:0;width:48%}
.lx-foot{text-align:center;color:#111;margin-top:30px}
.lx-foot b{display:block;font-size:13px;letter-spacing:.3px;margin-bottom:4px}
.lx-foot span{font-size:10.5px;letter-spacing:.2px}
.lx-foot i{display:block;height:14px;background:#1e1e1e;margin-top:14px}
.lx-foot.gp{color:#000}.lx-foot.gp b{font-weight:400;font-size:12px;letter-spacing:0;margin-bottom:2px}.lx-foot.gp span{font-size:12px;letter-spacing:0}.lx-foot.gp i{height:20px;margin-top:12px;-webkit-print-color-adjust:exact;print-color-adjust:exact;background:linear-gradient(90deg,#d91a1a 0 18.2%,#b51c1c 0 36.3%,#8f1d1e 0 51.2%,#6e1f20 0 66.2%,#482022 0 81.1%,#312123 0 96.1%,#040505 0 100%)}
@media print{
    @page{size:A4 portrait;margin:0}
    button{display:none}
    .lx-foot{position:fixed;left:0;right:0;bottom:0;margin:0}
}
</style></head><body>
@if($isLexent)<div class="lx-wm"><img src="{{ asset('images/lexent-watermark.png') }}" alt=""></div>@endif
<table class="pg">
<thead><tr><td><div style="height:{{ $hasLetterhead ? '14mm' : '12mm' }}"></div></td></tr></thead>
<tfoot><tr><td><div style="height:{{ $hasLetterhead ? '38mm' : '12mm' }}"></div></td></tr></tfoot>
<tbody><tr><td><div class="pg-body">
@if($isLexent)
<div class="lx-head">
    <img src="{{ $brandLogo }}" alt="LEXENT">
    <div class="lx-contact"><b>CONTACT INFORMATION</b>Ruko La Valle, Citra Garden Serpong No.66 Blk B17<br>0858 8889 9558<br>www.lexentindonesia.com</div>
</div>
@elseif($isGlosspro)
<div class="lx-head gp">
    <img src="{{ $brandLogo }}" alt="GlossPro">
    <div class="lx-contact"><b>CONTACT INFORMATION</b>Ruko La Valle, Citra Garden Serpong No.66 Blk B17<br>Cisauk, Kota Tangerang Selatan, Banten 15341<br>www.glossproindonesia.com</div>
</div>
@endif
<h1>INVOICE</h1><p><b>{{ $invoice->invoice_number }}</b><br>Date: {{ $invoice->invoice_date->format('d M Y') }}</p>
<table class="noborder"><tr><td><b>Customer</b><br>{{ optional($invoice->customer)->nama_customer }}<br>{{ optional($invoice->customer)->alamat }}<br>{{ optional($invoice->customer)->no_hp }}</td><td><b>Order</b><br>{{ optional($invoice->order)->order_number }} — {{ optional($invoice->order)->order_type }}</td></tr></table>
<table><tr><th>No</th><th>Description</th><th>Area / Luas</th><th>Qty</th>@unless($isAutomotive)<th>Unit Price</th><th>Discount</th><th>Subtotal</th>@endunless</tr>@foreach($invoice->items as $item)<tr><td>{{ $loop->iteration }}</td><td>{{ $item->treatment_name ? $item->treatment_name.' - ' : '' }}{{ $item->product_name }} {{ $item->variant_name }}</td><td>{{ $item->area ?: '-' }} @if(optional($invoice->order)->order_type === 'BUILDING')(Luas: {{ $item->total_luas }} m²)@elseif($item->panjang)({{ $item->panjang }} × {{ $item->lebar }} m; {{ $item->total_luas }} m²)@endif</td><td>{{ $item->quantity }} {{ $item->unit }}</td>@unless($isAutomotive)<td class="right">Rp {{ number_format($item->unit_price,2,',','.') }}</td><td class="right">Rp {{ number_format($item->discount,2,',','.') }}</td><td class="right">Rp {{ number_format($item->subtotal,2,',','.') }}</td>@endunless</tr>@endforeach</table>
<table class="noborder" style="margin-left:auto;width:40%"><tr><td>Subtotal</td><td class="right">Rp {{ number_format($invoice->subtotal,2,',','.') }}</td></tr><tr><td>Discount</td><td class="right">Rp {{ number_format($invoice->discount,2,',','.') }}</td></tr><tr><td>Tax</td><td class="right">Rp {{ number_format($invoice->tax_amount,2,',','.') }}</td></tr><tr><td><b>Grand Total</b></td><td class="right"><b>Rp {{ number_format($invoice->grand_total,2,',','.') }}</b></td></tr><tr><td>Outstanding</td><td class="right">Rp {{ number_format($invoice->outstanding_amount,2,',','.') }}</td></tr></table>
@if($bankAccounts->isNotEmpty())<div style="margin-top:24px"><b>Pembayaran melalui transfer:</b><br>@foreach($bankAccounts as $account){{ $account->bank_name }} — {{ $account->account_number }} a.n. {{ $account->account_holder }}<br>@endforeach</div>@endif
<div class="terms-page">
<div class="terms">
    <b>KETENTUAN GARANSI DAN LAYANAN</b>
    <ol>
        <li>Garansi berlaku sesuai jenis produk atau layanan, masa garansi, dan ketentuan yang tercantum pada invoice atau kartu garansi resmi.</li>
        <li>Garansi berlaku sejak tanggal pemasangan atau tanggal yang ditetapkan pada dokumen garansi.</li>
        <li>Garansi hanya berlaku untuk produk dan pekerjaan yang tercantum pada invoice serta tidak dapat dialihkan kepada pihak lain tanpa persetujuan perusahaan.</li>
        <li>Garansi mencakup kerusakan atau ketidaksesuaian yang terbukti disebabkan oleh cacat produk atau kesalahan pemasangan, sepanjang termasuk dalam cakupan garansi layanan terkait.</li>
        <li>Garansi tidak berlaku untuk kerusakan akibat kelalaian pengguna, kecelakaan, benturan, bencana alam, penggunaan yang tidak sesuai, perawatan yang salah, atau tindakan pihak lain yang berada di luar tanggung jawab perusahaan.</li>
        <li>Klaim garansi wajib disertai invoice atau bukti pembelian yang sah. Perusahaan berhak melakukan pemeriksaan sebelum menentukan penyelesaian klaim.</li>
        <li>Perbaikan atau penggantian yang disetujui akan dilakukan sesuai cakupan dan ketentuan garansi produk atau layanan terkait.</li>
        <li>Hal-hal yang tidak tercantum dalam ketentuan ini akan mengikuti kebijakan garansi resmi perusahaan dan ketentuan hukum yang berlaku.</li>
    </ol>
    Demikian surat penawaran ini kami sampaikan. Besar harapan kami dapat menjalin kerja sama yang baik dengan Bapak/Ibu. Atas perhatian dan kesempatan yang diberikan, kami ucapkan terima kasih.
</div>
</div>
</div></td></tr></tbody>
</table>
@if($isLexent)
<div class="lx-foot"><b>LEXENT :</b><span>BUILDING WINDOWFILM &nbsp;•&nbsp; AUTOMOTIVE WINDOWFILM &nbsp;•&nbsp; PAINT PROTECTION FILM</span><i></i></div>
@elseif($isGlosspro)
<div class="lx-foot gp"><b>Glosspro</b><span>Ruko La Valle, Citra Garden Serpong No.66 Blk B17, Cisauk, Kec. Cisauk, Kota Tangerang Selatan, Banten 15341</span><i></i></div>
@endif
<script>window.onload=()=>window.print()</script></body></html>
