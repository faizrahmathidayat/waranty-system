@extends('layout.layout')

@section('title', 'Laporan Mobil Masuk')

@section('content')

<section class="content" style="padding-top: 17px;">

    <style>
        .report-paper {
            background: #fff;
            width: 100%;
            min-height: 900px;
            padding: 35px 40px;
            box-shadow: 0 0 8px rgba(0, 0, 0, .15);
            border: 1px solid #ddd;
        }

        .report-header {
            text-align: center;
        }

        .report-header h2 {
            margin: 0;
            font-size: 24px;
            font-weight: bold;
        }

        .report-header h3 {
            margin: 5px 0;
            font-size: 20px;
            font-weight: bold;
        }

        .report-header p {
            margin-top: 10px;
        }

        .report-summary {
            margin-bottom: 15px;
            font-size: 15px;
        }

        .table-report {
            width: 100%;
            border-collapse: collapse;
            font-size: 13px;
        }

        .table-report th,
        .table-report td {
            border: 1px solid #333;
            padding: 7px 8px;
        }

        .table-report th {
            text-align: center;
            font-weight: bold;
        }

        .table-report td:first-child {
            text-align: center;
            width: 40px;
        }

        .report-footer {
            margin-top: 30px;
            font-size: 12px;
        }

        .signature {
            width: 250px;
            margin-left: auto;
            text-align: center;
        }

        @media print {

            body * {
                visibility: hidden;
            }

            .report-paper,
            .report-paper * {
                visibility: visible;
            }

            .report-paper {
                position: absolute;
                left: 0;
                top: 0;

                width: 100%;

                box-shadow: none;
                border: none;

                padding: 20px;
            }

            .no-print {
                display: none !important;
            }

        }
    </style>

    <div class="container-fluid">

        <div class="row">

            <div class="col-12">


                <!-- ========================= -->
                <!-- FILTER TANGGAL -->
                <!-- ========================= -->

                <div class="card">

                    <div class="card-header">

                        <h3 class="card-title">

                            <i class="fas fa-calendar-alt"></i>

                            <strong>
                                Filter Tanggal
                            </strong>

                        </h3>

                    </div>


                    <div class="card-body">

                        <div class="row">

                            <!-- Tanggal Mulai -->
                            <div class="col-md-3">

                                <div class="form-group">

                                    <label>
                                        Tanggal Mulai
                                    </label>

                                    <input
                                        type="date"
                                        id="tanggal_mulai"
                                        class="form-control">

                                </div>

                            </div>


                            <!-- Tanggal Akhir -->
                            <div class="col-md-3">

                                <div class="form-group">

                                    <label>
                                        Tanggal Akhir
                                    </label>

                                    <input
                                        type="date"
                                        id="tanggal_akhir"
                                        class="form-control">

                                </div>

                            </div>


                            <!-- Tampilkan -->
                            <div class="col-md-2">

                                <div class="form-group">

                                    <label>
                                        &nbsp;
                                    </label>

                                    <button
                                        type="button"
                                        id="btn_filter"
                                        class="btn btn-primary btn-block">

                                        <i class="fas fa-search"></i>
                                        Tampilkan

                                    </button>

                                </div>

                            </div>


                            <!-- Reset -->
                            <div class="col-md-2">

                                <div class="form-group">

                                    <label>
                                        &nbsp;
                                    </label>

                                    <button
                                        type="button"
                                        id="btn_reset"
                                        class="btn btn-secondary btn-block">

                                        <i class="fas fa-sync-alt"></i>

                                        Reset

                                    </button>

                                </div>

                            </div>

                        </div>

                    </div>

                </div>


                <!-- ========================= -->
                <!-- SUMMARY -->
                <!-- ========================= -->




                <!-- ========================= -->
                <!-- DATA MOBIL -->
                <!-- ========================= -->

                <div class="card">

                    <div class="card-header">

                        <h3 class="card-title">

                            <i class="fas fa-list"></i>

                            <strong>
                                Data Mobil Masuk
                            </strong>

                        </h3>


                        <div class="card-tools">

                            <button
                                type="button"
                                id="btn_export_excel"
                                class="btn btn-success btn-sm">

                                <i class="fas fa-file-excel"></i>

                                Export Excel

                            </button>


                            <button
                                type="button"
                                id="btn_export_pdf"
                                class="btn btn-danger btn-sm">

                                <i class="fas fa-file-pdf"></i>

                                Export PDF

                            </button>


                            <button
                                type="button"
                                id="btn_print"
                                class="btn btn-secondary btn-sm">

                                <i class="fas fa-print"></i>

                                Print

                            </button>

                        </div>

                    </div>


                    <div class="card-body">

                        <div class="table-responsive">

                            <!-- <table
                                id="tabel_laporan_mobil"
                                class="table table-bordered table-striped table-hover"
                                style="width:100%;">

                                <thead>

                                    <tr>

                                        <th>No</th>

                                        <th>Tanggal Pasang</th>

                                        <th>Kode Warranty</th>

                                        <th>No Polisi</th>

                                        <th>Customer</th>

                                        <th>Merk Mobil</th>

                                        <th>Tipe Mobil</th>

                                        <th>Warna</th>

                                        <th>Tahun</th>

                                        <th>Product</th>

                                        <th>Installer</th>

                                    </tr>

                                </thead>

                                <tbody>

                                </tbody>

                            </table> -->

                            <div class="report-paper">

                                <div class="report-header">

                                    <h2>WARRANTY SYSTEM</h2>

                                    <h3>LAPORAN MOBIL MASUK</h3>

                                    <p>
                                        Periode:
                                        <strong id="periode_laporan">
                                            -
                                        </strong>
                                    </p>

                                </div>

                                <hr>

                                <div class="report-summary">

                                    <strong>Total Mobil Masuk :</strong>

                                    <span id="total_mobil">
                                        0
                                    </span>

                                </div>

                                <table class="table-report">

                                    <thead>

                                        <tr>
                                            <th>No</th>
                                            <th>No Order</th>
                                            <th>Tanggal</th>
                                            <th>No. Polisi</th>
                                            <th>Customer</th>
                                            <th>Kendaraan</th>
                                            <th>Product</th>
                                            <th>Installer</th>
                                        </tr>

                                    </thead>

                                    <tbody id="report_mobil_body">

                                    </tbody>

                                </table>

                                <div class="report-footer">

                                    <div>
                                        Dicetak pada:
                                        <span id="tanggal_cetak"></span>
                                    </div>

                                    <br><br>

                                    <!-- <div class="signature">
                                        Mengetahui,
                                        <br><br><br>
                                        ______________________
                                    </div> -->

                                </div>

                            </div>

                        </div>

                    </div>

                </div>


            </div>

        </div>

    </div>

</section>

@endsection
