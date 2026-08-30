//datatables    
$(function() {
    
    $("#table_testing").DataTable({
        "responsive": true,
        "lengthChange": true,
        "autoWidth": false,
        "ajax": {
            "url": "testing/json",
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
              data: 'tgl_testing',
              name: 'tgl_testing'
            },
            {
                data: 'sn',
                name: 'sn'
            },
            {
                data: 'so',
                name: 'so'
            },
            {
              data: 'id_testing',
              name: 'id_testing',
            "render" : function(data, type, row) {
                return '<center><a data-toggle="tooltip" data-placement="top" value="' + data + '" class="btn-detail-testing" title="Detail" data-target="#modal_detail_testing"><i class="fa fa-edit" style="font-size: 22px; cursor:pointer;"></i></a></center>';
    
              }
            },
          ],
    })
});

//auto focus ketika click button tambah
$('#modal_tambah_testing').on('shown.bs.modal', function() {
    $('#sn').trigger('focus')
})

//auto focus ketika click reset
$('body').on('click', '.tombol-reset', function() {
    $('#sn').trigger('focus')
    let tmp = new Date(Date.now());
    let dateInputFormatted = tmp.toISOString().split('T')[0];
    console.log(dateInputFormatted);
    document.getElementById("tanggal").value = dateInputFormatted;


});

//hapus semua input ketika modal di close
$(document).ready(function() {
    $("#modal_tambah_testing").on("hidden.bs.modal", function() {
        document.getElementById("sn").value = "";
        document.getElementById("so").value = "";
        document.getElementById("rt_1").value = "";
        document.getElementById("rt_2").value = "";
        document.getElementById("rt_3").value = "";
        document.getElementById("ft_1").value = "";
        document.getElementById("ft_2").value = "";
        document.getElementById("ft_3").value = "";

        $('#tanggal,#sn,#so,#rt_1,#rt_2,#rt_3,#ft_1,#ft_2,#ft_3').removeClass('is-invalid');

        let tmp = new Date(Date.now());
        let dateInputFormatted = tmp.toISOString().split('T')[0];
        console.log(dateInputFormatted);
        document.getElementById("tanggal").value = dateInputFormatted;


    });
});

//fucntion simpan testing
function SimpanTesting() {

  var tgl = document.getElementById("tanggal").value;
  var sn = document.getElementById("sn").value;
  var so = document.getElementById("so").value;
  var id_cst = document.getElementById("id_cst").value;




  $("#tanggal").on('keyup', function(e) {
      if ($(this).val().length > 0) {
          $('#tanggal').removeClass('is-invalid');
      }
  });
  $("#sn").on('keyup', function(e) {
      if ($(this).val().length > 0) {
          $('#sn').removeClass('is-invalid');
      }
  });
  $("#so").on('keyup', function(e) {
      if ($(this).val().length > 0) {
          $('#so').removeClass('is-invalid');
      }
  });
  $("#id_cst").change(function(e) {
    if ($(this).val().length > 0) {
        $('#id_cst').removeClass('is-invalid');
    }
});



  if (tgl == '') {
      $('#tanggal').addClass('is-invalid');
      $('#tanggal').trigger('focus');
      return false;
  }
  if (sn == '') {
    $('#sn').addClass('is-invalid');
    $('#sn').trigger('focus');
    return false;
} 
  if (id_cst == '') {
    $('#id_cst').addClass('is-invalid');
    $('#id_cst').trigger('focus');
    return false;
  } if (so == '') {
      $('#so').addClass('is-invalid');
      $('#so').trigger('focus');
      return false;
  } 


  // var formInput = $('.form-aktifitas').serialize();
  var formInput = new FormData($('.form-testing')[0]);
  $.ajax({
      type: 'POST',
      url: "testing/store",
      data: formInput,
      processData: false,
      contentType: false,
      headers: {
          'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
      },
      success: function() {

        Swal.fire(
            'Berhasil',
            'Testing Berhasil disimpan',
            'success'
        )
          $('#table_testing').DataTable().ajax.reload(null, false);
          document.getElementById("sn").value = "";
          document.getElementById("so").value = "";
          document.getElementById("id_cst").value = "";
          document.getElementById("rt_1").value = "";
          document.getElementById("rt_2").value = "";
          document.getElementById("rt_3").value = "";
          document.getElementById("ft_1").value = "";
          document.getElementById("ft_2").value = "";
          document.getElementById("ft_3").value = "";
          $('#sn').trigger('focus')
          let tmp = new Date(Date.now());
          let dateInputFormatted = tmp.toISOString().split('T')[0];
          console.log(dateInputFormatted);
          document.getElementById("tanggal").value = dateInputFormatted;
      },
      error: function() {
        
        Swal.fire(
            'Gagal',
            'Testing Gagal disimpan',
            'error'
        )
          $('#table_testing').DataTable().ajax.reload(null, false);
      }

  });
  
  $('#sn').trigger('focus')

}


 //Menampilakan / show modal detail data testing
 $('body').on('click', '.btn-detail-testing', function() {
    const id_tstg = $(this).attr('value');
    $.ajax({
        url: "testing/show/" + id_tstg,
        type: "GET",
        dataType: "JSON",
        success: function(response) {
            console.log(response);
            const data = response.testing;
            const userLevel = response.userLevel;
            $('[name="id_testing_update"]').val(data.id_testing);
            $('[name="id_testing_detail"]').val(data.id_testing);
            $('[name="id_testing_hapus"]').val(data.id_testing);
            $('[name="tanggal_detail"]').val(data.tgl_testing);
            $('[name="so_detail"]').val(data.so);
            $('[name="sn_detail"]').val(data.sn);
            if (data.rutin_test_1 != 'NULL') {
                $('#rt1Detail').html(data.rutin_test_1 + ' <i class="fas fa-arrow-right"></i> <a href="storage/file_qc/' + data.rutin_test_1 + '" download>Download</a>');
            } else {
                $('#rt1Detail').text('');
            }
            if (data.rutin_test_2 != 'NULL') {
                $('#rt2Detail').html(data.rutin_test_2 + ' <i class="fas fa-arrow-right"></i> <a href="storage/file_qc/' + data.rutin_test_2 + '" download>Download</a>');
            } else {
                $('#rt2Detail').text('');
            }
            if (data.rutin_test_3 != 'NULL') {
                $('#rt3Detail').html(data.rutin_test_3 + ' <i class="fas fa-arrow-right"></i> <a href="storage/file_qc/' + data.rutin_test_3 + '" download>Download</a>');
            } else {
                $('#rt3Detail').text('');
            }
            if (data.full_test_1 != 'NULL') {
                $('#ft1Detail').html(data.full_test_1 + ' <i class="fas fa-arrow-right"></i> <a href="storage/file_qc/' + data.full_test_1 + '" download>Download</a>');
            } else {
                $('#ft1Detail').text('');
            }
            if (data.full_test_2 != 'NULL') {
                $('#ft2Detail').html(data.full_test_2 + ' <i class="fas fa-arrow-right"></i> <a href="storage/file_qc/' + data.full_test_2 + '" download>Download</a>');
            } else {
                $('#ft2Detail').text('');
            }
            if (data.full_test_3 != 'NULL') {
                $('#ft3Detail').html(data.full_test_3 + ' <i class="fas fa-arrow-right"></i> <a href="storage/file_qc/' + data.full_test_3 + '" download>Download</a>');
            } else {
                $('#ft3Detail').text('');
            }

            $('[name="id_cst_detail"]').val(data.id_user).trigger('change');
            $('#modal_detail_testing').modal('show');

            $("#tanggal_detail").attr("readonly", true);
            $("#so_detail").attr("readonly", true);
            $("#sn_detail").attr("readonly", true);
            $("#id_cst_detail").attr("readonly", true);

            document.getElementById('update_testing').style.display = 'none';
            document.getElementById('show_hide_rt_1').style.display = 'none';
            document.getElementById('show_hide_rt_2').style.display = 'none';
            document.getElementById('show_hide_rt_3').style.display = 'none';
            document.getElementById('show_hide_ft_1').style.display = 'none';
            document.getElementById('show_hide_ft_2').style.display = 'none';
            document.getElementById('show_hide_ft_3').style.display = 'none';

            if (userLevel === 'user') {
                document.getElementById('edit_testing').style.display = 'none';
                document.getElementById('hapus_testing').style.display = 'none';
            } else {
                document.getElementById('edit_testing').style.display = '';
            }
           

        }


    })
});

//klik tombol edit di detail testing
$('body').on('click', '#edit_testing', function() {

    document.getElementById('update_testing').style.display = '';
    document.getElementById('show_hide_rt_1').style.display = '';
    document.getElementById('show_hide_rt_2').style.display = '';
    document.getElementById('show_hide_rt_3').style.display = '';
    document.getElementById('show_hide_ft_1').style.display = '';
    document.getElementById('show_hide_ft_2').style.display = '';
    document.getElementById('show_hide_ft_3').style.display = '';
    document.getElementById('edit_testing').style.display = 'none';

    $("#tanggal_detail").attr("readonly", false);
    $("#so_detail").attr("readonly", false);
    $("#sn_detail").attr("readonly", false);
    $("#id_cst_detail").attr("readonly", false);

});


//close modal testing tanpa update
$('body').on('click', '#close_modal_detail_testing', function() {

    document.getElementById('update_testing').style.display = 'none';
    document.getElementById('show_hide_rt_1').style.display = 'none';
    document.getElementById('show_hide_rt_2').style.display = 'none';
    document.getElementById('show_hide_rt_3').style.display = 'none';
    document.getElementById('show_hide_ft_1').style.display = 'none';
    document.getElementById('show_hide_ft_2').style.display = 'none';
    document.getElementById('show_hide_ft_3').style.display = 'none';
    document.getElementById('edit_testing').style.display = '';

    $("#tanggal_detail").attr("readonly", true);
    $("#so_detail").attr("readonly", true);
    $("#sn_detail").attr("readonly", true);
    $("#id_user_detail").attr("readonly", true);

    $('#tanggal_detail,#sn_detail,#so_detail,#rt_1_update,#rt_2_update,#rt_3_update,#ft_1_update,#ft_2_update,#ft_3_update').removeClass('is-invalid');

});


function UpdateTesting() {

    var tgl_d = document.getElementById("tanggal_detail").value;
    var sn_d = document.getElementById("sn_detail").value;
    var so_d = document.getElementById("so_detail").value;
    var id_cst_d = document.getElementById("id_cst_detail").value;
  
    $("#tanggal_detail").on('keyup', function(e) {
        if ($(this).val().length > 0) {
            $('#tanggal_detail').removeClass('is-invalid');
        }
    });
    $("#sn_detail").on('keyup', function(e) {
        if ($(this).val().length > 0) {
            $('#sn_detail').removeClass('is-invalid');
        }
    });
    $("#so_detail").on('keyup', function(e) {
        if ($(this).val().length > 0) {
            $('#so_detail').removeClass('is-invalid');
        }
    });
    $("#id_cst_detail").change(function(e) {
      if ($(this).val().length > 0) {
          $('#id_cst_detail').removeClass('is-invalid');
      }
  });
  

    if (tgl_d == '') {
        $('#tanggal_detail').addClass('is-invalid');
        $('#tanggal_detail').trigger('focus');
        return false;
    }
    if (sn_d == '') {
      $('#sn_detail').addClass('is-invalid');
      $('#sn_detail').trigger('focus');
      return false;
  } 
    if (id_cst_d == '') {
      $('#id_cst_detail').addClass('is-invalid');
      $('#id_cst_detail').trigger('focus');
      return false;
    } if (so_d == '') {
        $('#so_detail').addClass('is-invalid');
        $('#so_detail').trigger('focus');
        return false;
    } 
  
    // var formInput = $('.form-aktifitas').serialize();
    var formInput_edit = new FormData($('.form-edit-testing')[0]);
    $.ajax({
        type: 'POST',
        url: "testing/update",
        data: formInput_edit,
        processData: false,
        contentType: false,
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        success: function() {
            $('#modal_detail_testing').modal('hide');
            Swal.fire(
                'Berhasil',
                'Testing Berhasil diupdate',
                'success'
            )
            $('#table_testing').DataTable().ajax.reload(null, false);
            document.getElementById('update_testing').style.display = 'none';
            document.getElementById('edit_testing').style.display = '';
        },
        error: function() {
          
            Swal.fire(
                'Gagal',
                'Testing gagal diupdate',
                'error'
            )
            $('#table_testing').DataTable().ajax.reload(null, false);
        }
  
    });
    
  
  }

  // hapus testing
  function HapusTesting() {

    Swal.fire({
        title: 'Hapus Testing',
        text: "Yakin Ingin Hapus Testing ?",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Yes'
    }).then((result) => {
        if (result.isConfirmed) {

            var id_tstg_hapus = document.getElementById("id_testing_hapus").value;
            var formHapus = $('.form-hapus-testing').serialize();
            $.ajax({
                type: 'POST',
                url: "testing/destroy",
                data: formHapus,
                success: function() {
                    Swal.fire(
                        'Berhasil',
                        'Testing Berhasil dihapus',
                        'success',
                        '3000'
                    )
                    $('#table_testing').DataTable().ajax.reload(null, false);
                    $('#modal_detail_testing').modal('hide');
                },
                error: function() {
                    Swal.fire({
                        icon: 'error',
                        title: 'gagal!',
                        text: 'Testing Gagal dihapus'
                    })
                    $('#table_testing').DataTable().ajax.reload(null, false);
                }
            });


        }
    })

}

  


