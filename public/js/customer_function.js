// Prefer the specific per-field validation message (xhr.responseJSON.errors)
// over Laravel's generic "The given data was invalid." wrapper text.
function firstValidationError(xhr, fallback) {
    var json = xhr && xhr.responseJSON;
    if (json && json.errors) {
        var firstField = Object.keys(json.errors)[0];
        if (firstField && json.errors[firstField] && json.errors[firstField][0]) {
            return json.errors[firstField][0];
        }
    }
    if (json && json.message) return json.message;
    return fallback;
}

//datatables customer
$(function() {
    $('#tabel_customer').DataTable({
        responsive: true,
        autoWidth: false,
        deferRender: true,
        searchDelay: 350,
        processing: true,
        serverSide: true,
        order: [],
        "ajax": {
            "url": "customer/json",
            "type": "GET",
        }, // memanggil route yang menampilkan data json
        columns: [

            {
                data: null,
                orderable: false,
                searchable: false,
                render : function(data, type, row, meta) {
                    return meta.row + meta.settings._iDisplayStart + 1;
                }
              },
            { // mengambil & menampilkan kolom sesuai tabel database
                data: 'nama_customer',
                name: 'nama_customer'
            },
            {
                data: 'no_hp',
                name: 'no_hp'
            },
            {
                data: 'email',
                name: 'email'
            },
            {
                data: 'alamat',
                name: 'alamat'
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
                data: 'id_customer',
                name: 'id_customer',
                "render": function(data, type, row) {
                   return '<button type="button" value="' + data + '" class="btn btn-sm btn-info btn-detail-customer" title="View"><i class="fas fa-eye"></i> View</button>';
                }
            },
        ]
    });
});

//klik tombol tambah 
$('#modal_tambah_customer').on('shown.bs.modal', function() {
    $('#nama_customer').trigger('focus')
})

//klik tombol reset modal tambah
$(document).on('click', '#modal_tambah_customer button[type="reset"]', function (event) {
    event.preventDefault();

    // The modal markup contains a table, so the browser can place the reset
    // button outside the form. Target the Customer form explicitly.
    var form = $('#form-customer');
    if (form.length) form[0].reset();

    // Inputs and feedback can be moved outside <form> by the browser because
    // this legacy modal places the form inside a table. Clear the whole modal.
    var modal = $('#modal_tambah_customer');
    modal.find('.is-invalid').removeClass('is-invalid');
    modal.find('.invalid-feedback').empty();
    modal.find('input:not([type="hidden"]), textarea').val('');

    $('#vehicleRows').empty();
    $('#buildingRows').empty();

    setTimeout(function () {
        modal.find('.is-invalid').removeClass('is-invalid');
        modal.find('.invalid-feedback').empty();
        $('#nama_customer').trigger('focus');
    }, 20);
});

  //klik tombol close modal tambah
  $(document).ready(function() {
    $("#modal_tambah_customer").on("hidden.bs.modal", function() {
      document.getElementById("nama_customer").value = "";
      document.getElementById("no_hp").value = "";
      document.getElementById("email").value = "";
      document.getElementById("alamat").value = "";

      $('#nama_customer,#no_hp,#email,#alamat').removeClass('is-invalid');

      $('#vehicleRows').empty();
      $('#buildingRows').empty();
    });
  });

// ---- Vehicle / Building baris dinamis (Tambah Customer & Detail Customer) ----
function customerVehicleRowHtml(idx) {
    return '<div class="row align-items-center mb-2" data-row>' +
        '<div class="col-6 col-md-2 mb-1"><input type="text" name="vehicles[' + idx + '][no_polisi]" class="form-control form-control-sm" placeholder="No Polisi"></div>' +
        '<div class="col-6 col-md-2 mb-1"><input type="text" name="vehicles[' + idx + '][merk]" class="form-control form-control-sm" placeholder="Merk"></div>' +
        '<div class="col-6 col-md-2 mb-1"><input type="text" name="vehicles[' + idx + '][model]" class="form-control form-control-sm" placeholder="Model/Tipe"></div>' +
        '<div class="col-6 col-md-2 mb-1"><input type="text" name="vehicles[' + idx + '][warna]" class="form-control form-control-sm" placeholder="Warna"></div>' +
        '<div class="col-8 col-md-2 mb-1"><input type="number" name="vehicles[' + idx + '][tahun]" class="form-control form-control-sm" placeholder="Tahun"></div>' +
        '<div class="col-4 col-md-2 mb-1 text-right"><button type="button" class="btn btn-sm btn-danger remove-row"><i class="fa fa-trash"></i></button></div>' +
        '</div>';
}

function customerBuildingRowHtml(idx) {
    return '<div class="row align-items-center mb-2" data-row>' +
        '<div class="col-12 col-md-4 mb-1"><input type="text" name="buildings[' + idx + '][nama_bangunan]" class="form-control form-control-sm" placeholder="Nama Bangunan"></div>' +
        '<div class="col-10 col-md-7 mb-1"><input type="text" name="buildings[' + idx + '][alamat]" class="form-control form-control-sm" placeholder="Alamat"></div>' +
        '<div class="col-2 col-md-1 mb-1 text-right"><button type="button" class="btn btn-sm btn-danger remove-row"><i class="fa fa-trash"></i></button></div>' +
        '</div>';
}

var customerVehicleRowIndex = 0;
var customerBuildingRowIndex = 0;
var customerVehicleRowIndexDetail = 0;
var customerBuildingRowIndexDetail = 0;

$(document).on('click', '#addVehicleRow', function () {
    $('#vehicleRows').append(customerVehicleRowHtml(customerVehicleRowIndex++));
});
$(document).on('click', '#addBuildingRow', function () {
    $('#buildingRows').append(customerBuildingRowHtml(customerBuildingRowIndex++));
});
$(document).on('click', '#addVehicleRowDetail', function () {
    $('#vehicleRowsDetail').append(customerVehicleRowHtml(customerVehicleRowIndexDetail++));
});
$(document).on('click', '#addBuildingRowDetail', function () {
    $('#buildingRowsDetail').append(customerBuildingRowHtml(customerBuildingRowIndexDetail++));
});
$(document).on('click', '.remove-row', function () {
    $(this).closest('[data-row]').remove();
});


   //klik tombol simpan
 function SimpanCustomer() {

    var nc = document.getElementById("nama_customer").value;
    var nh = document.getElementById("no_hp").value;
    var em = document.getElementById("email").value;
    var am = document.getElementById("alamat").value;

    $("#nama_customer").on('keyup',function() {
      if ($(this).val().length > 0) {
        $('#nama_customer').removeClass('is-invalid');
      }
    });
    $("#no_hp").on('keyup',function() {
        if ($(this).val().length > 0) {
          $('#no_hp').removeClass('is-invalid');
        }
    });
    $("#email").on('keyup',function() {
        if ($(this).val().length > 0) {
          $('#email').removeClass('is-invalid');
        }
    });
    $("#alamat").on('keyup',function() {
        if ($(this).val().length > 0) {
          $('#alamat').removeClass('is-invalid');
        }
    });

      if (nc == '') {
        $('#nama_customer').addClass('is-invalid');
        $('#nama_customer').trigger('focus');
        $('#nama_customer_notif').html('Nama Customer Tidak Boleh Kosong');
        return false;
      } else if (nh == '') {
        $('#no_hp').addClass('is-invalid');
        $('#no_hp').trigger('focus');
        $('#no_hp_notif').html('No HP tidak boleh kosong');
        return false;
      } else if (em == '') {
        $('#email').addClass('is-invalid');
        $('#email').trigger('focus');
        $('#email_notif').html('Email User tidak boleh kosong');
        return false;
      } else if (am == '') {
        $('#alamat').addClass('is-invalid');
        $('#alamat').trigger('focus');
        $('#alamat_notif').html('Lokasi User tidak boleh kosong');
        return false;
      }
      
      var btn = $('#simpan_customer');

    btn.prop('disabled', true);

    btn.html(`
        <span class="spinner-border spinner-border-sm mr-1"></span>
        Menyimpan...
    `);

    var formInput = $('.form-customer').serialize();
    $.ajax({
      type: 'POST',
      url: "customer/store",
      data: formInput,
      success: function(data) {
        if (data == 'must_unique') {
            Swal.fire({
                icon: 'warning',
                title: 'Email sudah terdaftar',
                text: '',
                timer: 3500,
                showConfirmButton: false,
              }).then(function() {
                $('#email').trigger('focus');
              });

        } else {
          Swal.fire({
            icon: 'success',
            title: 'Berhasil!',
            text: 'Customer Berhasil disimpan',
            timer: 3000,
          }).then(function() {
            $('#nama_customer').trigger('focus');
            $('#tabel_customer').DataTable().ajax.reload();
            document.getElementById("nama_customer").value = "";
            document.getElementById("no_hp").value = "";
            document.getElementById("email").value = "";
            document.getElementById("alamat").value = "";
          });
        }
      },
      error: function(xhr) {
          var message = firstValidationError(xhr, 'Customer Gagal di simpan');
          Swal.fire({
              icon: 'error',
              title: 'Gagal!',
              text: message
          })
          $('#tabel_customer').DataTable().ajax.reload(null, false);
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
    $('#nama_customer').trigger('focus');
  }

//menampilkan modal detail
 $('body').on('click', '.btn-detail-customer', function() {
    const idCustomer = $(this).attr('value');

    $.ajax({
        url: "customer/show/" + idCustomer,
        type: "GET",
        dataType: "JSON",
        success: function(data) {

            $('[name="id_customer"]').val(data.id_customer);
            $('[name="id_customer_hapus"]').val(data.id_customer);

            $('[name="nama_customer_detail"]').val(data.nama_customer);
            $('[name="no_hp_detail"]').val(data.no_hp);
            $('[name="email_detail"]').val(data.email);
            $('[name="alamat_detail"]').val(data.alamat);

            $('#modal_detail_customer').modal('show');

            $("#nama_customer_detail").attr("readonly", true);
            $("#no_hp_detail").attr("readonly", true);
            $("#email_detail").attr("readonly", true);
            $("#alamat_detail").attr("readonly", true);

            // Map database enabled/disabled to the shared switch UI.
            $('#status_customer').prop('checked', data.status === 'enabled').prop('disabled', true);
            $('#status_customer_value').val(data.status === 'enabled' ? 'enabled' : 'disabled');
            $('#status_customer_label').text(data.status === 'enabled' ? 'Active' : 'Inactive');

            document.getElementById('update_customer').style.display = 'none';

            if (data.status == 'enabled') {
                document.getElementById('hapus_customer').style.display = 'none';
            } else {
                document.getElementById('hapus_customer').style.display = '';
            }

        }
    });
});


//klik tombol edit modal detail
$('body').on('click', '#edit_customer', function() {

    document.getElementById('update_customer').style.display = '';
    document.getElementById('edit_customer').style.display = 'none';
  
    $("#nama_customer_detail").attr("readonly", false);
    $("#no_hp_detail").attr("readonly", false);
    $("#email_detail").attr("readonly", false);
    $("#alamat_detail").attr("readonly", false);

    $('#status_customer').prop('disabled', false);
  
  });

// Keep the hidden value compatible with Customer's existing enabled/disabled
// database mapping while showing only one switch to the user.
$(document).on('change', '#status_customer', function () {
    var active = $(this).is(':checked');
    $('#status_customer_value').val(active ? 'enabled' : 'disabled');
    $('#status_customer_label').text(active ? 'Active' : 'Inactive');
});


  //klik tombol close edit modal detail
  $('body').on('click', '#close_modal_detail_customer', function() {

    document.getElementById('update_customer').style.display = 'none';
    document.getElementById('edit_customer').style.display = '';
  
    $("#nama_customer_detail").attr("readonly", true);
    $("#no_hp_detail").attr("readonly", true);
    $("#email_detail").attr("readonly", true);
    $("#alamat_detail").attr("readonly", true);

    $('#nama_customer_detail,#no_hp_detail,#email_detail,#alamat_detail').removeClass('is-invalid');

    $('#vehicleRowsDetail').empty();
    $('#buildingRowsDetail').empty();

  });


  function UpdateCustomer() {

    var nama = $('#nama_customer_detail').val();
    var nohp = $('#no_hp_detail').val();
    var email = $('#email_detail').val();
    var alamat = $('#alamat_detail').val();

    $("#nama_customer_detail").on('keyup', function () {
        if ($(this).val().length > 0) {
            $(this).removeClass('is-invalid');
        }
    });

    $("#no_hp_detail").on('keyup', function () {
        if ($(this).val().length > 0) {
            $(this).removeClass('is-invalid');
        }
    });

    $("#email_detail").on('keyup', function () {
        if ($(this).val().length > 0) {
            $(this).removeClass('is-invalid');
        }
    });

    $("#alamat_detail").on('keyup', function () {
        if ($(this).val().length > 0) {
            $(this).removeClass('is-invalid');
        }
    });


    if (nama == '') {
        $('#nama_customer_detail').addClass('is-invalid');
        $('#nama_customer_detail').trigger('focus');
        $('#nama_customer_detail_notif').html('Nama Customer Tidak Boleh Kosong');
        return false;
      } else if (nohp == '') {
        $('#no_hp_detail').addClass('is-invalid');
        $('#no_hp_detail').trigger('focus');
        $('#no_hp_detail_notif').html('No HP tidak boleh kosong');
        return false;
      } else if (email == '') {
        $('#email_detail').addClass('is-invalid');
        $('#email_detail').trigger('focus');
        $('#email_detail_notif').html('Email User tidak boleh kosong');
        return false;
      } else if (alamat == '') {
        $('#alamat_detail').addClass('is-invalid');
        $('#alamat_detail').trigger('focus');
        $('#alamat_detail_notif').html('Lokasi User tidak boleh kosong');
        return false;
      }

    var formUpdate = $('.form-edit-customer').serialize();

    $.ajax({
        type: "POST",
        url: "customer/update",
        data: formUpdate,
        success: function (data) {

            if (data == 'must_unique') {

                Swal.fire({
                    icon: 'warning',
                    title: 'Customer sudah terdaftar',
                    timer: 3000,
                    showConfirmButton: false
                });

            } else {

                $('#modal_detail_customer').modal('hide');

                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil!',
                    text: 'Customer berhasil diupdate',
                    timer: 2500,
                    showConfirmButton: false
                });

                $('#tabel_customer').DataTable().ajax.reload(null, false);

                $('#update_customer').hide();
                $('#edit_customer').show();
            }

        },
        error: function (xhr) {

            var message = firstValidationError(xhr, 'Customer gagal diupdate');
            Swal.fire({
                icon: 'error',
                title: 'Gagal!',
                text: message
            });

        }

    });

}

function HapusCustomer() {

    Swal.fire({
        title: 'Hapus Customer',
        text: "Yakin ingin menghapus customer ini?",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Ya',
        cancelButtonText: 'Batal'
    }).then((result) => {

        if (result.isConfirmed) {

            var formHapus = $('.form-hapus-customer').serialize();

            $.ajax({
                type: 'POST',
                url: "customer/destroy",
                data: formHapus,

                success: function(data) {

                    if (data == 'USED') {

                        Swal.fire({
                            icon: 'warning',
                            title: 'Gagal',
                            text: 'Customer sudah memiliki data warranty sehingga tidak dapat dihapus.'
                        });

                    } else {

                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil',
                            text: 'Customer berhasil dihapus.',
                            timer: 2500,
                            showConfirmButton: false
                        });

                    }

                    $('#tabel_customer').DataTable().ajax.reload(null, false);
                    $('#modal_detail_customer').modal('hide');

                },

                error: function() {

                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal!',
                        text: 'Customer gagal dihapus.'
                    });

                    $('#tabel_customer').DataTable().ajax.reload(null, false);
                }

            });

        } else if (result.dismiss === Swal.DismissReason.cancel) {

            $('#tabel_customer').DataTable().ajax.reload(null, false);
            $('#modal_detail_customer').modal('hide');

        }

    });

}
