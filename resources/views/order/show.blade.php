@extends('layout.layout')
@section('title', 'Detail Order')
@section('content')
<style>.card-tools .btn + .btn{margin-left:.5rem}</style>
@php
    $invoice = $order->activeInvoice;
    $warranty = $order->warranties->first();
    $eligible = $order->details->where('warranty_eligible', true)->count();
@endphp
<section class="content" style="padding-top:17px"><div class="container-fluid">
    <div class="card">
        <div class="card-header"><h3 class="card-title">{{ $order->order_number }} — {{ $order->order_type }}</h3>
            <div class="card-tools">
                <a href="/order" class="btn btn-sm btn-secondary">Kembali</a>
                @if($order->status === 'OPEN')
                    <a href="/order/edit/{{ $order->id_order }}" class="btn btn-sm btn-warning">Edit Order</a>
                    <button class="btn btn-sm btn-primary invoice-generate" data-id="{{ $order->id_order }}">Generate Invoice</button>
                    <button class="btn btn-sm btn-danger order-detail-action" data-action="cancel">Cancel Order</button>
                @elseif($invoice)
                    <a href="{{ url('/invoice/show/'.$invoice->id_invoice) }}" class="btn btn-sm btn-primary">View Invoice</a>
                @endif
                @if($order->status === 'COMPLETED' && optional($invoice)->status === 'PAID' && !$warranty && $eligible)
                    <button class="btn btn-sm btn-success warranty-generate" data-id="{{ $order->id_order }}">Generate Warranty</button>
                @elseif($warranty)
                    <a target="_blank" class="btn btn-sm btn-success" href="{{ route('warranty.digital',$warranty->kode_warranty) }}">View Warranty</a>
                @endif
            </div>
        </div>
        <div class="card-body">
            <div class="row"><div class="col-md-3"><b>Customer</b><br>{{ optional($order->customer)->nama_customer }}</div><div class="col-md-3"><b>Asset</b><br>{{ $order->order_type === 'BUILDING' ? optional($order->building)->nama_bangunan : trim((optional($order->vehicle)->no_polisi ?? '').' '.(optional($order->vehicle)->merk ?? '')) }}</div><div class="col-md-3"><b>Order Status</b><br><span class="badge badge-info">{{ $order->status }}</span></div><div class="col-md-3"><b>Invoice</b><br>{{ optional($invoice)->status ?? '-' }}</div></div><hr>
            @foreach($order->details as $detail)
                <div class="card card-outline card-secondary"><div class="card-header py-2"><b>{{ $detail->area ?: 'Item '.$loop->iteration }}</b></div><div class="card-body"><div class="row"><div class="col-md-3"><b>Treatment</b><br>{{ $detail->treatment_name_snapshot }}</div><div class="col-md-3"><b>Product</b><br>{{ $detail->product_name_snapshot }}{{ $detail->variant_name_snapshot ? ' - '.$detail->variant_name_snapshot : '' }}</div><div class="col-md-2"><b>Qty</b><br>{{ $detail->quantity }} {{ $detail->unit }}</div><div class="col-md-2"><b>Warranty</b><br>{{ $detail->warranty_eligible ? $detail->warranty_months_snapshot.' Bulan' : 'Tidak eligible' }}</div>@if($order->order_type === 'BUILDING')<div class="col-md-2"><b>Luas</b><br>{{ $detail->total_luas }} m²</div>@endif</div></div></div>
            @endforeach
        </div>
    </div>
</div></section>
<script>window.orderDetailConfig={id:{{ $order->id_order }}};</script>
@endsection
