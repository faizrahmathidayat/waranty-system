@extends('layout/layout')
@section('content')
@section('title', 'Product')

<section class="content" style="padding-top: 17px;">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">

                <div class="card">

                    <div class="card-header">

                        <button type="button"
                            class="btn btn-primary"
                            data-toggle="modal"
                            data-target="#modal_tambah_product">

                            Tambah Product

                        </button>

                    </div>

                    @include('product.create')

                    <div class="card-body">

                        <div class="table-responsive grid" style="padding:5px;">

                            <table class="table table-bordered table-striped"
                                id="tabel_product"
                                style="width:100%;">

                                <thead class="bg-secondary">

                                    <tr>

                                        <th>No</th>

                                        <th>Nama Product</th>

                                        <th>Brand</th>

                                        <th>Jenis</th>
                                        <th>Product Type</th>

                                        <th>Masa Garansi (Bulan)</th>

                                        <th>Keterangan</th>

                                        <th>Action</th>

                                    </tr>

                                </thead>

                                <tbody>

                                </tbody>

                            </table>

                        </div>

                    </div>

                    @include('product.detail')

                </div>

            </div>
        </div>
    </div>
</section>

@endsection
