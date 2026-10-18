@extends('layout/layout')
@section('content')
@section('title', 'Warranty')

<section class="content" style="padding-top:17px;">
    <div class="container-fluid">

        <div class="row">

            <div class="col-12">

                <div class="card">

                    <div class="card-header">
                        <div class="d-flex align-items-center">
                            <select
                                id="filter_product"
                                class="form-control ml-2 mr-2"
                                style="width: 220px;">

                                <option value="">-- Semua Product --</option>

                                @foreach($products as $product)

                                <option value="{{ $product->id_product }}">
                                    {{ $product->nama_produk }}
                                </option>

                                @endforeach

                            </select>


                            <select
                                id="filter_status"
                                class="form-control"
                                style="width: 160px;">

                                <option value="">-- Semua Status --</option>
                                <option value="Active">ACTIVE</option>
                                <option value="Expired">EXPIRED</option>
                                <option value="Void">VOID</option>

                            </select>

                        </div>
                    </div>

                    @include('warranty.create')

                    <div class="card-body">

                        <div class="table-responsive grid" style="padding:5px;">

                            <table class="table table-bordered table-striped"
                                id="tabel_warranty"
                                style="width:100%;">

                                <thead class="bg-secondary">

                                    <tr>

                                        <th>No</th>
                                        <th>Kode Warranty</th>
                                        <th>Customer</th>
                                        <th>Product</th>
                                        <th>No Polisi</th>
                                        <th>No Invoice</th>
                                        <th>Tanggal Pasang</th>
                                        <th>Tanggal Expired</th>
                                        <th>Status</th>
                                        <th>Action</th>

                                    </tr>

                                </thead>

                                <tbody>

                                </tbody>

                            </table>

                        </div>

                    </div>

                    @include('warranty.detail')

                </div>

            </div>

        </div>

    </div>

</section>

@endsection
