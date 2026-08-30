@extends('layout.layout')
@section('content')
@section('title', 'Dashboard')
<style>
    .product-ranking {
        padding: 14px 18px;
        border-bottom: 1px solid #eee;
    }

    .product-ranking:last-child {
        border-bottom: none;
    }

    .product-ranking-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 8px;
    }

    .product-name {
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .product-number {
        width: 25px;
        height: 25px;

        display: inline-flex;
        align-items: center;
        justify-content: center;

        border-radius: 50%;

        background: #f1f3f5;
        color: #495057;

        font-size: 12px;
        font-weight: bold;
    }

    .product-ranking .progress {
        height: 9px;
        border-radius: 10px;
        background-color: #e9ecef;
    }

    .product-ranking .progress-bar {
        border-radius: 10px;
        transition: width 0.6s ease;
    }
</style>
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">Dashboard</h1>
            </div><!-- /.col -->
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="#">Home</a></li>
                    <li class="breadcrumb-item active">Dashboard</li>
                </ol>
            </div><!-- /.col -->
        </div><!-- /.row -->
    </div><!-- /.container-fluid -->
</div>

<section class="content">
    <div class="container-fluid">
        <!-- Small boxes (Stat box) -->
        <div class="row">
            <div class="col-lg-3 col-6">
                <!-- small box -->
                <div class="small-box bg-info">
                    <div class="inner">
                        <h3>{{ $count_warranty }}</h3>

                        <p>Warranty</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-shield-alt"></i>
                    </div>
                    <a href="/warranty" class="small-box-footer">More info <i class="fas fa-arrow-circle-right"></i></a>
                </div>
            </div>


            <!-- ./col -->
            <div class="col-lg-3 col-6">
                <!-- small box -->
                <div class="small-box bg-success">
                    <div class="inner">
                        <h3>{{ $count_customer }}</h3>

                        <p>Customer</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <a href="/customer" class="small-box-footer">More info <i class="fas fa-arrow-circle-right"></i></a>
                </div>
            </div>
            <!-- ./col -->
            <div class="col-lg-3 col-6">
                <!-- small box -->
                <div class="small-box bg-danger">
                    <div class="inner">
                        <h3>{{ $count_product }}</h3>

                        <p>Product</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-box-open"></i>
                    </div>
                    <a href="/product" class="small-box-footer">More info <i class="fas fa-arrow-circle-right"></i></a>
                </div>
            </div>

            <div class="col-lg-3 col-6">
                <!-- small box -->
                <div class="small-box bg-warning">
                    <div class="inner">
                        <h3>{{ $count_user }}</h3>

                        <p>User</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-user"></i>
                    </div>
                    <a href="/user" class="small-box-footer">More info <i class="fas fa-arrow-circle-right"></i></a>
                </div>
            </div>
            <!-- ./col -->



            <!-- ./col -->
        </div>

        <div class="row">
            <div class="col-lg-8">

                <div class="card">
                    <div class="card-header border-bottom">
                        <div class="d-flex justify-content-between">
                            <h3 class="card-title">📈 <strong>Warranty Overview</strong></h3>
                            <select id="tahun_chart" class="form-control form-control-sm" style="width:90px;">
                                <option value="2026">2026</option>
                                <option value="2025">2025</option>
                                <option value="2024">2024</option>
                            </select>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="d-flex">
                            <p class="d-flex flex-column">
                                <span class="text-bold text-lg">{{ $count_warranty }}</span>
                                <span>Warranty Tahun Ini</span>
                            </p>

                        </div>
                        <!-- /.d-flex -->

                        <div class="position-relative mb-4">
                            <canvas id="warranty-chart" height="200"></canvas>
                        </div>

                        <div class="d-flex flex-row justify-content-end">
                            <span class="mr-2">
                                <i class="fas fa-square text-primary"></i> Per Bulan
                            </span>

                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">

                <div class="card" style="height: 415px;">

                    <div class="card-header border-bottom" style="height: 60px;">

                        <h3 class="card-title">

                            <i class="fas fa-chart-pie"></i>

                            Status Warranty

                        </h3>

                    </div>

                    <div class="card-body">

                        <div class="row">

                            <div class="col-md-7">

                                <canvas id="status-chart" width="350" height="350"></canvas>

                            </div>

                            <div class="col-md-5">

                                <div class="mb-3">

                                    <i class="fas fa-circle text-success"></i>

                                    <strong>Active</strong>

                                    <br>

                                    <span id="status_active">0 (0%)</span>

                                </div>

                                <div>

                                    <i class="fas fa-circle text-danger"></i>

                                    <strong>Expired</strong>

                                    <br>

                                    <span id="status_expired">0 (0%)</span>

                                </div>

                            </div>

                        </div>

                    </div>

                </div>

            </div>
        </div>

        <div class="row">
            <div class="col-lg-6">
                <div class="card">
                    <div class="card-header border-0">
                        <h3 class="card-title">📋 <strong>Latest Warranty</strong> </h3>
                    </div>
                    <div class="card-body table-responsive p-0" style="height: 300px;">
                        <table class="table table-head-fixed text-nowrap">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Customer</th>
                                    <th>Product</th>
                                    <th>Tanggal Pasang</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody id="latest_warranty">
                                <!-- Data will be populated here -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="card">
                    <div class="card-header">

                        <h3 class="card-title">

                            <i class="fas fa-calendar-times text-danger"></i>

                            <strong>Expired (7 Hari Ke Depan)</strong>

                        </h3>

                    </div>

                    <div class="card-body p-0" style="overflow-y: auto; height: 300px;">

                        <div id="expired_soon_list">

                        </div>

                    </div>

                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-lg-12">
                <div class="card">

                    <div class="card-header">

                        <h3 class="card-title">

                            <i class="fas fa-trophy"></i>

                            Product Paling Laris

                        </h3>

                    </div>

                    <div class="card-body p-0">

                        <div id="product_terlaris"></div>

                    </div>

                </div>
            </div>
        </div>

    </div>
    <!-- /.row -->
    <!-- Main row -->
    <div class="row">
        <!-- Left col -->
        <section class="col-lg-7 connectedSortable">


        </section>

        <!-- right col -->
    </div>
    <!-- /.row (main row) -->
    </div><!-- /.container-fluid -->


</section>

@endsection