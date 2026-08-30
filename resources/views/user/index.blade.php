@extends('layout.layout')

@section('content')

@section('title', 'User')

<section class="content" style="padding-top: 17px;">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">


                        <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#modal_tambah_user" id="">
                            Tambah User
                        </button>

                    </div>

                    @include('user.create')
                    <!-- /.card-header -->
                    <div class="card-body">
                        <div class="table-responsive grid" style="padding : 5px;">
                            <table class="table table-bordered table-striped" id="tabel_user" style="width: 100%;">
                                <thead class="bg-secondary">
                                    <tr>
                                        <th width="5%">No</th>
                                        <th>Name</th>
                                        <th>Username</th>
                                        <th>Role</th>
                                        <th>Status</th>
                                        <th width="10%">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    @include('user.detail')
                    <!-- /.card-body -->
                </div>
            </div>
        </div>
    </div>
</section>
@endsection