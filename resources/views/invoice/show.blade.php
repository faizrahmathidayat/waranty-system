@extends('layout.layout')
@section('title', 'Invoice '.$invoice->invoice_number)
@section('content')
<script>window.invoicePaymentConfig={grandTotal:{{ (float) $invoice->grand_total }},paid:{{ (float) $invoice->paid_amount }},outstanding:{{ (float) $invoice->outstanding_amount }},canVoidPayment:{{ $invoice->order?->warranties->isEmpty() ? 'true' : 'false' }},payments:@json($invoice->payments->map(fn($payment)=>['id'=>$payment->id_payment,'status'=>$payment->status ?? 'ACTIVE'])->values()) };</script>
@if($invoice->status !== 'OPEN')<style>.invoice-cancel{display:none!important}</style>@endif
@php($orderWarranty = $invoice->order?->warranties->first())
@if($orderWarranty)<section class="content" style="padding-top:17px"><div class="container-fluid"><div class="alert alert-success mb-0">Warranty <strong>{{ $orderWarranty->kode_warranty }}</strong> sudah dibuat.<a target="_blank" class="btn btn-sm btn-success float-right" href="{{ route('warranty.digital',$orderWarranty->kode_warranty) }}">View Warranty</a></div></div></section>
@elseif($invoice->status === 'PAID' && $invoice->order?->status === 'COMPLETED' && $invoice->order?->details->where('warranty_eligible',true)->count())<section class="content" style="padding-top:17px"><div class="container-fluid text-right"><button class="btn btn-success warranty-generate" data-id="{{ $invoice->id_order }}" data-outstanding="0"><i class="fas fa-shield-alt"></i> Generate Warranty</button></div></section>@endif
<section class="content" style="padding-top:17px"><div class="container-fluid"><div class="card"><div class="card-header"><h3 class="card-title">INVOICE — {{ $invoice->invoice_number }}</h3><div class="card-tools"><a href="{{ url('/invoice') }}" class="btn btn-sm btn-secondary">Kembali</a> <a target="_blank" href="{{ url('/invoice/print/'.$invoice->id_invoice) }}" class="btn btn-sm btn-primary">Print</a>@if($invoice->status !== 'CANCELLED') <button class="btn btn-sm btn-danger invoice-cancel" data-id="{{ $invoice->id_invoice }}">Cancel</button>@endif</div></div><div class="card-body"><div class="row"><div class="col-md-4"><strong>Customer</strong><br>{{ $invoice->customer?->nama_customer }}<br>{{ $invoice->customer?->alamat }}<br>{{ $invoice->customer?->no_hp }} | {{ $invoice->customer?->email }}</div><div class="col-md-4"><strong>Invoice</strong><br>Date: {{ $invoice->invoice_date->format('d M Y') }}<br>Due: {{ $invoice->due_date?->format('d M Y') ?? '-' }}<br>Status: <span class="badge badge-info">{{ $invoice->status }}</span></div><div class="col-md-4"><strong>Order</strong><br>{{ $invoice->order?->order_number }} — {{ $invoice->order?->order_type }}<br>@if($invoice->order?->order_type==='BUILDING'){{ $invoice->order?->building?->nama_bangunan }} — {{ $invoice->order?->building?->alamat }}@else{{ $invoice->order?->vehicle?->no_polisi }} {{ $invoice->order?->vehicle?->merk }} {{ $invoice->order?->vehicle?->model }}@endif</div></div><hr><div class="table-responsive"><table class="table table-bordered"><thead class="bg-secondary"><tr><th>No</th><th>Description</th><th>Area / Luas</th><th>Qty</th><th>Unit Price</th><th>Discount</th><th>Subtotal</th></tr></thead><tbody>@foreach($invoice->items as $item)<tr><td>{{ $loop->iteration }}</td><td>{{ $item->treatment_name }} - {{ $item->product_name }}{{ $item->variant_name ? ' - '.$item->variant_name : '' }}</td><td>{{ $item->area }}@if($invoice->order?->order_type === 'BUILDING')<br><small>Luas: {{ $item->total_luas }} m²</small>@elseif($item->panjang)<br><small>{{ $item->panjang }} × {{ $item->lebar }} m; {{ $item->total_luas }} m²</small>@endif</td><td>{{ $item->quantity }} {{ $item->unit }}</td><td>Rp {{ number_format($item->unit_price,2,',','.') }}</td><td>Rp {{ number_format($item->discount,2,',','.') }}</td><td>Rp {{ number_format($item->subtotal,2,',','.') }}</td></tr>@endforeach</tbody></table></div><div class="row justify-content-end"><div class="col-md-4"><table class="table"><tr><th>Subtotal</th><td class="text-right">Rp {{ number_format($invoice->subtotal,2,',','.') }}</td></tr><tr><th>Discount</th><td class="text-right">Rp {{ number_format($invoice->discount,2,',','.') }}</td></tr><tr><th>Tax</th><td class="text-right">Rp {{ number_format($invoice->tax_amount,2,',','.') }}</td></tr><tr><th>Grand Total</th><td class="text-right font-weight-bold">Rp {{ number_format($invoice->grand_total,2,',','.') }}</td></tr><tr><th>Paid</th><td class="text-right">Rp {{ number_format($invoice->paid_amount,2,',','.') }}</td></tr><tr><th>Outstanding</th><td class="text-right">Rp {{ number_format($invoice->outstanding_amount,2,',','.') }}</td></tr></table></div></div></div></div></div></section>
<div class="modal fade" id="paymentModal"><div class="modal-dialog"><form id="paymentForm" class="modal-content" data-url="{{ url('/invoice/'.$invoice->id_invoice.'/payment') }}"><input type="hidden" name="_token" value="{{ csrf_token() }}"><div class="modal-header bg-primary"><h5 class="modal-title">Tambah Payment</h5><button type="button" class="close" data-dismiss="modal">×</button></div><div class="modal-body"><div class="form-group"><label>Payment Date *</label><input type="date" class="form-control" name="payment_date" value="{{ now()->format('Y-m-d') }}"></div><div class="form-group"><label>Grand Total</label><input class="form-control" readonly value="Rp {{ number_format($invoice->grand_total,2,',','.') }}"></div><div class="form-group"><label>Paid</label><input class="form-control" readonly value="Rp {{ number_format($invoice->paid_amount,2,',','.') }}"></div><div class="form-group"><label>Outstanding</label><div class="input-group"><input id="paymentOutstanding" class="form-control" readonly value="{{ $invoice->outstanding_amount }}"><div class="input-group-append"><button class="btn btn-outline-primary" type="button" id="payFull">Bayar Lunas</button></div></div></div><div class="form-group"><label>Amount *</label><input type="number" min="0.01" step="0.01" class="form-control" name="amount"></div><div class="form-group"><label>Payment Method *</label><select class="form-control" name="payment_method"><option>CASH</option><option>TRANSFER</option><option>EDC</option><option>QRIS</option><option>OTHER</option></select></div><div class="form-group"><label>Reference Number</label><input class="form-control" name="reference_number"></div><div class="form-group mb-0"><label>Notes</label><textarea class="form-control" name="notes"></textarea></div></div><div class="modal-footer"><button class="btn btn-primary">Simpan Payment</button></div></form></div></div>
<section class="content">
    <div class="container-fluid">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">PAYMENT HISTORY</h3>
                @if (!in_array($invoice->status, ['PAID', 'CANCELLED']))
                    <button class="btn btn-sm btn-primary float-right" data-toggle="modal" data-target="#paymentModal">Tambah Payment</button>
                @endif
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead class="bg-secondary"><tr><th>No</th><th>Tanggal</th><th>Type</th><th>Method</th><th>Reference</th><th>Amount</th><th>Input By</th><th>Status</th><th>Action</th></tr></thead>
                        <tbody>
                            @forelse ($invoice->payments as $payment)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $payment->payment_date }}</td>
                                    <td>{{ $payment->payment_type }}</td>
                                    <td>{{ $payment->payment_method }}</td>
                                    <td>{{ $payment->reference_number ?: '-' }}</td>
                                    <td>Rp {{ number_format($payment->amount, 2, ',', '.') }}</td>
                                    <td>{{ $payment->created_by ?: '-' }}</td>
                                    <td>
                                        @if (($payment->status ?? 'ACTIVE') === 'VOID')
                                            <span class="badge badge-secondary">VOID</span>
                                            @if ($payment->voided_at)
                                                <br><small>{{ $payment->voided_at->format('d/m/Y H:i') }}</small>
                                            @endif
                                            @if ($payment->void_reason)
                                                <br><small>{{ $payment->void_reason }}</small>
                                            @endif
                                        @else
                                            <span class="badge badge-success">ACTIVE</span>
                                        @endif
                                    </td>
                                    <td></td>
                                </tr>
                            @empty
                                <tr><td colspan="9" class="text-center">Belum ada payment.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
