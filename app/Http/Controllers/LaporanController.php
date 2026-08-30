<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Warranty;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class LaporanController extends Controller
{
    /**
     * Halaman Laporan Mobil Masuk
     */
    public function mobil()
    {
        if (empty(Auth::user()->username)) {
            return redirect()->to('/login');
        }

        return view('laporan_mobil.index', [
            'title'     => 'Laporan Mobil Masuk',
            'navbar'    => 'Laporan Mobil Masuk'
        ]);
    }


    /**
     * Data Laporan Mobil Masuk
     */
    public function mobilData(Request $request)
    {
        if (empty(Auth::user()->username)) {
            return redirect()->to('/login');
        }

        return response()->json($this->orderReportData('AUTOMOTIVE', $request));
    }

    public function exportExcel(Request $request)
    {
        if (empty(Auth::user()->username)) {
            return redirect()->to('/login');
        }

        $query = Warranty::query()

            ->leftJoin(
                'customers',
                'customers.id_customer',
                '=',
                'warranties.id_customer'
            )

            ->leftJoin(
                'products',
                'products.id_product',
                '=',
                'warranties.id_product'
            )

            ->select(
                'warranties.kode_warranty',
                'warranties.tanggal_pasang',
                'warranties.no_polisi',
                'customers.nama_customer',
                'warranties.merk_mobil',
                'warranties.tipe_mobil',
                'warranties.warna_mobil',
                'warranties.tahun_mobil',
                'products.nama_produk',
                'warranties.installer'
            )

            ->where('warranties.status', '!=', 'Void');


        if ($request->filled('tanggal_mulai')) {

            $query->whereDate(
                'warranties.tanggal_pasang',
                '>=',
                $request->tanggal_mulai
            );
        }


        if ($request->filled('tanggal_akhir')) {

            $query->whereDate(
                'warranties.tanggal_pasang',
                '<=',
                $request->tanggal_akhir
            );
        }


        $data = $query
            ->orderBy('warranties.tanggal_pasang', 'asc')
            ->get();


        $filename = 'laporan_mobil_' . date('Ymd_His') . '.csv';


        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];


        $callback = function () use ($data) {

            $file = fopen('php://output', 'w');

            // BOM supaya Excel membaca UTF-8
            fprintf(
                $file,
                chr(0xEF) . chr(0xBB) . chr(0xBF)
            );


            fputcsv($file, [
                'No',
                'Tanggal Pasang',
                'Kode Warranty',
                'No Polisi',
                'Customer',
                'Merk Mobil',
                'Tipe Mobil',
                'Warna',
                'Tahun',
                'Product',
                'Installer'
            ]);


            foreach ($data as $index => $row) {

                fputcsv($file, [

                    $index + 1,

                    $row->tanggal_pasang,

                    $row->kode_warranty,

                    $row->no_polisi,

                    $row->nama_customer,

                    $row->merk_mobil,

                    $row->tipe_mobil,

                    $row->warna_mobil,

                    $row->tahun_mobil,

                    $row->nama_produk,

                    $row->installer

                ]);
            }


            fclose($file);
        };


        return response()->stream(
            $callback,
            200,
            $headers
        );
    }

    public function exportPdf(Request $request)
    {
        if (empty(Auth::user()->username)) {
            return redirect()->to('/login');
        }

        $query = Warranty::query()

            ->leftJoin(
                'customers',
                'customers.id_customer',
                '=',
                'warranties.id_customer'
            )

            ->leftJoin(
                'products',
                'products.id_product',
                '=',
                'warranties.id_product'
            )

            ->select(
                'warranties.kode_warranty',
                'warranties.tanggal_pasang',
                'warranties.no_polisi',
                'customers.nama_customer',
                'warranties.merk_mobil',
                'warranties.tipe_mobil',
                'warranties.warna_mobil',
                'warranties.tahun_mobil',
                'products.nama_produk',
                'warranties.installer'
            )

            ->where('warranties.status', '!=', 'Void');


        if ($request->filled('tanggal_mulai')) {

            $query->whereDate(
                'warranties.tanggal_pasang',
                '>=',
                $request->tanggal_mulai
            );
        }


        if ($request->filled('tanggal_akhir')) {

            $query->whereDate(
                'warranties.tanggal_pasang',
                '<=',
                $request->tanggal_akhir
            );
        }


        $data = $query
            ->orderBy('warranties.tanggal_pasang', 'asc')
            ->get();


        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView(
            'laporan_mobil.mobil_pdf',
            [
                'data' => $data,
                'tanggal_mulai' => $request->tanggal_mulai,
                'tanggal_akhir' => $request->tanggal_akhir
            ]
        );

        $pdf->setPaper('A4', 'landscape');


        return $pdf->download(
            'laporan_mobil_' . date('Ymd_His') . '.pdf'
        );
    }

    public function building()
    {
        if (empty(Auth::user()->username)) return redirect()->to('/login');
        return view('laporan_generic.index', ['title' => 'Laporan Building', 'navbar' => 'Laporan Building', 'reportUrl' => url('/laporan-building/data'), 'exportExcelUrl' => url('/laporan-building/export-excel'), 'exportPdfUrl' => url('/laporan-building/export-pdf'), 'reportType' => 'BUILDING']);
    }

    public function buildingData(Request $request)
    {
        if (empty(Auth::user()->username)) return redirect()->to('/login');
        return response()->json($this->orderReportData('BUILDING', $request));
    }

    public function invoice()
    {
        if (empty(Auth::user()->username)) return redirect()->to('/login');
        return view('laporan_generic.index', ['title' => 'Laporan Invoice', 'navbar' => 'Laporan Invoice', 'reportUrl' => url('/laporan-invoice/data'), 'exportExcelUrl' => url('/laporan-invoice/export-excel'), 'exportPdfUrl' => url('/laporan-invoice/export-pdf'), 'reportType' => 'INVOICE']);
    }

    public function invoiceData(Request $request)
    {
        if (empty(Auth::user()->username)) return redirect()->to('/login');
        return response()->json($this->invoiceReportData($request));
    }

    public function buildingExportExcel(Request $request)
    {
        if (empty(Auth::user()->username)) return redirect()->to('/login');
        return $this->streamReportCsv($this->orderReportData('BUILDING', $request)['data'], 'laporan_building', [
            'No', 'No Order', 'Tanggal', 'Customer', 'Building', 'Produk', 'Teknisi', 'Status'
        ], function ($row) {
            return [$row->order_number, $row->order_date, $row->nama_customer, $row->nama_bangunan, $row->products, $row->installer, $row->status];
        });
    }

    public function invoiceExportExcel(Request $request)
    {
        if (empty(Auth::user()->username)) return redirect()->to('/login');
        return $this->streamReportCsv($this->invoiceReportData($request)['data'], 'laporan_invoice', [
            'No', 'No Invoice', 'Tanggal', 'No Order', 'Customer', 'Type', 'Grand Total', 'Paid', 'Outstanding', 'Status'
        ], function ($row) {
            return [$row->invoice_number, $row->invoice_date, $row->order_number, $row->nama_customer, $row->order_type, $row->grand_total, $row->paid_amount, $row->outstanding_amount, $row->status];
        });
    }

    public function buildingExportPdf(Request $request)
    {
        if (empty(Auth::user()->username)) return redirect()->to('/login');
        $report = $this->orderReportData('BUILDING', $request);
        return $this->downloadGenericPdf('Laporan Building', 'BUILDING', $report, $request, 'laporan_building');
    }

    public function invoiceExportPdf(Request $request)
    {
        if (empty(Auth::user()->username)) return redirect()->to('/login');
        return $this->downloadGenericPdf('Laporan Invoice', 'INVOICE', $this->invoiceReportData($request), $request, 'laporan_invoice');
    }

    private function invoiceReportData(Request $request): array
    {
        $query = DB::table('invoices')->join('orders', 'orders.id_order', '=', 'invoices.id_order')->join('customers', 'customers.id_customer', '=', 'invoices.id_customer')
            ->select('invoices.invoice_number', 'invoices.invoice_date', 'orders.order_number', 'orders.order_type', 'customers.nama_customer', 'invoices.grand_total', 'invoices.paid_amount', 'invoices.outstanding_amount', 'invoices.status');
        if ($request->filled('tanggal_mulai')) $query->whereDate('invoices.invoice_date', '>=', $request->tanggal_mulai);
        if ($request->filled('tanggal_akhir')) $query->whereDate('invoices.invoice_date', '<=', $request->tanggal_akhir);
        $data = $query->orderByDesc('invoices.invoice_date')->get();
        return ['total' => $data->count(), 'data' => $data, 'summary' => ['grand_total' => (float) $data->sum('grand_total'), 'paid_amount' => (float) $data->sum('paid_amount'), 'outstanding_amount' => (float) $data->sum('outstanding_amount')]];
    }

    private function streamReportCsv($data, string $prefix, array $headings, callable $rowMapper)
    {
        $filename = $prefix . '_' . date('Ymd_His') . '.csv';
        return response()->stream(function () use ($data, $headings, $rowMapper) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF));
            fputcsv($file, $headings);
            foreach ($data as $index => $row) fputcsv($file, array_merge([$index + 1], $rowMapper($row)));
            fclose($file);
        }, 200, ['Content-Type' => 'text/csv; charset=UTF-8', 'Content-Disposition' => 'attachment; filename="' . $filename . '"']);
    }

    private function downloadGenericPdf(string $title, string $type, array $report, Request $request, string $filename)
    {
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('laporan_generic.pdf', [
            'title' => $title, 'type' => $type, 'data' => $report['data'], 'summary' => $report['summary'] ?? null,
            'tanggal_mulai' => $request->tanggal_mulai, 'tanggal_akhir' => $request->tanggal_akhir,
        ]);
        return $pdf->setPaper('A4', 'landscape')->download($filename . '_' . date('Ymd_His') . '.pdf');
    }

    private function orderReportData(string $type, Request $request): array
    {
        $query = DB::table('orders')->join('customers', 'customers.id_customer', '=', 'orders.id_customer')
            ->leftJoin('vehicles', 'vehicles.id_vehicle', '=', 'orders.id_vehicle')->leftJoin('buildings', 'buildings.id_building', '=', 'orders.id_building')
            ->leftJoin('technicians', 'technicians.id_technician', '=', 'orders.id_technician')->leftJoin('order_details', 'order_details.id_order', '=', 'orders.id_order')
            ->where('orders.order_type', $type)->select('orders.order_number', 'orders.order_date', 'orders.order_date as tanggal_pasang', 'orders.status', 'customers.nama_customer', 'vehicles.no_polisi', 'vehicles.merk', 'vehicles.merk as merk_mobil', 'vehicles.model', 'vehicles.model as tipe_mobil', 'buildings.nama_bangunan', 'buildings.alamat', 'technicians.name as installer', DB::raw("GROUP_CONCAT(DISTINCT order_details.product_name_snapshot ORDER BY order_details.id_order_detail SEPARATOR ', ') as products"), DB::raw("GROUP_CONCAT(DISTINCT order_details.product_name_snapshot ORDER BY order_details.id_order_detail SEPARATOR ', ') as nama_produk"))
            ->groupBy('orders.id_order', 'orders.order_number', 'orders.order_date', 'orders.status', 'customers.nama_customer', 'vehicles.no_polisi', 'vehicles.merk', 'vehicles.model', 'buildings.nama_bangunan', 'buildings.alamat', 'technicians.name');
        if ($request->filled('tanggal_mulai')) $query->whereDate('orders.order_date', '>=', $request->tanggal_mulai);
        if ($request->filled('tanggal_akhir')) $query->whereDate('orders.order_date', '<=', $request->tanggal_akhir);
        $data = $query->orderByDesc('orders.order_date')->get();
        return ['total' => $data->count(), 'data' => $data];
    }
}
