$(function () {
    if (!window.genericReport) return;
    var c = window.genericReport;
    var columns = c.type === 'INVOICE'
        ? [['Invoice','invoice_number'],['Tanggal','invoice_date'],['No Order','order_number'],['Customer','nama_customer'],['Type','order_type'],['Grand Total','grand_total'],['Paid','paid_amount'],['Outstanding','outstanding_amount'],['Status','status']]
        : [['No Order','order_number'],['Tanggal','order_date'],['Customer','nama_customer'],['Asset',c.type === 'BUILDING' ? 'nama_bangunan' : 'no_polisi'],['Produk','products'],['Teknisi','installer'],['Status','status']];
    $('#reportHead').html('<th>No</th>'+columns.map(function(x){ return '<th>'+x[0]+'</th>'; }).join(''));
    function money(value){ return 'Rp '+Number(value || 0).toLocaleString('id-ID',{minimumFractionDigits:2,maximumFractionDigits:2}); }
    function dateText(value){if(!value)return '';var p=String(value).slice(0,10).split('-');return p.length===3?p[2]+'/'+p[1]+'/'+p[0]:value;}
    function load(){ var start=$('#reportStart').val(),end=$('#reportEnd').val();$.getJSON(c.url,{tanggal_mulai:start,tanggal_akhir:end}).done(function(r){ $('#reportTotal').text(r.total); if(c.type==='INVOICE'){var summary=r.summary||{};$('#invoiceGrandTotal').text(money(summary.grand_total));$('#invoicePaidTotal').text(money(summary.paid_amount));$('#invoiceOutstandingTotal').text(money(summary.outstanding_amount));} $('#reportPeriod').text(start&&end?dateText(start)+' s/d '+dateText(end):start?'Mulai '+dateText(start):end?'Sampai '+dateText(end):'Semua Data');$('#reportPrintedAt').text(new Date().toLocaleDateString('id-ID')); $('#reportBody').html(r.data.length ? r.data.map(function(row,index){ return '<tr><td>'+(index+1)+'</td>'+columns.map(function(col){ var value=row[col[1]]; return '<td>'+(['grand_total','paid_amount','outstanding_amount'].indexOf(col[1])>=0 ? money(value) : (value == null || value === '' ? '-' : value))+'</td>'; }).join('')+'</tr>'; }).join('') : '<tr><td colspan="'+(columns.length+1)+'" class="text-center">Tidak ada data.</td></tr>'); $('#reportHead').html('<th>No</th>'+columns.map(function(x){ return '<th>'+x[0]+'</th>'; }).join('')); }).fail(function(){ $('#reportBody').html('<tr><td colspan="'+(columns.length+1)+'" class="text-center text-danger">Gagal memuat laporan.</td></tr>'); }); }
    function blank(){ $('#reportTotal').text('0'); if(c.type==='INVOICE'){$('#invoiceGrandTotal,#invoicePaidTotal,#invoiceOutstandingTotal').text('Rp 0,00');} $('#reportPeriod').text('Semua Data'); $('#reportPrintedAt').text(''); $('#reportBody').html(''); }
    function exportUrl(url){ return url+'?tanggal_mulai='+encodeURIComponent($('#reportStart').val())+'&tanggal_akhir='+encodeURIComponent($('#reportEnd').val()); }
    $('#reportFilter').on('click',load); $('#reportReset').on('click',function(){$('#reportStart,#reportEnd').val('');blank();}); $('#reportPrint').on('click',function(){window.print();}); $('#reportExcel').on('click',function(){window.location.href=exportUrl(c.excelUrl);}); $('#reportPdf').on('click',function(){window.location.href=exportUrl(c.pdfUrl);}); blank();
});

function LoadLaporanMobil() {

    var tanggalMulai = $('#tanggal_mulai').val();
    var tanggalAkhir = $('#tanggal_akhir').val();

    $.ajax({

        url: '/laporan-mobil/data',

        type: 'GET',

        data: {
            tanggal_mulai: tanggalMulai,
            tanggal_akhir: tanggalAkhir
        },

        dataType: 'JSON',

        beforeSend: function () {

            $('#report_mobil_body').html(`
                <tr>
                    <td colspan="7" class="text-center">
                        <i class="fas fa-spinner fa-spin"></i>
                        Memuat data...
                    </td>
                </tr>
            `);

        },

        success: function (response) {

            var html = '';

            if (response.data.length == 0) {

                html = `
                    <tr>
                        <td colspan="7" class="text-center">
                            Tidak ada data mobil pada periode tersebut.
                        </td>
                    </tr>
                `;

            } else {

                $.each(response.data, function (index, item) {

                    html += `
                        <tr>

                            <td>
                                ${index + 1}
                            </td>

                            <td>
                                ${item.order_number ?? '-'}
                            </td>

                            <td>
                                ${item.tanggal_pasang ?? '-'}
                            </td>

                            <td>
                                ${item.no_polisi ?? '-'}
                            </td>

                            <td>
                                ${item.nama_customer ?? '-'}
                            </td>

                            <td>
                                ${(item.merk_mobil ?? '-') + ' ' + (item.tipe_mobil ?? '')}
                            </td>

                            <td>
                                ${item.nama_produk ?? '-'}
                            </td>

                            <td>
                                ${item.installer ?? '-'}
                            </td>

                        </tr>
                    `;

                });

            }

            $('#report_mobil_body').html(html);


            // Total mobil
            $('#total_mobil').text(response.total);


            // Periode
            if (tanggalMulai && tanggalAkhir) {

                $('#periode_laporan').text(
                    formatTanggal(tanggalMulai) +
                    ' s/d ' +
                    formatTanggal(tanggalAkhir)
                );

            } else if (tanggalMulai) {

                $('#periode_laporan').text(
                    'Mulai ' + formatTanggal(tanggalMulai)
                );

            } else if (tanggalAkhir) {

                $('#periode_laporan').text(
                    'Sampai ' + formatTanggal(tanggalAkhir)
                );

            } else {

                $('#periode_laporan').text('Semua Data');

            }


            // Tanggal cetak
            $('#tanggal_cetak').text(
                formatTanggal(new Date())
            );

        },

        error: function (xhr) {

            console.log(xhr.responseText);

            $('#report_mobil_body').html(`
                <tr>
                    <td colspan="7"
                        class="text-center text-danger">

                        <i class="fas fa-exclamation-circle"></i>

                        Gagal mengambil data laporan.

                    </td>
                </tr>
            `);

        }

    });

}

function formatTanggal(tanggal) {

    if (!tanggal) {
        return '-';
    }

    var date = new Date(tanggal);

    return date.toLocaleDateString('id-ID', {
        day: '2-digit',
        month: 'long',
        year: 'numeric'
    });

}

$('#btn_filter').click(function () {

    LoadLaporanMobil();

});

$('#btn_reset').click(function () {

    $('#tanggal_mulai').val('');
    $('#tanggal_akhir').val('');

    LoadLaporanMobil();

});

//jika ingin saat buka laporan tampil langsung
// $(document).ready(function () {

//     LoadLaporanMobil();

// });


$('#btn_export_excel').click(function () {

    var tanggalMulai = $('#tanggal_mulai').val();
    var tanggalAkhir = $('#tanggal_akhir').val();

    var url =
        '/laporan-mobil/export-excel' +
        '?tanggal_mulai=' + encodeURIComponent(tanggalMulai) +
        '&tanggal_akhir=' + encodeURIComponent(tanggalAkhir);

    window.location.href = url;

});

$('#btn_print').click(function () {

    window.print();

});

$('#btn_export_pdf').click(function () {

    var tanggalMulai = $('#tanggal_mulai').val();
    var tanggalAkhir = $('#tanggal_akhir').val();

    var url =
        '/laporan-mobil/export-pdf' +
        '?tanggal_mulai=' + encodeURIComponent(tanggalMulai) +
        '&tanggal_akhir=' + encodeURIComponent(tanggalAkhir);

    window.location.href = url;

});
