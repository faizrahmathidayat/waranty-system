//datatables
$(function() {
    
    $("#table_user").DataTable({
        "responsive": true,
        "lengthChange": true,
        "autoWidth": false,
        "ajax": {
            "url": "user/json",
            "type": "GET",
          }, // memanggil route yang menampilkan data json
          columns: [
    
            {
              data: null,
              render : function(data, type, row, meta) {
                  return meta.row + meta.settings._iDisplayStart + 1;
              }
            },
            { // mengambil & menampilkan kolom sesuai tabel database
              data: 'name',
              name: 'name'
            },
            {
              data: 'username',
              name: 'username'
            },
            {
                data: 'level',
                name: 'level'
            },
            {
              data: 'id',
              name: 'id',
            "render" : function(data, type, row) {
                // return "<center><button class='btn btn-primary btn-sm btn-detail' value='data : 'ID_AKTFS',name:'ID_AKTFS''>Lihat</button></center>";
                return '<center><a data-toggle="tooltip" data-placement="top" value="' + data + '" class="btn-detail-user" title="Detail" data-target="#modal_detail_customer"><i class="fa fa-edit" style="font-size: 22px; cursor:pointer;"></i></a></center>';
    
              }
            },
          ],
    })
});


//validate password
$('#password').on('input', function() {
    var password = $(this).val();
    validatePassword(password);
  });
  
  function validatePassword(password) {
    var isValid = /^(?=.*\d)(?=.*[a-zA-Z]).{8,}$/.test(password);
  
    if (isValid) {
      $('#password').removeClass('is-invalid');
      $('#simpan').prop('disabled', false);
    } else {
      $('#password').addClass('is-invalid');
      $('#simpan').prop('disabled', true);
    }
  }


//remove class after close modal
$(document).ready(function() {
    $("#modal_tambah_user").on("hidden.bs.modal", function() {
        document.getElementById("nama").value = "";
        document.getElementById("username").value = "";
        document.getElementById("password").value = "";
        document.getElementById("level").value = "";


        $('#nama,#username,#password,#level').removeClass('is-invalid');


    });
});

//auto focus ketika click button tambah
$('#modal_tambah_user').on('shown.bs.modal', function() {
    $('#nama').trigger('focus')
})

//autofocus ketika klik reset
$('body').on('click', '.tombol-reset-user', function() {
    $('#nama').trigger('focus')
    $('#nama,#username,#password,#level').removeClass('is-invalid');
});


//simpan user
function SimpanUser() {

    var nama = document.getElementById("nama").value;
    var uname = document.getElementById("username").value;
    var lvl = document.getElementById("level").value;
    var pwd = document.getElementById("password").value;



    $("#nama").on('input',function() {
        if ($(this).val().length > 0) {
            $('#nama').removeClass('is-invalid');
        }
    });
    $("#username").on('input',function() {
        if ($(this).val().length > 0) {
            $('#username').removeClass('is-invalid');
        }
    });
    $("#level").on('input', function(e) {
        if ($(this).val().length > 0) {
            $('#level').removeClass('is-invalid');
        }
    });


    if (nama == '' && uname == '' && lvl == '' && pwd == '') {
        $('#nama,#username,#level,#password').addClass('is-invalid');
        return false;
    } else if (nama == '') {
        $('#nama').addClass('is-invalid');
        return false;
    } else if (uname == '') {
        $('#username').addClass('is-invalid');
        return false;
    } else if (lvl == '') {
        $('#level').addClass('is-invalid');
        return false;
    } else if (pwd == ''){
        $('#password').addClass('is-invalid');
        return false;
    }

    var formInput = $('.form-user').serialize();
    $.ajax({
        type: 'POST',
        url: "user/store",
        data: formInput,
        success: function(data) {
            if (data == 'must_unique') {
                toastr.warning('Username sudah ada yang sama')
                $('#username').trigger('focus');

            } else {
                Swal.fire(
                    'Berhasil',
                    'User Baru Berhasil disimpan',
                    'success'
                )
                $('#table_user').DataTable().ajax.reload();
                document.getElementById("nama").value = "";
                document.getElementById("username").value = "";
                document.getElementById("level").value = "";
                document.getElementById("password").value = "";
                $('#nama').trigger('focus');
            }
        },
        error: function() {
            toastr.error('Customer Gagal Disimpan')
            $('#table_user').DataTable().ajax.reload(null, false);
        }

    });

    $('#nama').trigger('focus')
    
}


  //Menampilakan modal detail data customer
  $('body').on('click', '.btn-detail-user', function() {

    const idCustomer = $(this).attr('value');
    $.ajax({
      url: "user/show/" + idCustomer,
      type: "GET",
      dataType: "JSON",
      success: function(data) {
        console.log(data);
        $('[name="id_cst_detail"]').val(data.id);
        $('[name="id_cst_hapus"]').val(data.id);
        $('[name="nama_customer_detail"]').val(data.name);
        $('[name="username_detail"]').val(data.username);
        $('[name="level_detail"]').val(data.level);

        $('#modal_detail_customer').modal('show');

        $("#nama_customer_detail").attr("readonly", true);
        $("#username_detail").attr("readonly", true);
        $("#level_detail").attr("readonly", true);
        $("#password_dummy").attr("readonly", true);

        document.getElementById('update_customer').style.display = 'none';
        document.getElementById('form_ganti_password').style.display = 'none';
        document.getElementById('btn_update_password').style.display = 'none';
        
      }
    })
  });

  //klik tombol edit detail user
  $('body').on('click', '#edit_customer', function() {

    $("#nama_customer_detail").attr("readonly", false);
    $("#level_detail").attr("readonly", false);

    document.getElementById('edit_customer').style.display = 'none';
    document.getElementById('update_customer').style.display = '';
    document.getElementById('btn_update_password').style.display = 'none';
    document.getElementById('btn_ganti_password').style.display = 'none';
});

//klik tombol close detail modal
$('body').on('click', '#close_modal_detail_customer', function() {

  document.getElementById('update_customer').style.display = 'none';
  document.getElementById('edit_customer').style.display = '';

  $("#nama_customer_detail").attr("readonly", true);
  $("#username_detail").attr("readonly", true);
  $("#level_detail").attr("readonly", true);

  $('#nama_customer_detail,#username_detail,#level_detail').removeClass('is-invalid');
  document.getElementById('form_ganti_password').style.display = 'none';
  document.getElementById('btn_update_password').style.display = 'none';
  document.getElementById('btn_ganti_password').style.display = '';

  document.getElementById("password_baru").value = "";
  document.getElementById("konfirmasi_password_baru").value = "";


  $('#password_baru,#konfirmasi_password_baru').removeClass('is-invalid');

  $('#btn_update_password').prop('disabled', false);

});


//klik tombol ganti password
$('body').on('click', '#btn_ganti_password', function() {

  $('#password_baru').trigger('focus');
  document.getElementById('form_ganti_password').style.display = '';
  document.getElementById('btn_ganti_password').style.display = 'none';
  document.getElementById('btn_update_password').style.display = '';
  document.getElementById('edit_customer').style.display = 'none';
  document.getElementById('update_customer').style.display = 'none';



});


//validasi ganti password
$('#password_baru').on('input', function() {
  var password_baru = $(this).val();
  validatePasswordBaru(password_baru);
});

function validatePasswordBaru(password_baru) {
  var isValid = /^(?=.*\d)(?=.*[a-zA-Z]).{8,}$/.test(password_baru);

  if (isValid) {
    $('#password_baru').removeClass('is-invalid');
    $('#btn_update_password').prop('disabled', false);
  } else {
    $('#password_baru').addClass('is-invalid');
    $('#btn_update_password').prop('disabled', true);
  }
}



//update password
function UpdatePassword() {

  var pass_new      = document.getElementById('password_baru').value;
  var conf_pass_new = document.getElementById('konfirmasi_password_baru').value;

  if (pass_new == '')
  {
    $('#password_baru').addClass('is-invalid');
    return false;
  } else {
    $("#password_baru").on('input',function() {
      if ($(this).val().length > 0) {
          $('#password_baru').removeClass('is-invalid');
      }
  });
  }

  if(conf_pass_new == '')
  {
    $('#konfirmasi_password_baru').addClass('is-invalid');
    return false;
  } 
  else 
  {
    $("#konfirmasi_password_baru").on('input',function() {
      if ($(this).val().length > 0) {
          $('#konfirmasi_password_baru').removeClass('is-invalid');
      }
  });
  }

  var formInput = $('.form-edit-customer').serialize();
    $.ajax({
        type: 'POST',
        url: "user/update_password",
        data: formInput,
        success: function(data) {
            if (data == 'not match') {
              Swal.fire(
                'Gagal',
                'Password Konfirmasi tidak sama',
                'error'
            )
                $('#konfirmasi_password_baru').addClass('is-invalid');
                $('#konfirmasi_password_baru_feedback').text('Password Konfirmasi tidak sama dengan Password Baru'); // Menampilkan pesan validasi
                
            } else {
                Swal.fire(
                    'Berhasil',
                    'Password Baru Berhasil diupdate',
                    'success'
                )

                $('#table_user').DataTable().ajax.reload();
                document.getElementById("password_baru").value = "";
                document.getElementById("konfirmasi_password_baru").value = "";
                $('#konfirmasi_password_baru').removeClass('is-invalid');
                $('#konfirmasi_password').removeClass('is-invalid');
                $('#modal_detail_customer').modal('hide');
         
            }
        },
        error: function() {
          Swal.fire(
            'Gagal',
            'Password Gagal di update',
            'error'
        )
        }

    });
}


//update user
function UpdateUser() {
  var formInput = $('.form-edit-customer').serialize();
  var nama_detail = document.getElementById("nama_customer_detail").value;
  var level_detail = document.getElementById("level_detail").value;
  

   if (nama_detail == '') {
    $('#nama_customer_detail').addClass('is-invalid');
    $('#nama_customer_detail').trigger('focus');
      }

     if (level_detail == '') {
        $('#level_detail').addClass('is-invalid');
        $('#level_detail').trigger('focus');
    }

    $("#nama_customer_detail").on('input', function(e) {
      if ($(this).val().length > 0) {
          $('#nama_customer_detail').removeClass('is-invalid');
      }
  });

  $("#level_detail").on('input', function(e) {
    if ($(this).val().length > 0) {
        $('#level_detail').removeClass('is-invalid');
    }
  });


  $.ajax({
    type: 'POST',
    url: "user/update_user",
    data: formInput,
    success: function(data) {
    
            Swal.fire(
                'Berhasil',
                'Customer Berhasil diupdate',
                'success'
            )

            $('#table_user').DataTable().ajax.reload();
            $('#modal_detail_customer').modal('hide');
            $('#nama_customer_detail').attr("readonly", true);
            $('#level_detail').attr("readonly", true);  
            document.getElementById('edit_customer').style.display = '';
            document.getElementById('update_customer').style.display = 'none';
        
    },
    error: function() {
      Swal.fire(
        'Gagal',
        'Password Gagal di update',
        'error'
    )
    }

});


  



}
  