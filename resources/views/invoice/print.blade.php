<!doctype html>
@php
    $orderBrand = optional($invoice->order)->brand;
    $isLexent = $orderBrand === 'LEXENT';
    $isGlosspro = $orderBrand === 'GLOSSPRO';
    $hasLetterhead = $isLexent || $isGlosspro;
    $isAutomotive = optional($invoice->order)->order_type === 'AUTOMOTIVE';
    $brandLogo = $isGlosspro ? public_path('images/glosspro-invoice-logo.png') : ($isLexent ? public_path(\App\Models\Order::BRAND_LOGOS['LEXENT']) : null);
    $marginBottom = $hasLetterhead ? '38mm' : '12mm';
    $marginTop = $hasLetterhead ? '14mm' : '12mm';
@endphp
<html><head><meta charset="utf-8"><title>{{ $invoice->invoice_number }}</title><style>
@page{margin:{{ $marginTop }} 15mm {{ $marginBottom }} 15mm}
body{font-family:Helvetica,Arial,sans-serif;margin:0;font-size:12px;color:#000}
h1{margin:0;font-size:22px}
table{width:100%;border-collapse:collapse;margin-top:18px}
th,td{border:1px solid #555;padding:7px;text-align:left;vertical-align:top}
.right{text-align:right}
.summary td{vertical-align:top}.summary .notes-cell{width:58%;padding-right:18px}.summary .totals-cell{width:42%}
.noborder td{border:0;padding:2px}
.lx-head{margin-top:0;margin-bottom:22px;border-bottom:3px solid #3b78c3}
.lx-head.gp{border-bottom-color:#e10600}
.lx-head td{border:0;padding:0 0 14px 0;vertical-align:middle}
.lx-head img{height:34px}
.lx-head.gp img{height:78px}
.lx-contact{text-align:right;line-height:1.55;color:#6b7280;font-size:13px}
.lx-contact b{color:#16233b;font-family:"Times New Roman",serif;font-size:13px}
.terms-page{page-break-before:always}
.terms{font-size:15px;line-height:1.55;text-align:justify}
.terms ol{margin:8px 0 16px;padding-left:22px}.terms li{margin-bottom:7px}
.lx-wm{position:fixed;right:-15mm;bottom:-38mm;width:100mm}
.lx-wm img{width:100mm}
.lx-foot{position:fixed;left:-15mm;right:-15mm;bottom:-38mm;text-align:center;color:#111}
.lx-foot b{font-size:13px;letter-spacing:.3px}
.lx-foot span{font-size:10.5px;letter-spacing:.2px}
.lx-foot .bar{width:100%;margin-top:14px;border-collapse:collapse}
.lx-foot .bar td{border:0;padding:0;height:14px;background:#1e1e1e}
.lx-foot.gp{color:#000}.lx-foot.gp b{font-weight:normal;font-size:12px;letter-spacing:0}.lx-foot.gp span{font-size:12px;letter-spacing:0}
.lx-foot.gp .bar{margin-top:12px}.lx-foot.gp .bar td{height:20px}
</style></head><body>
@if($isLexent)<div class="lx-wm"><img src="{{ public_path('images/lexent-watermark.png') }}" alt=""></div>@endif
@if($isLexent)
<div class="lx-foot"><b>LEXENT :</b><br><span>BUILDING WINDOWFILM &nbsp;•&nbsp; AUTOMOTIVE WINDOWFILM &nbsp;•&nbsp; PAINT PROTECTION FILM</span><table class="bar"><tr><td></td></tr></table></div>
@elseif($isGlosspro)
<div class="lx-foot gp"><b>Glosspro</b><br><span>Ruko La Valle, Citra Garden Serpong No.66 Blk B17, Cisauk, Kec. Cisauk, Kota Tangerang Selatan, Banten 15341</span><table class="bar"><tr><td style="width:18.2%;background:#d91a1a"></td><td style="width:18.1%;background:#b51c1c"></td><td style="width:14.9%;background:#8f1d1e"></td><td style="width:15%;background:#6e1f20"></td><td style="width:14.9%;background:#482022"></td><td style="width:15%;background:#312123"></td><td style="width:3.9%;background:#040505"></td></tr></table></div>
@endif
@if($isLexent)
<table class="lx-head"><tr>
    <td><img src="{{ $brandLogo }}" alt="LEXENT"></td>
    <td class="lx-contact"><b>CONTACT INFORMATION</b><br>Ruko La Valle, Citra Garden Serpong No.66 Blk B17<br>0858 8889 9558<br>www.lexentindonesia.com</td>
</tr></table>
@elseif($isGlosspro)
<table class="lx-head gp"><tr>
    <td><img src="{{ $brandLogo }}" alt="GlossPro"></td>
    <td class="lx-contact"><b>CONTACT INFORMATION</b><br>Ruko La Valle, Citra Garden Serpong No.66 Blk B17<br>Cisauk, Kota Tangerang Selatan, Banten 15341<br>www.glossproindonesia.com</td>
</tr></table>
@endif
<h1>INVOICE</h1><p><b>{{ $invoice->invoice_number }}</b><br>Date: {{ $invoice->invoice_date->format('d M Y') }}</p>
<table class="noborder"><tr><td><b>Customer</b><br>{{ optional($invoice->customer)->nama_customer }}<br>{{ optional($invoice->customer)->alamat }}<br>{{ optional($invoice->customer)->no_hp }}</td><td><b>Order</b><br>{{ optional($invoice->order)->order_number }} — {{ optional($invoice->order)->order_type }}</td></tr></table>
@if(($design ?? 1) === 2)
<table><tr><th>No.</th><th>Produk</th><th>Tipe Produk</th><th>Qty</th></tr>@foreach($invoice->items as $item)<tr><td>{{ $loop->iteration }}</td><td>{{ $item->product_name }} {{ $item->variant_name }}</td><td>{{ optional(optional(optional($item->orderDetail)->product)->productType)->name ?: '-' }}</td><td>{{ $item->quantity + 0 }} {{ $item->unit }}</td></tr>@endforeach</table>
@else
<table><tr><th>No</th><th>Description</th><th>Area / Luas</th><th>Qty</th>@unless($isAutomotive)<th>Unit Price</th><th>Discount</th><th>Subtotal</th>@endunless</tr>@foreach($invoice->items as $item)<tr><td>{{ $loop->iteration }}</td><td>{{ $item->treatment_name ? $item->treatment_name.' - ' : '' }}{{ $item->product_name }} {{ $item->variant_name }}</td><td>{{ $item->area ?: '-' }} @if(optional($invoice->order)->order_type === 'BUILDING')(Luas: {{ $item->total_luas }} m²)@elseif($item->panjang)({{ $item->panjang }} × {{ $item->lebar }} m; {{ $item->total_luas }} m²)@endif</td><td>{{ $item->quantity }} {{ $item->unit }}</td>@unless($isAutomotive)<td class="right">Rp {{ number_format($item->unit_price,2,',','.') }}</td><td class="right">Rp {{ number_format($item->discount,2,',','.') }}</td><td class="right">Rp {{ number_format($item->subtotal,2,',','.') }}</td>@endunless</tr>@endforeach</table>
@endif
<table class="noborder summary"><tr><td class="notes-cell">@if(filled(optional($invoice->order)->notes))<b>Notes</b><br>{!! nl2br(e($invoice->order->notes)) !!}@endif</td><td class="totals-cell"><table class="noborder" style="margin-top:0;width:100%"><tr><td>Subtotal</td><td class="right">Rp {{ number_format($invoice->subtotal,2,',','.') }}</td></tr><tr><td>Discount</td><td class="right">Rp {{ number_format($invoice->discount,2,',','.') }}</td></tr><tr><td>Tax</td><td class="right">Rp {{ number_format($invoice->tax_amount,2,',','.') }}</td></tr><tr><td><b>Grand Total</b></td><td class="right"><b>Rp {{ number_format($invoice->grand_total,2,',','.') }}</b></td></tr><tr><td>Outstanding</td><td class="right">Rp {{ number_format($invoice->outstanding_amount,2,',','.') }}</td></tr></table></td></tr></table>
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
</body></html>
