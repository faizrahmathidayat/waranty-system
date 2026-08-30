// ==========================================
// DATATABLE WARRANTY
// ==========================================

$(function () {

    $('#tabel_warranty').DataTable({

        responsive: true,
        autoWidth: false,
        deferRender: true,
        searchDelay: 350,
        processing: true,
        serverSide: true,
        order: [],

        ajax: {
            url: "warranty/json",
            type: "GET",

            data: function (d) {

            d.product = $('#filter_product').val();
            d.status = $('#filter_status').val();

    }
        },

        columns: [

            {
                data: null,
                render: function (data, type, row, meta) {
                    return meta.row + meta.settings._iDisplayStart + 1;
                }
            },

            {
                data: 'kode_warranty',
                name: 'kode_warranty'
            },

            {
                data: 'nama_customer',
                name: 'customers.nama_customer'
            },

            {
                data: 'nama_produk',
                name: 'products.nama_produk'
            },

            {
                data: 'no_polisi',
                name: 'no_polisi'
            },

             {
                data: 'no_invoice',
                name: 'no_invoice'
            },

            {
                data: 'tanggal_pasang',
                name: 'tanggal_pasang'
            },

            {
                data: 'tanggal_expired',
                name: 'tanggal_expired'
            },

            {
                data: 'status_view',
                render: function (data) {

                    switch (data) {

                        case 'Active':
                            return '<span class="badge badge-success">ACTIVE</span>';

                        case 'Expired':
                            return '<span class="badge badge-danger">EXPIRED</span>';

                        case 'Void':
                            return '<span class="badge badge-secondary">VOID</span>';

                        default:
                            return data;
                    }

                }
            },

            {
                data: 'id_warranty',
                name: 'id_warranty',

                render: function (data) {

                    return '<center>\
                        <a data-toggle="modal" \
                           data-placement="top" \
                           value="' + data + '" \
                           class="btn-detail-warranty" \
                           title="Detail" \
                           data-target="#modal_detail_warranty">\
                        <i class="fa fa-edit" style="font-size:22px;cursor:pointer;"></i>\
                        </a>\
                        </center>';

                }

            }

        ]

    });

});



// ==========================================
// FOCUS MODAL
// ==========================================

$('#modal_tambah_warranty').on('shown.bs.modal', function () {

    $('#form-warranty')[0].reset();

    $('#no_invoice').removeClass('is-invalid');
    $('#id_customer').removeClass('is-invalid').removeClass('border-danger');
    $('#id_product').removeClass('is-invalid').removeClass('border-danger');
    $('#id_customer').val('').trigger('change');
    $('#id_product').val('').trigger('change');
    $('#no_polisi').removeClass('is-invalid');
    $('#merk_mobil').removeClass('is-invalid');
    $('#tipe_mobil').removeClass('is-invalid');
    $('#warna_mobil').removeClass('is-invalid');
    $('#tahun_mobil').removeClass('is-invalid');
    $('#tanggal_pasang').removeClass('is-invalid');
    $('#installer').removeClass('is-invalid');
    $('#catatan').removeClass('is-invalid');

    $('#id_customer_notif').html('');
    $('#id_product_notif').html('');
    $('#no_invoice_notif').html('');
    $('#no_polisi_notif').html('');
    $('#merk_mobil_notif').html('');
    $('#tipe_mobil_notif').html('');
    $('#warna_mobil_notif').html('');
    $('#tahun_mobil_notif').html('');
    $('#tanggal_pasang_notif').html('');
    $('#installer_notif').html('');

    $('#no_invoice').trigger('focus');

});



// ==========================================
// RESET MODAL
// ==========================================

$('body').on('click', '.btn-danger', function () {

    $('#no_invoice').trigger('focus');

});



// ==========================================
// CLOSE MODAL
// ==========================================

$(document).ready(function () {

    $("#modal_tambah_warranty").on("hidden.bs.modal", function () {

        $('#form-warranty')[0].reset();

        $('#no_invoice').removeClass('is-invalid');
        $('#id_customer').removeClass('is-invalid');
        $('#id_product').removeClass('is-invalid');
        $('#no_polisi').removeClass('is-invalid');

    });

});



// ==========================================
// SIMPAN WARRANTY
// ==========================================

function SimpanWarranty() {

    var customer = $('#id_customer').val();
    var product = $('#id_product').val();
    var invoice = $('#no_invoice').val();
    var nopol = $('#no_polisi').val();
    var mrk_mobil = $('#merk_mobil').val();
    var tipe_mobil = $('#tipe_mobil').val();
    var warna_mobil = $('#warna_mobil').val();
    var tahun_mobil = $('#tahun_mobil').val();
    var tanggal_pasang = $('#tanggal_pasang').val();
    var tanggal_expired = $('#tanggal_expired').val();
    var installer = $('#installer').val();
    var catatan = $('#catatan').val();

     $("#id_customer").change(function(e) {
        if ($(this).val().length > 0) {
          $('#id_customer').removeClass('is-invalid');
        }
      });

      $("#id_product").change(function(e) {
        if ($(this).val().length > 0) {
          $('#id_product').removeClass('is-invalid');
        }
      });

       $("#no_invoice").on('keyup',function() {
      if ($(this).val().length > 0) {
        $('#no_invoice').removeClass('is-invalid');
        }
      });
       $("#no_polisi").on('keyup',function() {
      if ($(this).val().length > 0) {
        $('#no_polisi').removeClass('is-invalid');
      }
    });

     $("#merk_mobil").on('keyup',function() {
      if ($(this).val().length > 0) {
        $('#merk_mobil').removeClass('is-invalid');
      }
    });

     $("#tipe_mobil").on('keyup',function() {
      if ($(this).val().length > 0) {
        $('#tipe_mobil').removeClass('is-invalid');
      }
    });

     $("#warna_mobil").on('keyup',function() {
      if ($(this).val().length > 0) {
        $('#warna_mobil').removeClass('is-invalid');
      }
    });

     $("#tahun_mobil").on('keyup',function() {
      if ($(this).val().length > 0) {
        $('#tahun_mobil').removeClass('is-invalid');
      }
    });

     $("#tanggal_pasang").change(function(e) {
        if ($(this).val().length > 0) {
          $('#tanggal_pasang').removeClass('is-invalid');
        }
      });

    $("#installer").on('keyup',function() {
      if ($(this).val().length > 0) {
        $('#installer').removeClass('is-invalid');
      }
    });

    $("#catatan").on('keyup',function() {
      if ($(this).val().length > 0) {
        $('#catatan').removeClass('is-invalid');
      }
    });

     if (customer == "") {
        $('#id_customer').addClass('is-invalid');
        $('#id_customer').addClass('border-danger');
        $('#id_customer').focus();
        $('#id_customer_notif').html("Nama Customer tidak boleh kosong");
        return false;
    }

    else if (product == "") {
        $('#id_product').addClass('is-invalid');
        $('#id_product').addClass('border-danger');
        $('#id_product').focus();
        $('#id_product_notif').html("Nama Product tidak boleh kosong");
        return false;
    }

    else if (invoice == "") {
        $('#no_invoice').addClass('is-invalid');
        $('#no_invoice').focus();
        $('#no_invoice_notif').html("No Invoice tidak boleh kosong");
        return false;
    }

    else if (nopol == "") {
        $('#no_polisi').addClass('is-invalid');
        $('#no_polisi').focus();
        $('#no_polisi_notif').html("No Polisi tidak boleh kosong");
        return false;
    }

    else if (mrk_mobil == "") {
        $('#merk_mobil').addClass('is-invalid');
        $('#merk_mobil').focus();
        $('#merk_mobil_notif').html("Merk Mobil tidak boleh kosong");
        return false;
    }  
    
    else if (tipe_mobil == "") {
        $('#tipe_mobil').addClass('is-invalid');
        $('#tipe_mobil').focus();
        $('#tipe_mobil_notif').html("Tipe Mobil tidak boleh kosong");
        return false;
    }

    else if (warna_mobil == "") {
        $('#warna_mobil').addClass('is-invalid');
        $('#warna_mobil').focus();
        $('#warna_mobil_notif').html("Warna Mobil tidak boleh kosong");
        return false;
    }

    else if (tahun_mobil == "") {
        $('#tahun_mobil').addClass('is-invalid');
        $('#tahun_mobil').focus();
        $('#tahun_mobil_notif').html("Tahun Mobil tidak boleh kosong");
        return false;
    }

    else if (tanggal_pasang == "") {
        $('#tanggal_pasang').addClass('is-invalid');
        $('#tanggal_pasang').focus();
        $('#tanggal_pasang_notif').html("Tanggal Pasang tidak boleh kosong");
        return false;
    }

     else if (installer == "") {
        $('#installer').addClass('is-invalid');
        $('#installer').focus();
        $('#installer_notif').html("Installer tidak boleh kosong");
        return false;
    }


    var formInput = $('.form-warranty').serialize();

    var btn = $('#btn_save_warranty');

    btn.prop('disabled', true);

    btn.html(`
        <span class="spinner-border spinner-border-sm mr-1"></span>
        Menyimpan...
    `);

    $.ajax({

        type: "POST",

        url: "warranty/store",

        data: formInput,

        dataType: "json",

        success: function (response) {

            if (!response.success) {

                if (response.message == "must_unique") {

                    Swal.fire({

                        icon: 'warning',
                        title: 'Nomor Invoice sudah digunakan',
                        timer: 2500,
                        showConfirmButton: false

                    });

                }

                return;
            }

            $('#modal_tambah_warranty').modal('hide');

            $('#id_customer').val('').trigger('change');
            $('#id_product').val('').trigger('change');

            $('#tabel_warranty').DataTable().ajax.reload(null, false);

            $('#form-warranty')[0].reset();

            Swal.fire({

                title: 'Warranty Berhasil Dibuat',

                html: `
                    <div class="text-center">

                        <img src="${response.qr_code}"
                             width="220"
                             class="img-fluid mb-3">

                        <h5>${response.kode_warranty}</h5>

                        <a href="${response.url}"
                           target="_blank"
                           class="btn btn-primary mt-2">
                           Lihat Digital Warranty
                        </a>

                    </div>
                `,

                width: 500,

                confirmButtonText: 'Tutup'

            });

        },

        error: function (xhr) {

            console.log(xhr.responseText);

            Swal.fire({

                icon: 'error',

                title: 'Oops...',

                text: 'Terjadi kesalahan saat menyimpan Warranty.'

            });

        },

         complete: function () {

            // =========================
            // KEMBALIKAN BUTTON
            // =========================

            btn.prop('disabled', false);

            btn.html(`
                <i class="fas fa-save"></i>
                Simpan
            `);

        }

    });

}

// ==========================================
// DETAIL WARRANTY
// ==========================================

$('body').on('click', '.btn-detail-warranty', function () {

    const idWarranty = $(this).attr('value');

    $.ajax({

        url: "warranty/show/" + idWarranty,
        type: "GET",
        dataType: "JSON",

        success: function (data) {

            // ========================================
// CEK STATUS WARRANTY
// ========================================

var statusWarranty = '';

if (data.status === 'Void') {

    statusWarranty = 'Void';

} else {

    var today = new Date();
    today.setHours(0, 0, 0, 0);

    var expired = new Date(data.tanggal_expired);
    expired.setHours(0, 0, 0, 0);

    if (expired < today) {

        statusWarranty = 'Expired';

    } else {

        statusWarranty = 'Active';

    }

}


// ========================================
// TAMPILKAN STATUS
// ========================================

if (statusWarranty === 'Active') {

    $('#status_warranty_detail')
        .text('Active')
        .css('color', '#007bff');

} else if (statusWarranty === 'Expired') {

    $('#status_warranty_detail')
        .text('Expired')
        .css('color', '#dc3545');

} else if (statusWarranty === 'Void') {

    $('#status_warranty_detail')
        .text('Void')
        .css('color', '#6c757d');

}

            $('#id_warranty').val(data.id_warranty);
            $('#id_warranty_void').val(data.id_warranty);

            $('#kode_warranty_detail').val(data.kode_warranty);
            // Simpan kode warranty ke tombol QR Code
            $('#btn_show_qrcode').attr('data-kode', data.kode_warranty);

            $('#qr_code_detail').attr('src','/qrcode/' + data.kode_warranty + '.svg?' + new Date().getTime()
);

            // $('#id_customer_detail').val(data.id_customer);
            // $('#id_product_detail').val(data.id_product);
            $('#id_customer_detail').val(data.id_customer).trigger('change');
            $('#id_product_detail').val(data.id_product).trigger('change');
            // $('#create_by_detail').val(data.name);
            $('#create_by_detail').html(data.name);

            $('#no_invoice_detail').val(data.no_invoice);
            $('#no_polisi_detail').val(data.no_polisi);

            $('#merk_mobil_detail').val(data.merk_mobil);
            $('#tipe_mobil_detail').val(data.tipe_mobil);
            $('#warna_mobil_detail').val(data.warna_mobil);
            $('#tahun_mobil_detail').val(data.tahun_mobil);

            $('#tanggal_pasang_detail').val(data.tanggal_pasang);
            $('#tanggal_expired_detail').val(data.tanggal_expired);

            $('#installer_detail').val(data.installer);
            $('#catatan_detail').val(data.catatan);

            console.log(data.status);

            switch (data.status) {

                case 'Active':

                    $('#status_badge')
                        .removeClass()
                        .addClass('badge badge-success')
                        .html('<i class="fas fa-check-circle"></i> ACTIVE');

                    break;

                case 'Claim':

                    $('#status_badge')
                        .removeClass()
                        .addClass('badge badge-warning')
                        .html('<i class="fas fa-tools"></i> CLAIM');

                    break;

                case 'Expired':

                    $('#status_badge')
                        .removeClass()
                        .addClass('badge badge-danger')
                        .html('<i class="fas fa-times-circle"></i> EXPIRED');

                    break;

                case 'Void':

                    $('#status_badge')
                        .removeClass()
                        .addClass('badge badge-secondary')
                        .html('<i class="fas fa-ban"></i> VOID');

                    break;

                default:

                    $('#status_badge')
                        .removeClass()
                        .addClass('badge badge-dark')
                        .html('<i class="fas fa-question-circle"></i> UNKNOWN');

            }

            $('#status').val(data.status);

            $('#modal_detail_warranty').modal('show');


            // readonly
            $('#id_customer_detail').prop('disabled', true);
            $('#id_product_detail').prop('disabled', true);

            $('#no_invoice_detail').prop('readonly', true);
            $('#no_polisi_detail').prop('readonly', true);

            $('#merk_mobil_detail').prop('readonly', true);
            $('#tipe_mobil_detail').prop('readonly', true);

            $('#warna_mobil_detail').prop('readonly', true);
            $('#tahun_mobil_detail').prop('readonly', true);

            $('#tanggal_pasang_detail').prop('readonly', true);
            $('#tanggal_expired_detail').prop('readonly', true);

            $('#installer_detail').prop('readonly', true);
            $('#catatan_detail').prop('readonly', true);

            $('#status').prop('disabled', true);


            // $('#btn_update_warranty').hide();
            // $('#btn_edit_warranty').show();

            if (data.status == 'Void') {

                $('#btn_update_warranty').hide();
                $('#btn_edit_warranty').hide();
                $('#btn_digital_warranty').hide();
                $('#btn_void_warranty').hide();

            } else {

                $('#btn_update_warranty').hide();
                $('#btn_edit_warranty').show();
                $('#btn_digital_warranty').attr('href','/warranty/' + data.kode_warranty).show();
                $('#btn_void_warranty').show();

            }

            

        }

    });

});



// ==========================================
// EDIT WARRANTY
// ==========================================

$('body').on('click', '#btn_edit_warranty', function () {

    $('#btn_update_warranty').show();
    $('#btn_edit_warranty').hide();

    $('#id_customer_detail').prop('disabled', false);
    $('#id_product_detail').prop('disabled', false);

    $('#no_invoice_detail').prop('readonly', false);
    $('#no_polisi_detail').prop('readonly', false);

    $('#merk_mobil_detail').prop('readonly', false);
    $('#tipe_mobil_detail').prop('readonly', false);

    $('#warna_mobil_detail').prop('readonly', false);
    $('#tahun_mobil_detail').prop('readonly', false);

    $('#tanggal_pasang_detail').prop('readonly', false);

    // tetap readonly karena dihitung otomatis nanti
    $('#tanggal_expired_detail').prop('readonly', true);

    $('#installer_detail').prop('readonly', false);
    $('#catatan_detail').prop('readonly', false);

    $('#status').prop('disabled', false);

});



// ==========================================
// CLOSE DETAIL
// ==========================================

$('#modal_detail_warranty').on('hidden.bs.modal', function () {

    $('#btn_update_warranty').hide();
    $('#btn_edit_warranty').show();

    $('#id_customer_detail').prop('disabled', true);
    $('#id_product_detail').prop('disabled', true);

    $('#no_invoice_detail').prop('readonly', true);
    $('#no_polisi_detail').prop('readonly', true);

    $('#merk_mobil_detail').prop('readonly', true);
    $('#tipe_mobil_detail').prop('readonly', true);

    $('#warna_mobil_detail').prop('readonly', true);
    $('#tahun_mobil_detail').prop('readonly', true);

    $('#tanggal_pasang_detail').prop('readonly', true);
    $('#tanggal_expired_detail').prop('readonly', true);

    $('#installer_detail').prop('readonly', true);
    $('#catatan_detail').prop('readonly', true);

    $('#status').prop('disabled', true);

});

//===========================================
// UPDATE WARRANTY
//===========================================

function UpdateWarranty() {

    var valid = true;

    if ($("#id_customer_detail").val() == "") {
        $("#id_customer_detail").addClass("is-invalid");
        $("#id_customer_detail_notif").html("Customer wajib dipilih");
        valid = false;
    } else {
        $("#id_customer_detail").removeClass("is-invalid");
    }

    if ($("#id_product_detail").val() == "") {
        $("#id_product_detail").addClass("is-invalid");
        $("#id_product_detail_notif").html("Product wajib dipilih");
        valid = false;
    } else {
        $("#id_product_detail").removeClass("is-invalid");
    }

    if ($("#no_invoice_detail").val() == "") {
        $("#no_invoice_detail").addClass("is-invalid");
        $("#no_invoice_detail_notif").html("No Invoice wajib diisi");
        valid = false;
    } else {
        $("#no_invoice_detail").removeClass("is-invalid");
    }

    if ($("#no_polisi_detail").val() == "") {
        $("#no_polisi_detail").addClass("is-invalid");
        $("#no_polisi_detail_notif").html("No Polisi wajib diisi");
        valid = false;
    } else {
        $("#no_polisi_detail").removeClass("is-invalid");
    }

    if (valid == false) {
        return false;
    }

    $.ajax({

        url: "warranty/update",
        type: "POST",
        data: $(".form-edit-warranty").serialize(),

        beforeSend: function () {

            $("#update_warranty").html("Loading...");
            $("#update_warranty").attr("disabled", true);

        },

        success: function (response) {

            if (response == "must_unique") {

                Swal.fire({

                    icon: 'warning',
                    title: 'Warning',
                    text: 'Nomor Invoice sudah digunakan.'

                });

                $("#update_warranty").html("Update");
                $("#update_warranty").attr("disabled", false);

                return false;

            }

            Swal.fire({

                icon: 'success',
                title: 'Success',
                text: 'Warranty berhasil diupdate.'

            });

            $("#modal_detail_warranty").modal("hide");

            $('#tabel_warranty').DataTable().ajax.reload(null, false);

            $("#update_warranty").html("Update");
            $("#update_warranty").attr("disabled", false);

            $("#edit_warranty").show();
            $("#update_warranty").hide();

        },

        error: function () {

            Swal.fire({

                icon: 'error',
                title: 'Error',
                text: 'Terjadi kesalahan.'

            });

            $("#update_warranty").html("Update");
            $("#update_warranty").attr("disabled", false);

        }

    });

}

//===========================================
// HAPUS WARRANTY
//===========================================

function HapusWarranty() {

    Swal.fire({

        title: 'Apakah anda yakin?',
        text: "Data Warranty akan dihapus!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Ya, Hapus!',
        cancelButtonText: 'Batal'

    }).then((result) => {

        if (result.isConfirmed) {

            $.ajax({

                url: "warranty/destroy",
                type: "POST",
                data: $(".form-delete-warranty").serialize(),

                beforeSend: function () {

                    $(".btn-hapus-warranty")
                        .html("Loading...")
                        .attr("disabled", true);

                },

                success: function (response) {

                    if (response == "USED") {

                        Swal.fire({

                            icon: "warning",
                            title: "Warning",
                            text: "Data Warranty tidak dapat dihapus."

                        });

                        $(".btn-hapus-warranty")
                            .html("Hapus")
                            .attr("disabled", false);

                        return false;

                    }

                    Swal.fire({

                        icon: "success",
                        title: "Success",
                        text: "Data Warranty berhasil dihapus."

                    });

                    $("#modal_detail_warranty").modal("hide");

                    $('#tabel_warranty')
                        .DataTable()
                        .ajax
                        .reload(null, false);

                    $(".btn-hapus-warranty")
                        .html("Hapus")
                        .attr("disabled", false);

                },

                error: function () {

                    Swal.fire({

                        icon: "error",
                        title: "Error",
                        text: "Terjadi kesalahan."

                    });

                    $(".btn-hapus-warranty")
                        .html("Hapus")
                        .attr("disabled", false);

                }

            });

        }

    });

}

//hitung expired
function hitungExpired() {

    let tanggalPasang = $('#tanggal_pasang').val();

    let bulanGaransi = $('#id_product option:selected').data('garansi');

    if (!tanggalPasang || !bulanGaransi) {
        $('#tanggal_expired').val('');
        return;
    }

    let tgl = new Date(tanggalPasang);

    tgl.setMonth(tgl.getMonth() + parseInt(bulanGaransi));

    let tahun = tgl.getFullYear();
    let bulan = String(tgl.getMonth() + 1).padStart(2, '0');
    let hari = String(tgl.getDate()).padStart(2, '0');

    $('#tanggal_expired').val(`${tahun}-${bulan}-${hari}`);
}

$('#id_product').on('change', function () {
    hitungExpired();
});

$('#tanggal_pasang').on('change', function () {
    hitungExpired();
});

function VoidWarranty() {

    Swal.fire({

        title: 'Void Warranty',
        text: 'Warranty yang sudah di-void tidak dapat dikembalikan. Lanjutkan?',
        icon: 'warning',

        showCancelButton: true,
        confirmButtonText: 'Ya',
        cancelButtonText: 'Batal'

    }).then((result) => {

        if (result.isConfirmed) {

            var formData = $('.form-void-warranty').serialize();

            $.ajax({

                type: "POST",

                url: "/warranty/void",

                data: formData,

                success: function (data) {

                    if (data == "SUCCESS") {

                        Swal.fire({

                            icon: 'success',
                            title: 'Berhasil',
                            text: 'Warranty berhasil di-Void.',
                            timer: 2000,
                            showConfirmButton: false

                        });

                        $('#tabel_warranty').DataTable().ajax.reload(null, false);
                        $('#modal_detail_warranty').modal('hide');

                    } else if (data == "ALREADY_VOID") {

                        Swal.fire({

                            icon: 'warning',
                            title: 'Peringatan',
                            text: 'Warranty sudah berstatus Void.'

                        });

                    } else {

                        Swal.fire({

                            icon: 'error',
                            title: 'Gagal',
                            text: 'Warranty tidak ditemukan.'

                        });

                    }

                }

            });

        }

    });

}

function ShowQRCode() {

    var kode = $('#btn_show_qrcode').attr('data-kode');

    Swal.fire({

        title: 'QR Code Warranty',

        html: `
            <img
                src="/qrcode/${kode}.svg?${new Date().getTime()}"
                class="img-fluid"
                style="max-width:250px;">
            <br><br>
            <strong>${kode}</strong>
        `,

        confirmButtonText: 'Tutup',
        width: 420

    });

}

// Multi-type warranty form (Bootstrap 4 / jQuery). Kept separate from legacy detail handlers.
(function () {
    function productOptions() { return $('#product-options-template').html() || ''; }
    function itemHtml(index) {
        var type = $('#warranty_type').val();
        var field = type === 'CAR' ? '<input class="form-control" name="items[' + index + '][posisi_kaca]" placeholder="Posisi Kaca" required>' : '<input class="form-control" name="items[' + index + '][area_pekerjaan]" placeholder="Area Pekerjaan" required>';
        var dimensions = type === 'BUILDING' ? '<div class="col-md-2"><input class="form-control building-size" type="number" min="0.01" step="0.01" name="items[' + index + '][panjang]" placeholder="Panjang (m)" required></div><div class="col-md-2"><input class="form-control building-size" type="number" min="0.01" step="0.01" name="items[' + index + '][lebar]" placeholder="Lebar (m)" required></div><div class="col-md-1"><input class="form-control building-size" type="number" min="1" name="items[' + index + '][jumlah]" placeholder="Jml" required></div><div class="col-md-2"><input class="form-control luas-per-item" readonly placeholder="Luas/item m²"><input type="hidden" class="total-luas"></div><div class="col-md-2"><input class="form-control total-luas-view" readonly placeholder="Total m²"></div>' : '';
        return '<div class="card item-row mb-2"><div class="card-body py-2"><div class="row align-items-center"><div class="col-md-2">' + field + '</div><div class="col-md-3"><select class="form-control item-product" name="items[' + index + '][id_product]" required>' + productOptions() + '</select></div>' + dimensions + '<div class="col-md-2"><input class="form-control item-expired" readonly placeholder="Tanggal Expired"></div><div class="col-md-1"><select class="form-control" name="items[' + index + '][status]"><option value="Active">Active</option><option value="Claim">Claim</option></select></div><div class="col-md-1"><button type="button" class="btn btn-outline-danger remove-item"><i class="fa fa-times"></i></button></div></div></div></div>';
    }
    function addItem() { var i = $('#warranty-items .item-row').length; $('#warranty-items').append(itemHtml(i)); }
    function expiry(row) { var date = $('#tanggal_pasang').val(), months = row.find('.item-product option:selected').data('garansi'); if (!date || months === undefined) return row.find('.item-expired').val(''); var d = new Date(date + 'T00:00:00'); d.setMonth(d.getMonth() + parseInt(months, 10)); row.find('.item-expired').val(d.toISOString().slice(0, 10)); }
    function area(row) { var p = parseFloat(row.find('[name$="[panjang]"]').val()) || 0, l = parseFloat(row.find('[name$="[lebar]"]').val()) || 0, q = parseInt(row.find('[name$="[jumlah]"]').val(), 10) || 0; row.find('.luas-per-item').val((p * l).toFixed(2)); row.find('.total-luas-view').val((p * l * q).toFixed(2)); }
    $('#warranty_type').on('change', function () { var type = this.value; $('.type-fields').addClass('d-none'); if (type === 'BUILDING') $('#building-fields').removeClass('d-none'); if (type === 'CAR' || type === 'PPF') $('#vehicle-fields').removeClass('d-none'); $('#warranty-items').empty(); if (type) addItem(); });
    $('#modal_tambah_warranty').on('shown.bs.modal', function () { $('#warranty_type').val(''); $('.type-fields').addClass('d-none'); $('#warranty-items').empty(); });
    $('#add-warranty-item').on('click', function () { if ($('#warranty_type').val()) addItem(); });
    $(document).on('click', '.remove-item', function () { $(this).closest('.item-row').remove(); });
    $(document).on('change', '.item-product, #tanggal_pasang', function () { $('#warranty-items .item-row').each(function () { expiry($(this)); }); });
    $(document).on('input', '.building-size', function () { area($(this).closest('.item-row')); });
})();

function SimpanWarranty() {
    var form = $('#form-warranty');
    if (!form[0].checkValidity()) { form[0].reportValidity(); return; }
    if (!$('#warranty-items .item-row').length) { Swal.fire('Peringatan', 'Minimal satu item warranty wajib diisi.', 'warning'); return; }
    $('#btn_save_warranty').prop('disabled', true).text('Menyimpan...');
    $.ajax({ url: '/warranty/store', type: 'POST', data: form.serialize() })
        .done(function (response) { Swal.fire('Berhasil', 'Warranty ' + response.kode_warranty + ' berhasil dibuat.', 'success'); $('#modal_tambah_warranty').modal('hide'); $('#tabel_warranty').DataTable().ajax.reload(null, false); })
        .fail(function (xhr) { var message = xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : 'Data warranty gagal disimpan.'; Swal.fire('Gagal', message, 'error'); })
        .always(function () { $('#btn_save_warranty').prop('disabled', false).html('<i class="fa fa-save"></i> Simpan'); });
}


$('#filter_product, #filter_status').on('change', function () {

    $('#tabel_warranty').DataTable().ajax.reload();

});

// Multi-type detail/edit uses the existing modal and endpoint.  It replaces the
// legacy single-product handlers above while retaining QR, Digital Warranty and Void.
(function () {
    var detailData = null;
    var editing = false;
    function esc(value) { return $('<div>').text(value == null ? '' : value).html(); }
    function displayDate(value) { if (!value) return '-'; var date = String(value).slice(0, 10).split('-'); return date.length === 3 ? date[2] + '/' + date[1] + '/' + date[0] : esc(value); }
    function productOptions(selected) {
        return ($('#product-options-template option').map(function () {
            return '<option value="' + this.value + '" data-garansi="' + ($(this).data('garansi') || '') + '"' + (String(this.value) === String(selected) ? ' selected' : '') + '>' + esc($(this).text()) + '</option>';
        }).get().join(''));
    }
    function statusBadge(status) {
        var cls = { Active: 'success', Claim: 'warning', Expired: 'danger', Void: 'secondary' }[status] || 'dark';
        return '<span class="badge badge-' + cls + '">' + esc(String(status).toUpperCase()) + '</span>';
    }
    function detailExpiry(row) {
        var date = $('#tanggal_pasang_detail').val(), months = row.find('.detail-product option:selected').data('garansi');
        if (!date || months === undefined || months === '') return '';
        var d = new Date(date + 'T00:00:00'); d.setMonth(d.getMonth() + parseInt(months, 10)); return d.toISOString().slice(0, 10);
    }
    function calculateDetailArea(row) {
        var p = parseFloat(row.find('[name$="[panjang]"]').val()) || 0, l = parseFloat(row.find('[name$="[lebar]"]').val()) || 0, q = parseInt(row.find('[name$="[jumlah]"]').val(), 10) || 0;
        row.find('.detail-luas').text((p * l * q).toFixed(2) + ' m²');
    }
    function itemRow(item, index) {
        var type = detailData.warranty_type, field = type === 'CAR' ? 'posisi_kaca' : 'area_pekerjaan', label = type === 'CAR' ? 'Posisi Kaca' : 'Area PPF';
        var prefix = 'items[' + index + ']';
        var row = '<tr class="detail-item-row"><td><span class="detail-read">' + esc(item[field]) + '</span><input class="form-control form-control-sm detail-editable" name="' + prefix + '[' + field + ']" value="' + esc(item[field]) + '" placeholder="' + label + '"></td>';
        row += '<td><span class="detail-read">' + esc(item.product ? item.product.nama_produk : (item.product_name_snapshot || '')) + '</span><select class="form-control form-control-sm select2 detail-product detail-editable" name="' + prefix + '[id_product]">' + productOptions(item.id_product) + '</select></td>';
        if (type === 'BUILDING') row += '<td><span class="detail-read">' + esc(item.panjang) + ' × ' + esc(item.lebar) + ' m</span><div class="detail-editable"><input class="form-control form-control-sm mb-1 detail-size" type="number" min="0.01" step="0.01" name="' + prefix + '[panjang]" value="' + esc(item.panjang) + '" placeholder="Panjang"><input class="form-control form-control-sm detail-size" type="number" min="0.01" step="0.01" name="' + prefix + '[lebar]" value="' + esc(item.lebar) + '" placeholder="Lebar"></div></td><td><span class="detail-read">' + esc(item.jumlah) + '</span><input class="form-control form-control-sm detail-editable detail-size" type="number" min="1" name="' + prefix + '[jumlah]" value="' + esc(item.jumlah) + '"></td><td class="detail-luas">' + esc(item.total_luas) + ' m²</td>';
        else row += '<td>' + displayDate(item.tanggal_pasang) + '</td>';
        row += '<td class="detail-expired">' + displayDate(item.tanggal_expired) + '</td><td>' + statusBadge(item.status) + '<select class="form-control form-control-sm mt-1 detail-editable" name="' + prefix + '[status]"><option value="Active"' + (item.status === 'Active' ? ' selected' : '') + '>Active</option><option value="Claim"' + (item.status === 'Claim' ? ' selected' : '') + '>Claim</option></select></td><td><button type="button" class="btn btn-sm btn-outline-danger remove-detail-item detail-edit-only"><i class="fa fa-times"></i></button></td></tr>';
        return row;
    }
    function renderItems() {
        var type = detailData.warranty_type;
        $('#detail-items-head').html(type === 'BUILDING' ? '<tr><th>Area</th><th>Product</th><th>Ukuran</th><th>Qty</th><th>Total Luas</th><th>Expired</th><th>Status</th><th></th></tr>' : '<tr><th>' + (type === 'CAR' ? 'Posisi Kaca' : 'Area PPF') + '</th><th>Product</th><th>Tanggal Pasang</th><th>Expired</th><th>Status</th><th></th></tr>');
        $('#detail-items').html(detailData.items.map(itemRow).join(''));
        $('#detail-items .detail-product').select2({ width: '100%' });
        setEditMode(editing);
    }
    function setEditMode(value) {
        editing = value;
        $('.detail-editable, .detail-edit-only').toggle(value);
        $('.detail-read').toggle(!value);
        $('#id_customer_detail').prop('disabled', !value).next('.select2-container').toggle(value);
        $('#detail-items .detail-product').next('.select2-container').toggle(value);
        var headerFields = $('#no_invoice_detail,#tanggal_pasang_detail,#installer_detail,#catatan_detail,#detail_vehicle .detail-editable,#detail_building .detail-editable');
        headerFields.closest('.form-group').toggle(true);
        headerFields.toggle(true).prop('readonly', !value);
        if (detailData) {
            var installation = $('#tanggal_pasang_detail');
            installation.attr('type', value ? 'date' : 'text').val(value ? String(detailData.tanggal_pasang || '').slice(0, 10) : displayDate(detailData.tanggal_pasang));
        }
        $('#btn_edit_warranty').toggle(!value && detailData && detailData.status !== 'Void');
        $('#btn_update_warranty').toggle(value);
    }
    function renderDetail(data) {
        detailData = data; editing = false;
        $('#id_warranty, #id_warranty_void').val(data.id_warranty); $('#kode_warranty_detail').val(data.kode_warranty); $('#pin_warranty_detail').val(data.pin_warranty || '-');
        $('#detail_type_label').text(data.warranty_type_name || data.warranty_type); $('#customer_detail_read').text(data.nama_customer || '-'); $('#id_customer_detail').val(data.id_customer).trigger('change');
        $('#no_invoice_detail').val(data.no_invoice || '-'); $('#tanggal_pasang_detail').data('iso', String(data.tanggal_pasang || '').slice(0, 10)); $('#installer_detail').val(data.installer || '-'); $('#catatan_detail').val(data.catatan || '-'); $('#create_by_detail').val(data.created_by || '-');
        $('#status_badge').replaceWith('<span id="status_badge">' + statusBadge(data.status) + '</span>'); $('#btn_show_qrcode').attr('data-kode', data.kode_warranty); $('#btn_digital_warranty').attr('href', '/warranty/' + data.kode_warranty);
        $('.detail-type-section').hide();
        if (data.warranty_type === 'BUILDING') { $('#detail_building').show(); $('#nama_bangunan_detail').val(data.building ? data.building.nama_bangunan : ''); $('#alamat_bangunan_detail').val(data.building ? data.building.alamat : ''); }
        else { $('#detail_vehicle').show(); var v = data.warranty_type === 'PPF' ? data.ppf : data.vehicle; $('#no_polisi_detail').val(v ? v.no_polisi : ''); $('#merk_mobil_detail').val(v ? v.merk_mobil : ''); $('#tipe_mobil_detail').val(v ? v.tipe_mobil : ''); $('#warna_mobil_detail').val(v ? v.warna_mobil : ''); $('#tahun_mobil_detail').val(v ? v.tahun_mobil : ''); }
        renderItems(); $('#btn_void_warranty').toggle(data.status !== 'Void'); $('#btn_digital_warranty').toggle(data.status !== 'Void'); $('#modal_detail_warranty').modal('show');
    }
    $('body').off('click', '.btn-detail-warranty').on('click', '.btn-detail-warranty', function () { $.getJSON('/warranty/show/' + $(this).attr('value')).done(renderDetail).fail(function () { Swal.fire('Gagal', 'Detail warranty tidak dapat dimuat.', 'error'); }); });
    $('body').off('click', '#btn_edit_warranty').on('click', '#btn_edit_warranty', function () { setEditMode(true); });
    $('#modal_detail_warranty').off('hidden.bs.modal').on('hidden.bs.modal', function () { detailData = null; editing = false; });
    $('#add-detail-item').on('click', function () { var item = detailData.warranty_type === 'CAR' ? { posisi_kaca: '', id_product: '', tanggal_pasang: $('#tanggal_pasang_detail').val(), tanggal_expired: '', status: 'Active' } : { area_pekerjaan: '', id_product: '', tanggal_pasang: $('#tanggal_pasang_detail').val(), tanggal_expired: '', status: 'Active' }; if (detailData.warranty_type === 'BUILDING') $.extend(item, { panjang: '', lebar: '', jumlah: '', total_luas: '0.00' }); detailData.items.push(item); renderItems(); });
    $(document).on('click', '.remove-detail-item', function () { var index = $(this).closest('tr').index(); detailData.items.splice(index, 1); renderItems(); });
    $(document).on('change', '.detail-product, #tanggal_pasang_detail', function () { $('#detail-items tr').each(function () { $(this).find('.detail-expired').text(detailExpiry($(this))); }); });
    $(document).on('input', '.detail-size', function () { calculateDetailArea($(this).closest('tr')); });
    window.UpdateWarranty = function () { var form = $('#form-edit-warranty'); if (!form[0].checkValidity()) { form[0].reportValidity(); return; } if (!$('#detail-items tr').length) { Swal.fire('Peringatan', 'Minimal satu item warranty wajib diisi.', 'warning'); return; } $('#btn_update_warranty').prop('disabled', true).text('Menyimpan...'); $.post('/warranty/update', form.serialize()).done(function () { $('#modal_detail_warranty').modal('hide'); $('#tabel_warranty').DataTable().ajax.reload(null, false); Swal.fire('Berhasil', 'Warranty berhasil diupdate.', 'success'); }).fail(function (xhr) { Swal.fire('Gagal', xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : 'Warranty gagal diupdate.', 'error'); }).always(function () { $('#btn_update_warranty').prop('disabled', false).html('<i class="fas fa-save"></i> Update'); }); };
})();
