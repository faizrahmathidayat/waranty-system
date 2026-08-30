

$(document).ready(function () {

    // This file is included by the shared layout. Do not request dashboard
    // data from non-dashboard pages such as Invoice or Order.
    if (!$('#warranty-chart').length) {
        return;
    }

    ChartWarranty();
    ChartStatusWarranty();
    LatestWarranty();
    ExpiredSoonWarranty();
    ProductTerlaris();

     $('#tahun_chart').on('change', function () {

        ChartWarranty($(this).val());

    });

});

function ChartWarranty(tahun = $('#tahun_chart').val())
{
    $.ajax({

        url: '/dashboard/chart/warranty',

        type: 'GET',

        data: {
            tahun: tahun
        },

        dataType: 'json',

        success: function(response){

            DrawChartWarranty(response);

        }

    });
}



function DrawChartWarranty(response)
{
    var ctx = $('#warranty-chart');

    new Chart(ctx, {

        type: 'line',

        data: {

            labels: response.label,

            datasets: [{

                label: 'Warranty',

                data: response.data,

                borderColor: '#007bff',

                backgroundColor: 'transparent',

                pointBackgroundColor: '#007bff',

                pointBorderColor: '#007bff',

                fill: false,

                borderWidth: 3

            }]

        },

        options: {

            maintainAspectRatio: false,

            responsive: true,

            legend: {

                display: false

            },

            scales: {

                yAxes: [{

                    ticks: {

                        beginAtZero: true,

                        precision: 0

                    }

                }]

            }

        }

    });

    // Total Warranty Tahun Ini
    $('.text-bold.text-lg').text(response.total);

}


// ===========================================
// DONUT CHART STATUS WARRANTY
// ===========================================
// Plugin untuk menampilkan tulisan di tengah Donut
Chart.pluginService.register({

    beforeDraw: function (chart) {

        if (chart.config.type !== 'doughnut') {
            return;
        }

        var width = chart.chart.width;
        var height = chart.chart.height;
        var ctx = chart.chart.ctx;

        ctx.restore();

        var total = chart.config.data.total || 0;

        // Tulisan Total
        ctx.font = "bold 16px Arial";
        ctx.fillStyle = "#666";
        ctx.textAlign = "center";
        ctx.textBaseline = "middle";
        ctx.fillText("Total", width / 2, height / 2 - 15);

        // Angka Total
        ctx.font = "bold 26px Arial";
        ctx.fillStyle = "#000";
        ctx.fillText(total, width / 2, height / 2 + 15);

        ctx.save();

    }

});
var statusChart = null;

function ChartStatusWarranty()
{
    $.ajax({

        url: '/dashboard/chart/status',

        type: 'GET',

        dataType: 'json',

        success: function(response){

            console.log(response);

            DrawStatusChart(response);

        },

        error:function(xhr){

            console.log(xhr.responseText);

        }

    });
}

function DrawStatusChart(response)
{

    // Hapus chart lama
    if(statusChart != null){

        statusChart.destroy();

    }

    var ctx = document.getElementById('status-chart').getContext('2d');

    statusChart = new Chart(ctx, {

        type: 'doughnut',

        data: {
            total: response.total,
            labels: response.label,

            datasets: [{

                data: response.data,

                backgroundColor: [
                    '#28a745',
                    '#dc3545'
                ],

                borderWidth: 0

            }]

        },

        options: {

            responsive: true,

            maintainAspectRatio: false,

            cutoutPercentage: 55,

            legend: {

                display: false

            },

            tooltips: {

                callbacks: {

                    label: function(tooltipItem, data) {

                        return data.labels[tooltipItem.index] + ' : ' + data.datasets[0].data[tooltipItem.index];

                    }

                }

            }

        }

    });

    // =============================
    // Isi Keterangan Sebelah Kanan
    // =============================

    $('#status_active').html(

        '<b>' + response.active + '</b> (' + response.active_percent + '%)'

    );

    $('#status_expired').html(

        '<b>' + response.expired + '</b> (' + response.expired_percent + '%)'

    );

}

function LatestWarranty()
{

    $.ajax({

        url:'/dashboard/chart/latest-warranty',

        type:'GET',

        success:function(response){

            var html='';

            $.each(response,function(i,item){

                var status='';

                if(item.tanggal_expired >= getToday()){

                    status='<span class="badge badge-success">Active</span>';

                }else{

                    status='<span class="badge badge-danger">Expired</span>';

                }

                html +=

                '<tr>'+

                    '<td>'+item.kode_warranty+'</td>'+

                    '<td>'+item.nama_customer+'</td>'+

                    '<td>'+item.nama_produk+'</td>'+

                    '<td>'+FormatTanggal(item.tanggal_pasang)+'</td>'+

                    '<td>'+status+'</td>'+

                '</tr>';

            });

            $('#latest_warranty').html(html);

        }

    });

}

function getToday()
{
    var today = new Date();

    var year = today.getFullYear();

    var month = String(today.getMonth() + 1).padStart(2, '0');

    var day = String(today.getDate()).padStart(2, '0');

    return year + '-' + month + '-' + day;
}

function FormatTanggal(tanggal)
{
    var d = new Date(tanggal);

    var hari = String(d.getDate()).padStart(2, '0');
    var bulan = String(d.getMonth() + 1).padStart(2, '0');
    var tahun = d.getFullYear();

    return hari + '/' + bulan + '/' + tahun;
}

function ExpiredSoonWarranty()
{

    $.ajax({

        url:'/dashboard/warranty-expired-soon',

        type:'GET',

        dataType:'json',

        success:function(response){

            var html='';

            $.each(response,function(i,item){

                html+=`

                <div class="media p-2 border-bottom">

                    <div class="mr-3 text-center">

                        <i class="fas fa-calendar-alt text-danger fa-2x"></i>

                        <br>

                        <small class="text-danger">

                            ${item.sisa_hari} Hari Lagi

                        </small>

                    </div>

                    <div class="media-body">

                        <strong>${item.kode_warranty}</strong>

                        <br>

                        <small>${item.nama_customer}</small>

                        <br>

                        <small>${item.nama_produk}</small>

                    </div>

                    <div class="text-right">

                        <span class="text-danger">

                            ${FormatTanggal(item.tanggal_expired)}

                        </span>

                    </div>

                </div>

                `;

            });

            $('#expired_soon_list').html(html);

        }

    });

}




function ProductTerlaris() {

    $.ajax({

        url: '/dashboard/product-terlaris',

        type: 'GET',

        dataType: 'JSON',

        success: function (data) {

            var html = '';

            if (data.length == 0) {

                html = `
                    <div class="text-center text-muted p-4">
                        Belum ada data product
                    </div>
                `;

            } else {

                // Cari nilai tertinggi
                var maxTotal = Math.max.apply(
                    null,
                    data.map(function (item) {
                        return parseInt(item.total);
                    })
                );

                $.each(data, function (index, item) {

                    var total = parseInt(item.total);

                    // Hitung persentase bar
                    var percentage = (total / maxTotal) * 100;

                    html += `

                        <div class="product-ranking">

                            <div class="product-ranking-header">

                                <div class="product-name">

                                    <span class="product-number">
                                        ${index + 1}
                                    </span>

                                    <strong>
                                        ${item.nama_produk}
                                    </strong>

                                </div>

                                <span class="badge badge-primary">
                                    ${total} Warranty
                                </span>

                            </div>


                            <div class="progress">

                                <div
                                    class="progress-bar bg-primary"
                                    role="progressbar"
                                    style="width: ${percentage}%">

                                </div>

                            </div>

                        </div>

                    `;

                });

            }

            $('#product_terlaris').html(html);

        },

        error: function (xhr) {

            console.log(
                'Error Product Terlaris:',
                xhr
            );

            $('#product_terlaris').html(`

                <div class="text-center text-danger p-4">

                    <i class="fas fa-exclamation-circle"></i>

                    Gagal mengambil data product

                </div>

            `);

        }

    });

}
