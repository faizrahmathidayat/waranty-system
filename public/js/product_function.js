// ==========================================
// DATATABLE PRODUCT
// ==========================================
$(function () {

    $('#tabel_product').DataTable({

        responsive: true,
        autoWidth: false,
        deferRender: true,
        searchDelay: 350,
        processing: true,
        serverSide: true,
        order: [],

        ajax: {
            url: "product/json",
            type: "GET",
        },

        columns: [

            {
                data: null,
                orderable: false,
                searchable: false,
                render: function (data, type, row, meta) {
                    return meta.row + meta.settings._iDisplayStart + 1;
                }
            },

            {
                data: 'nama_produk',
                name: 'nama_produk'
            },

            {
                data: 'brand',
                name: 'brand'
            },

            {
                data: 'jenis',
                name: 'jenis'
            },
            {
                data: 'product_type_name',
                name: 'product_types.name'
            },

            {
              data: 'status',
              render: function(data) {
                  return data == 'enabled'
                      ? '<span class="badge badge-success">Enabled</span>'
                      : '<span class="badge badge-danger">Disabled</span>';
              }
            },

            {
                data: 'id_product',
                name: 'id_product',
                render: function (data) {

                    return '<center>\
                        <a data-toggle="modal" \
                           data-placement="top" \
                           value="' + data + '" \
                           class="btn btn-sm btn-info btn-detail-product" \
                           title="View" \
                           data-target="#modal_detail_product">\
                        <i class="fas fa-eye"></i> View\
                        </a>\
                        </center>';

                }
            }

        ]

    });

});


// ==========================================
// FOCUS MODAL TAMBAH
// ==========================================

$('#modal_tambah_product').on('shown.bs.modal', function () {

    $('#nama_produk').trigger('focus');

});


// ==========================================
// RESET MODAL
// ==========================================

$('body').on('click', '.btn-danger', function () {

    $('#nama_produk').trigger('focus');

});


// ==========================================
// CLOSE MODAL
// ==========================================

$(document).ready(function () {

    $("#modal_tambah_product").on("hidden.bs.modal", function () {

        $("#nama_produk").val('');
        $("#brand").val('');
        $("#jenis").val('');
        $("#keterangan").val('');

        $('#nama_produk').removeClass('is-invalid');
        $('#brand').removeClass('is-invalid');
        $('#jenis').removeClass('is-invalid');

    });

});



// ==========================================
// SIMPAN PRODUCT
// ==========================================

function SimpanProduct() {

    var nama = $('#nama_produk').val();
    var brand = $('#brand').val();
    var jenis = $('#jenis').val();
    var ket = $('#keterangan').val();


    $("#nama_produk").keyup(function () {

        if ($(this).val().length > 0) {

            $(this).removeClass('is-invalid');

        }

    });

    $("#brand").keyup(function () {

        if ($(this).val().length > 0) {

            $(this).removeClass('is-invalid');

        }

    });

    $("#jenis").keyup(function () {

        if ($(this).val().length > 0) {

            $(this).removeClass('is-invalid');

        }

    });

    if (nama == '') {

        $('#nama_produk').addClass('is-invalid');
        $('#nama_produk').focus();
        $('#nama_produk_notif').html('Nama Product tidak boleh kosong');

        return false;

    }

    if (brand == '') {

        $('#brand').addClass('is-invalid');
        $('#brand').focus();
        $('#brand_notif').html('Brand tidak boleh kosong');

        return false;

    }

    if (jenis == '') {

        $('#jenis').addClass('is-invalid');
        $('#jenis').focus();
        $('#jenis_notif').html('Jenis tidak boleh kosong');

        return false;

    }

    var btn = $('#simpan_product');

    btn.prop('disabled', true);

    btn.html(`
        <span class="spinner-border spinner-border-sm mr-1"></span>
        Menyimpan...
    `);
    var formInput = $('.form-product').serialize();


    $.ajax({

        type: "POST",

        url: "product/store",

        data: formInput,

        success: function (data) {

            if (data == "must_unique") {

                Swal.fire({

                    icon: 'warning',
                    title: 'Product sudah terdaftar',
                    timer: 3000,
                    showConfirmButton: false

                }).then(function () {

                    $('#nama_produk').focus();

                });

            }

            else {

                Swal.fire({

                    icon: 'success',
                    title: 'Berhasil!',
                    text: 'Product berhasil disimpan',
                    timer: 2500,
                    showConfirmButton: false

                }).then(function () {

                    $('#tabel_product').DataTable().ajax.reload();

                    $('#nama_produk').val('');
                    $('#brand').val('');
                    $('#jenis').val('');
                    $('#keterangan').val('');

                    $('#nama_produk').focus();

                });

            }

        },

        error: function () {

            Swal.fire({

                icon: 'error',
                title: 'Gagal!',
                text: 'Product gagal disimpan'

            });

            $('#tabel_product').DataTable().ajax.reload(null, false);

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
// DETAIL PRODUCT
// ==========================================

$('body').on('click', '.btn-detail-product', function () {

    const idProduct = $(this).attr('value');

    $.ajax({

        url: "product/show/" + idProduct,
        type: "GET",
        dataType: "JSON",

        success: function (data) {

            $('[name="id_product"]').val(data.id_product);
            $('[name="id_product_hapus"]').val(data.id_product);

            $('[name="nama_produk_detail"]').val(data.nama_produk);
            $('[name="brand_detail"]').val(data.brand);
            $('[name="jenis_detail"]').val(data.jenis);
            $('[name="id_product_type_detail"]').val(data.id_product_type).trigger('change');
            $('[name="kode_produk_detail"]').val(data.kode_produk);
            $('[name="harga_default_detail"]').val(data.harga_default);
            $('#is_warranty_eligible_detail').prop('checked', !!data.is_warranty_eligible);
            $('[name="keterangan_detail"]').val(data.keterangan);

            // Pilih radio sesuai status dari database
            if (data.status == 'enabled') {
            $('#status_enabled').prop('checked', true);
            } else {
            $('#status_disabled').prop('checked', true);
            }

            $('input[name="status"]').prop('disabled', true);

            $('#modal_detail_product').modal('show');

            $("#nama_produk_detail").attr("readonly", true);
            $("#brand_detail").attr("readonly", true);
            $("#jenis_detail").attr("readonly", true);
            $('#id_product_type_detail, #kode_produk_detail, #harga_default_detail, #is_warranty_eligible_detail').prop('disabled', true);
            $("#keterangan_detail").attr("readonly", true);

            $('#update_product').hide();
            $('#edit_product').show();

        }

    });

});


// ==========================================
// EDIT PRODUCT
// ==========================================

$('body').on('click', '#edit_product', function () {

    $('#update_product').show();
    $('#edit_product').hide();

    $("#nama_produk_detail").attr("readonly", false);
    $("#brand_detail").attr("readonly", false);
    $("#jenis_detail").attr("readonly", false);
    $('#id_product_type_detail, #kode_produk_detail, #harga_default_detail, #is_warranty_eligible_detail').prop('disabled', false);
    $("#keterangan_detail").attr("readonly", false);
    $('input[name="status"]').prop('disabled', false);

    $('#nama_produk_detail').trigger('focus');

});


// ==========================================
// CLOSE DETAIL MODAL
// ==========================================

$('body').on('click', '#close_modal_detail_product', function () {

    $('#update_product').hide();
    $('#edit_product').show();

    $("#nama_produk_detail").attr("readonly", true);
    $("#brand_detail").attr("readonly", true);
    $("#jenis_detail").attr("readonly", true);
    $('#id_product_type_detail, #kode_produk_detail, #harga_default_detail, #is_warranty_eligible_detail').prop('disabled', true);
    $("#keterangan_detail").attr("readonly", true);

    $('#nama_produk_detail').removeClass('is-invalid');
    $('#brand_detail').removeClass('is-invalid');
    $('#jenis_detail').removeClass('is-invalid');

});

// ==========================================
// UPDATE PRODUCT
// ==========================================

function UpdateProduct() {

    var nama = $('#nama_produk_detail').val();
    var brand = $('#brand_detail').val();
    var jenis = $('#jenis_detail').val();
    var keterangan = $('#keterangan_detail').val();

    $("#nama_produk_detail").on('keyup', function () {
        if ($(this).val().length > 0) {
            $(this).removeClass('is-invalid');
        }
    });

    $("#brand_detail").on('keyup', function () {
        if ($(this).val().length > 0) {
            $(this).removeClass('is-invalid');
        }
    });

    $("#jenis_detail").on('keyup', function () {
        if ($(this).val().length > 0) {
            $(this).removeClass('is-invalid');
        }
    });

    if (nama == '') {

        $('#nama_produk_detail').addClass('is-invalid');
        $('#nama_produk_detail').focus();
        $('#nama_produk_detail_notif').html('Nama Product tidak boleh kosong');

        return false;
    }

    if (brand == '') {

        $('#brand_detail').addClass('is-invalid');
        $('#brand_detail').focus();
        $('#brand_detail_notif').html('Brand tidak boleh kosong');

        return false;
    }

    if (jenis == '') {

        $('#jenis_detail').addClass('is-invalid');
        $('#jenis_detail').focus();
        $('#jenis_detail_notif').html('Jenis tidak boleh kosong');

        return false;
    }

    var formUpdate = $('.form-edit-product').serialize();

    $.ajax({

        type: "POST",

        url: "product/update",

        data: formUpdate,

        success: function (data) {

            if (data == 'must_unique') {

                Swal.fire({

                    icon: 'warning',
                    title: 'Product sudah terdaftar',
                    timer: 3000,
                    showConfirmButton: false

                });

            } else {

                $('#modal_detail_product').modal('hide');

                Swal.fire({

                    icon: 'success',
                    title: 'Berhasil!',
                    text: 'Product berhasil diupdate',
                    timer: 2500,
                    showConfirmButton: false

                });

                $('#tabel_product').DataTable().ajax.reload(null, false);

                $('#update_product').hide();
                $('#edit_product').show();

            }

        },

        error: function () {

            Swal.fire({

                icon: 'error',
                title: 'Gagal!',
                text: 'Product gagal diupdate'

            });

        }

    });

}

// ==========================================
// HAPUS PRODUCT
// ==========================================

function HapusProduct() {

    Swal.fire({

        title: 'Hapus Product',
        text: "Yakin ingin menghapus product ini?",
        icon: 'warning',

        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',

        confirmButtonText: 'Ya',
        cancelButtonText: 'Batal'

    }).then((result) => {

        if (result.isConfirmed) {

            var formHapus = $('.form-hapus-product').serialize();

            $.ajax({

                type: "POST",

                url: "product/destroy",

                data: formHapus,

                success: function (data) {
                    

                    if (data == "USED") {

                        Swal.fire({

                            icon: 'warning',
                            title: 'Gagal',
                            text: 'Product sudah digunakan pada data warranty sehingga tidak dapat dihapus.'

                        });

                    } else {

                        Swal.fire({

                            icon: 'success',
                            title: 'Berhasil',
                            text: 'Product berhasil dihapus.',
                            timer: 2500,
                            showConfirmButton: false

                        });

                    }

                    $('#tabel_product').DataTable().ajax.reload(null, false);
                    $('#modal_detail_product').modal('hide');

                },

                error: function () {

                    Swal.fire({

                        icon: 'error',
                        title: 'Gagal!',
                        text: 'Product gagal dihapus.'

                    });

                    $('#tabel_product').DataTable().ajax.reload(null, false);

                }

            });

        }

        else if (result.dismiss === Swal.DismissReason.cancel) {

            $('#tabel_product').DataTable().ajax.reload(null, false);
            $('#modal_detail_product').modal('hide');

        }

    });

}
