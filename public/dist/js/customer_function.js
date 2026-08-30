
 //===fuction js customer====
 
 //datatables    
$(function() {
    
    $("#table_customer").DataTable({
        "responsive": true,
        "lengthChange": true,
        "autoWidth": false,
        "ajax": {
            "url": "customer/json",
            "type": "GET",
          }, // memanggil route yang menampilkan data json
          columns: [
    
            {
              "render": function(data, type, row, meta) {
                return meta.row + meta.settings._iDisplayStart + 1;
              }
            },
            { // mengambil & menampilkan kolom sesuai tabel database
              data: 'NAMA_CST',
              name: 'NAMA_CST'
            },
            {
              data: 'LOKASI',
              name: 'LOKASI'
            },
            {
              data: 'ID_CST',
              name: 'ID_CST',
            "render" : function(data, type, row) {
                // return "<center><button class='btn btn-primary btn-sm btn-detail' value='data : 'ID_AKTFS',name:'ID_AKTFS''>Lihat</button></center>";
                return '<center><a data-toggle="tooltip" data-placement="top" value="' + data + '" class="btn-editCustomer" title="Detail" data-target="#modalEdit_selesai"><i class="fa fa-edit" style="font-size: 22px; cursor:pointer;"></i></a></center>';
    
              }
            },
          ],
          order: [
            [3, "DESC"]
          ],
      
          
        

    })
});


//show modal
$('#modal_tambah_customer').on('shown.bs.modal', function() {
    $('#nama_customer').trigger('focus')
})

//auto focus after klik reset
$('body').on('click', '.tombol-reset', function() {
    $('#nama_customer').trigger('focus')
});

//hapus value after close modal
$(document).ready(function() {
    $("#modal_tambah_customer").on("hidden.bs.modal", function() {
        document.getElementById("nama_customer").value = "";
        document.getElementById("lokasi").value = "";
        document.getElementById("ket_customer").value = "";
        $('#nama_customer,#lokasi,#ket_customer').removeClass('is-invalid');
    });
});


//simpan customer
function SimpanCustomer() {

    var namcst = document.getElementById("nama_customer").value;
    var loka = document.getElementById("lokasi").value;
    var ketc = document.getElementById("ket_customer").value;

    $("#nama_customer").change(function() {
        if ($(this).val().length > 0) {
            $('#nama_customer').removeClass('is-invalid');
        }
    });
    $("#lokasi").change(function() {
        if ($(this).val().length > 0) {
            $('#lokasi').removeClass('is-invalid');
        }
    });
    $("#ket_customer").on('keyup', function(e) {
        if ($(this).val().length > 0) {
            $('#ket_customer').removeClass('is-invalid');
        }
    });

    if (namcst == '' && loka == '' && ketc == '') {
        $('#nama_customer,#lokasi,#ket_customer').addClass('is-invalid');
        return false;
    } else if (namcst == '') {
        $('#nama_customer').addClass('is-invalid');
    } else if (loka == '') {
        $('#lokasi').addClass('is-invalid');
        return false;
    } else if (ketc == '') {
        $('#ket_customer').addClass('is-invalid');
        return false;
    }

    var formInput = $('.form-customer').serialize();
    $.ajax({
        type: 'POST',
        url: "customer/store",
        data: formInput,
        success: function(data) {
            if (data == 'must_unique') {
                toastr.warning('Nama Customer dan Lokasi sudah ada yang sama')
                $('#nama_customer').trigger('focus');

            } else {
                toastr.success('Customer Berhasil Disimpan')
                $('#tabel_customer').DataTable().ajax.reload();
                document.getElementById("nama_customer").value = "";
                document.getElementById("lokasi").value = "";
                document.getElementById("ket_customer").value = "";
            }
        },
        error: function() {
            toastr.error('Customer Gagal Disimpan')
            $('#example').DataTable().ajax.reload(null, false);
        }

    });
    $('#nama_customer').trigger('focus');
}
// $(function() {

// $('.tombol-simpan').click(function() {
//     toastr.error('Lorem ipsum dolor sit amet, consetetur sadipscing elitr.')
//   });

// });
//end customer
