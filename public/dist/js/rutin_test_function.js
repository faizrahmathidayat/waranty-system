$(function() {
    
    $("#table_rutin_test").DataTable({
        "responsive": true,
        "lengthChange": true,
        "autoWidth": false,
        "ajax": {
            "url": "rutin_test/json",
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
                data: 'rutin_test_1',
                name: 'rutin_test_1',
                render: function(data, type, row) {
                    if (data != 'NULL') {
                        var folder = 'storage/file_qc'; // Ganti dengan folder yang sesuai
                        var url = folder + '/' + data;
                        return ' <a href="' + url + '" download>Download</a>';
                    } else {
                        return '';
                    }
                }
            },
            {
                data: 'rutin_test_2',
                name: 'rutin_test_2',
                render: function(data, type, row) {
                    if (data != 'NULL') {
                        var folder = 'storage/file_qc'; // Ganti dengan folder yang sesuai
                        var url = folder + '/' + data;
                        return ' <a href="' + url + '" download>Download</a>';
                    } else {
                        return '';
                    }
                }
            },
            {
                data: 'rutin_test_3',
                name: 'rutin_test_3',
                render: function(data, type, row) {
                    if (data != 'NULL') {
                        var folder = 'storage/file_qc'; // Ganti dengan folder yang sesuai
                        var url = folder + '/' + data;
                        return ' <a href="' + url + '" download>Download</a>';
                    } else {
                        return '';
                    }
                }
            },
          ],
    })
});