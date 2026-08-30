<!DOCTYPE html>
<html>

<head>

    <meta charset="UTF-8">

    <title>Laporan Mobil Masuk</title>

    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 10px;
        }

        .header {
            text-align: center;
            margin-bottom: 15px;
        }

        .header h2 {
            margin: 0;
            font-size: 18px;
        }

        .header h3 {
            margin: 5px 0;
            font-size: 14px;
        }

        .periode {
            text-align: center;
            margin-bottom: 15px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
            border: 1px solid #000;
            padding: 5px;
        }

        th {
            text-align: center;
            background: #eee;
        }
    </style>

</head>

<body>

    <div class="header">

        <h2>
            WARRANTY SYSTEM
        </h2>

        <h3>
            LAPORAN MOBIL MASUK
        </h3>

    </div>


    <div class="periode">

        Periode :

        {{ $tanggal_mulai ?: '-' }}

        s/d

        {{ $tanggal_akhir ?: '-' }}

    </div>


    <table>

        <thead>

            <tr>

                <th>No</th>
                <th>Tanggal</th>
                <th>Kode Warranty</th>
                <th>No Polisi</th>
                <th>Customer</th>
                <th>Kendaraan</th>
                <th>Product</th>
                <th>Installer</th>

            </tr>

        </thead>

        <tbody>

            @foreach($data as $index => $row)

            <tr>

                <td>
                    {{ $index + 1 }}
                </td>

                <td>
                    {{ $row->tanggal_pasang }}
                </td>

                <td>
                    {{ $row->kode_warranty }}
                </td>

                <td>
                    {{ $row->no_polisi }}
                </td>

                <td>
                    {{ $row->nama_customer }}
                </td>

                <td>
                    {{ $row->merk_mobil }}
                    {{ $row->tipe_mobil }}
                </td>

                <td>
                    {{ $row->nama_produk }}
                </td>

                <td>
                    {{ $row->installer }}
                </td>

            </tr>

            @endforeach

        </tbody>

    </table>

</body>

</html>