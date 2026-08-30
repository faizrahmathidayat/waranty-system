//==============================
// DATATABLE USER
//==============================
$(function () {

    $('#tabel_user').DataTable({

        responsive: true,
        autoWidth: false,
        processing: true,
        serverSide: true,
        order: [],

        ajax: {
            url: "user/json",
            type: "GET",
        },

        columns: [

            {
                data: null,
                render: function (data, type, row, meta) {

                    return meta.row + meta.settings._iDisplayStart + 1;

                }
            },

            {
                data: 'name',
                name: 'name'
            },

            {
                data: 'username',
                name: 'username'
            },

            {
                data: 'role',
                name: 'role'
            },

            {
                data: 'status',

                render: function (data) {

                    return data == 'enabled'

                        ? '<span class="badge badge-success">Enabled</span>'

                        : '<span class="badge badge-danger">Disabled</span>';

                }

            },

            {
                data: 'user_id',

                render: function (data) {

                    return '<center>\
                        <a value="' + data + '"\
                        class="btn btn-sm btn-info btn-detail-user"\
                        data-toggle="modal"\
                        data-target="#modal_detail_user">\
                        <i class="fas fa-eye"></i> View\
                        </a>\
                    </center>';

                }

            }

        ]

    });

});

//==============================
// MODAL TAMBAH
//==============================

$('#modal_tambah_user').on('shown.bs.modal', function () {

    $('#name').trigger('focus');

});

$('button[type="reset"]').on('click', function () {

    var form = $(this).closest('form');

    // Hapus class invalid
    form.find('.is-invalid').removeClass('is-invalid');

    // Hapus pesan error
    form.find('.invalid-feedback').html('');

    // Focus kembali ke name
    setTimeout(function () {
        $('#name').trigger('focus');
    }, 100);

});


$(document).ready(function () {

    $("#modal_tambah_user").on("hidden.bs.modal", function () {

        $("#name").val('');
        $("#username").val('');
        $("#password").val('');

        $("#role").val('');

        $("#status_enabled").prop("checked", true);

        $('#name,#username,#password,#role')
            .removeClass('is-invalid');

    });

});

function SimpanUser() {

    var nama = $('#name').val();
    var username = $('#username').val();
    var password = $('#password').val();
    var role = $('#role').val();


        $("#name").on('keyup',function() {
      if ($(this).val().length > 0) {
        $('#name').removeClass('is-invalid');
      }
    });

    $("#username").on('keyup',function() {
        if ($(this).val().length > 0) {
          $('#username').removeClass('is-invalid');
        }
    });

    $("#password").on('keyup',function() {
        if ($(this).val().length > 0) {
          $('#password').removeClass('is-invalid');
        }
    });

    $("#role").on('keyup',function() {
        if ($(this).val().length > 0) {
          $('#role').removeClass('is-invalid');
        }
    });

    if (nama == '') {

        $('#name').addClass('is-invalid');
        $('#name_notif').html('Nama tidak boleh kosong');
        $('#name').focus();

        return false;

    }

    if (username == '') {

        $('#username').addClass('is-invalid');
        $('#username_notif').html('Username tidak boleh kosong');
        $('#username').focus();

        return false;

    }

    if (password == '') {

        $('#password').addClass('is-invalid');
        $('#password_notif').html('Password tidak boleh kosong');
        $('#password').focus();

        return false;

    }

    if (role == '') {

        $('#role').addClass('is-invalid');
        $('#role_notif').html('Role belum dipilih');
        $('#role').focus();

        return false;

    }



     var btn = $('#simpan_user');

    btn.prop('disabled', true);

    btn.html(`
        <span class="spinner-border spinner-border-sm mr-1"></span>
        Menyimpan...
    `);
    var formInput = $('.form-user').serialize();

    $.ajax({

        type: "POST",

        url: "user/store",

        data: formInput,

        success: function(data){

            if(data == 'must_unique'){

                Swal.fire({

                    icon:'warning',

                    title:'Username sudah digunakan',

                    timer:2500,

                    showConfirmButton:false

                });

                $('#username').focus();

            }else{

                Swal.fire({

                    icon:'success',

                    title:'Berhasil',

                    text:'User berhasil disimpan',

                    timer:2500,

                    showConfirmButton:false

                });

                $('#modal_tambah_user').modal('hide');

                $('#tabel_user').DataTable().ajax.reload(null,false);

            }

        },

        error:function(){

            Swal.fire({

                icon:'error',

                title:'Gagal',

                text:'User gagal disimpan'

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

//==============================
// DETAIL USER
//==============================
$('body').on('click', '.btn-detail-user', function () {

    const idUser = $(this).attr('value');

    $.ajax({

        url: "user/show/" + idUser,

        type: "GET",

        dataType: "JSON",

        success: function (data) {

            $('[name="user_id"]').val(data.user_id);
            $('[name="user_id_hapus"]').val(data.user_id);

            $('[name="name_detail"]').val(data.name);
            $('[name="username_detail"]').val(data.username);

            // Password selalu kosong
            $('[name="password_detail"]').val('');

            $('#role_detail').val(data.role);

            if (data.status == 'enabled') {

                $('#status_enabled_detail').prop('checked', true);

            } else {

                $('#status_disabled_detail').prop('checked', true);

            }

            $('#modal_detail_user').modal('show');

            // readonly
            $('#name_detail').prop('readonly', true);
            $('#username_detail').prop('readonly', true);
            $('#password_detail').prop('readonly', true);

            $('#role_detail').prop('disabled', true);

            $('input[name="status_detail"]').prop('disabled', true);

            $('#update_user').hide();
            $('#edit_user').show();

        }

    });

});

//==============================
// EDIT USER
//==============================
$('body').on('click', '#edit_user', function () {

    $('#update_user').show();
    $('#edit_user').hide();

    $('#name_detail').prop('readonly', false);
    $('#username_detail').prop('readonly', false);
    $('#password_detail').prop('readonly', false);

    $('#role_detail').prop('disabled', false);

    $('input[name="status_detail"]').prop('disabled', false);

});

//==============================
// CLOSE MODAL
//==============================
$('body').on('click', '#close_modal_detail_user', function () {

    $('#update_user').hide();
    $('#edit_user').show();

    $('#name_detail').prop('readonly', true);
    $('#username_detail').prop('readonly', true);
    $('#password_detail').prop('readonly', true);

    $('#role_detail').prop('disabled', true);

    $('input[name="status_detail"]').prop('disabled', true);

});

function UpdateUser() {

    var nama = $('#name_detail').val();
    var username = $('#username_detail').val();
    var role = $('#role_detail').val();

    if (nama == '') {

        $('#name_detail').addClass('is-invalid');
        $('#name_detail_notif').html('Nama tidak boleh kosong');
        $('#name_detail').focus();
        return false;

    }

    if (username == '') {

        $('#username_detail').addClass('is-invalid');
        $('#username_detail_notif').html('Username tidak boleh kosong');
        $('#username_detail').focus();
        return false;

    }

    if (role == '') {

        $('#role_detail').addClass('is-invalid');
        $('#role_detail').focus();
        return false;

    }

    var formUpdate = $('.form-edit-user').serialize();

    $.ajax({

        type: "POST",

        url: "user/update",

        data: formUpdate,

        success: function(data){

            if(data == 'must_unique'){

                Swal.fire({
                    icon:'warning',
                    title:'Username sudah digunakan',
                    timer:2500,
                    showConfirmButton:false
                });

                $('#username_detail').focus();

            }else if(data == 'not_found'){

                Swal.fire({
                    icon:'error',
                    title:'User tidak ditemukan'
                });

            }else{

                $('#modal_detail_user').modal('hide');

                Swal.fire({

                    icon:'success',

                    title:'Berhasil',

                    text:'User berhasil diupdate',

                    timer:2500,

                    showConfirmButton:false

                });

                $('#tabel_user').DataTable().ajax.reload(null,false);

            }

        },

        error:function(){

            Swal.fire({

                icon:'error',

                title:'Gagal',

                text:'User gagal diupdate'

            });

        }

    });

}

function HapusUser() {

    Swal.fire({
        title: 'Hapus User',
        text: "Yakin ingin menghapus user ini?",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Ya',
        cancelButtonText: 'Batal'
    }).then((result) => {

        if (result.isConfirmed) {

            var formHapus = $('.form-hapus-user').serialize();

            $.ajax({

                type: 'POST',

                url: 'user/destroy',

                data: formHapus,

                success: function(data){

                    if(data == 'SELF'){

                        Swal.fire({
                            icon:'warning',
                            title:'Tidak dapat menghapus akun yang sedang digunakan.'
                        });

                    }else if(data == 'NOT_FOUND'){

                        Swal.fire({
                            icon:'error',
                            title:'User tidak ditemukan.'
                        });

                    }else{

                        Swal.fire({
                            icon:'success',
                            title:'Berhasil',
                            text:'User berhasil dihapus.',
                            timer:2500,
                            showConfirmButton:false
                        });

                        $('#modal_detail_user').modal('hide');

                        $('#tabel_user').DataTable().ajax.reload(null,false);

                    }

                },

                error:function(){

                    Swal.fire({
                        icon:'error',
                        title:'Gagal',
                        text:'User gagal dihapus.'
                    });

                }

            });

        }

    });

}
