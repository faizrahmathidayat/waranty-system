@extends('layout/layout')
@section('content')
@section('title', 'Customer')

<section class="content" style="padding-top: 17px;">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <!-- <h4 class="card-title">Customer</h4>
                        <br> -->

                        <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#modal_tambah_customer" id="">
                            Tambah Customer
                        </button>

                    </div>

                    @include ('customer.create')
                    <!-- /.card-header -->
                    <div class="card-body">
                        <div class="table-responsive grid" style="padding : 5px;">
                            <table class="table table-bordered table-striped" id="tabel_customer" style="width: 100%;">
                                <thead class="bg-secondary">
                                    <tr>
                                        <th>No</th>
                                        <TH>Nama Customer</TH>
                                        <th>No Handphone</th>
                                        <th>Email</th>
                                        <th>Alamat</th>
                                        <th>Status</th>
                                        <th>action</th>
                                    </tr>
                                </thead>
                                <tbody>

                                </tbody>
                            </table>
                        </div>
                    </div>

                    @include('customer.detail')
                    <!-- /.card-body -->
                </div>
            </div>
        </div>
    </div>

</section>
@endsection