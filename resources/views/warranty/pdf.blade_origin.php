<!DOCTYPE html>
<html lang="id">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Digital Warranty</title>

    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <link rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {

            font-family: 'Poppins', sans-serif;

            background:
                radial-gradient(circle at top left, #2B7FFF 0%, transparent 35%),
                radial-gradient(circle at bottom right, #0B2A4A 0%, transparent 40%),
                linear-gradient(135deg, #071B2D, #0F4C81);

            min-height: 100vh;

            display: flex;

            justify-content: center;

            align-items: center;

            overflow-x: hidden;

            padding: 40px;

        }

        /*=============================
        CARD
=============================*/

        .warranty-card {

            position: relative;

            width: 860px;

            min-height: 620px;

            height: 620px;

            border-radius: 30px;

            overflow: hidden;

            color: #fff;

            background:
                linear-gradient(135deg,
                    rgba(255, 255, 255, .10),
                    rgba(255, 255, 255, .02));

            backdrop-filter: blur(12px);

            border: 1px solid rgba(255, 255, 255, .15);

            box-shadow:

                0 35px 70px rgba(0, 0, 0, .45),

                inset 0 1px 1px rgba(255, 255, 255, .25);

        }

        /*=============================
      BACKGROUND EFFECT
=============================*/

        .warranty-card::before {

            content: "";

            position: absolute;

            width: 420px;

            height: 420px;

            border-radius: 50%;

            background: rgba(255, 255, 255, .05);

            top: -160px;

            right: -120px;

        }

        .warranty-card::after {

            content: "";

            position: absolute;

            width: 320px;

            height: 320px;

            border-radius: 50%;

            background: rgba(255, 255, 255, .04);

            bottom: -130px;

            left: -80px;

        }

        /* Watermark */

        .watermark {

            position: absolute;

            right: -20px;

            bottom: -70px;

            font-size: 330px;

            color: rgba(255, 255, 255, .04);

            z-index: 1;

        }

        /*=============================
       CONTENT
=============================*/

        .card-content {

            position: relative;
            z-index: 5;

            padding: 40px;

            display: flex;

            flex-direction: column;

            height: 100%;

        }

        /*=============================
        HEADER
=============================*/

        .header {

            display: flex;

            justify-content: space-between;

            align-items: flex-start;

        }

        .logo {

            display: flex;

            align-items: center;

            gap: 15px;

        }

        .logo-circle {

            width: 70px;

            height: 70px;

            border-radius: 50%;

            background: rgba(255, 255, 255, .18);

            display: flex;

            justify-content: center;

            align-items: center;

            font-size: 28px;

        }

        .logo-text {

            line-height: 1.3;

        }

        .logo-text h2 {

            font-size: 22px;

            font-weight: 700;

            letter-spacing: 1px;

        }

        .logo-text span {

            font-size: 13px;

            opacity: .75;

        }

        .code {

            text-align: right;

        }

        .code small {

            font-size: 13px;

            opacity: .75;

        }

        .code h1 {

            margin-top: 8px;

            font-size: 34px;

            font-weight: 800;

            letter-spacing: 4px;

        }

        /*=============================
      CUSTOMER
=============================*/

        .customer {

            margin-top: 40px;

        }

        .customer small {

            opacity: .75;

            font-size: 14px;

        }

        .customer h3 {

            margin-top: 8px;

            font-size: 34px;

            font-weight: 700;

        }

        /*=============================
      INFO GRID
=============================*/

        .info-grid {

            margin-top: 25px;

            display: grid;

            grid-template-columns: repeat(2, 1fr);

            gap: 25px 50px;

        }

        .info {

            border-bottom: 1px solid rgba(255, 255, 255, .15);

            padding-bottom: 10px;

        }

        .info label {

            display: block;

            font-size: 13px;

            opacity: .70;

        }

        .info h4 {

            margin-top: 8px;

            font-size: 19px;

            font-weight: 600;

        }

        /*=============================
        FOOTER
=============================*/

        .footer {

            position: absolute;

            left: 40px;
            right: 40px;
            bottom: 25px;

            display: flex;

            justify-content: space-between;

            align-items: flex-end;
            /* sebelumnya center */

        }

        .status {

            padding: 12px 24px;

            border-radius: 40px;

            background: #18B45B;

            font-weight: 600;

            letter-spacing: .5px;

            box-shadow: 0 8px 25px rgba(0, 0, 0, .25);

        }

        .status.expired {

            background: #E53935;

        }

        .download {

            text-decoration: none;

            color: #0F4C81;

            background: #fff;

            padding: 14px 24px;

            border-radius: 40px;

            font-weight: 600;

            transition: .3s;

        }

        .download:hover {

            transform: translateY(-3px);

        }

        /*=============================
      MOBILE
=============================*/

        @media(max-width:900px) {

            body {

                padding: 20px;

            }

            .warranty-card {

                width: 100%;

                height: auto;

            }

            .card-content {

                padding: 30px;

            }

            .code {

                margin-top: 15px;

            }

            .code h1 {

                font-size: 26px;

            }

            .customer h3 {

                font-size: 26px;

            }

            .info-grid {

                grid-template-columns: 1fr;

            }

            .footer {

                position: relative;

                left: 0;

                right: 0;

                bottom: 0;

                margin-top: 40px;

                flex-direction: column;

                gap: 20px;

            }

        }
    </style>

</head>

<body>

    <div class="warranty-card">

        <!-- Watermark -->
        <div class="watermark">
            <i class="fa-solid fa-shield-halved"></i>
        </div>

        <div class="card-content">

            <!-- =========================
             HEADER
        ========================== -->
            <div class="header">

                <div class="logo">

                    <div class="logo-circle">
                        <i class="fa-solid fa-shield-halved"></i>
                    </div>

                    <div class="logo-text">

                        <h2>DIGITAL WARRANTY</h2>

                        <span>
                            Premium Digital Warranty Card
                        </span>

                    </div>

                </div>

                <div class="code">

                    <small>Warranty Code</small>

                    <h1>{{ $warranty->kode_warranty }}</h1>

                </div>

            </div>

            <!-- =========================
             CUSTOMER
        ========================== -->

            <div class="customer">

                <small>Customer Name</small>

                <h3>

                    {{ $warranty->customer->nama_customer }}

                </h3>

            </div>

            <!-- =========================
                INFORMATION
        ========================== -->

            <div class="info-grid">

                <div class="info">

                    <label>
                        <i class="fa-solid fa-layer-group"></i>
                        Product
                    </label>

                    <h4>

                        {{ $warranty->product->nama_produk }}

                    </h4>

                </div>

                <div class="info">

                    <label>

                        <i class="fa-solid fa-car"></i>

                        Vehicle

                    </label>

                    <h4>

                        {{ $warranty->merk_mobil }}

                        {{ $warranty->tipe_mobil }}

                    </h4>

                </div>

                <div class="info">

                    <label>

                        <i class="fa-solid fa-id-card"></i>

                        Plate Number

                    </label>

                    <h4>

                        {{ $warranty->no_polisi }}

                    </h4>

                </div>

                <div class="info">

                    <label>

                        <i class="fa-solid fa-calendar-days"></i>

                        Installation

                    </label>

                    <h4>

                        {{ date('d M Y',strtotime($warranty->tanggal_pasang)) }}

                    </h4>

                </div>

                <div class="info">

                    <label>

                        <i class="fa-solid fa-hourglass-end"></i>

                        Valid Until

                    </label>

                    <h4>

                        {{ date('d M Y',strtotime($warranty->tanggal_expired)) }}

                    </h4>

                </div>

                <!-- <div class="info">

                    <label>

                        <i class="fa-solid fa-user-gear"></i>

                        Installer

                    </label>

                    <h4>

                        {{ $warranty->installer }}

                    </h4>

                </div> -->

            </div>

            <!-- =========================
                FOOTER
        ========================== -->

            <div class="footer">

                @if(strtotime($warranty->tanggal_expired) >= strtotime(date('Y-m-d')))

                <div class="status">

                    <i class="fa-solid fa-circle-check"></i>

                    ACTIVE WARRANTY

                </div>

                @else

                <div class="status expired">

                    <i class="fa-solid fa-circle-xmark"></i>

                    EXPIRED WARRANTY

                </div>

                @endif

                <a href="#" class="download">

                    <i class="fa-solid fa-download"></i>

                    Download PDF

                </a>

            </div>

        </div>

    </div>

</body>

</html>