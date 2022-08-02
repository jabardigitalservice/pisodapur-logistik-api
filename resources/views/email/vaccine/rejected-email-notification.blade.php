<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta http-equiv="X-UA-Compatible" content="ie=edge">
        <title>E-Mail PIKOBAR</title>
        <style>
            #customers {
                font-family: Arial, Helvetica, sans-serif;
                border-collapse: collapse;
                width: 100%;
            }
            #customers td, #customers th {
                border: 1px solid #ddd;
                padding: 8px;
            }
            #customers tr:nth-child(even){background-color: #f2f2f2;}
            #customers tr:hover {background-color: #ddd;}
            #customers th {
                padding-top: 12px;
                padding-bottom: 12px;
                text-align: left;
                background-color: #04AA6D;
                color: white;
            }
            .card-box {
                float: left;
                border: 1px border rgb(56, 56, 56);
                background-color: rgb(234, 234, 234);
                border-radius: 15px;
                padding: 15px;
                width: 80%;
                margin: 5px;
            }
            .card-box-col-2 {
                float: left;
                border: 1px border rgb(56, 56, 56);
                background-color: rgb(234, 234, 234);
                border-radius: 15px;
                padding: 15px;
                width: 40%;
                margin: 5px;
            }
            .col-12 {
                float: left;
                border-radius: 15px;
                padding: 15px;
                width: 100%;
            }
            /* Clear floats after the columns */
            .row:after {
                content: "";
                display: table;
                clear: both;
            }
        </style>
    </head>
    <body>

        <div class="row col-12">
            <div>Kepada Yth. {{ $data->applicant_fullname }}</div>
            <div>{{ $data->agency_name }}</div>
        </div>
        <div>
            <br>
            <div class="row col-12">
                Terimakasih telah melakukan permohonan pada Aplikasi Permohonan Logistik Pikobar Vaksin. Melalui email ini, kami mengabarkan hasil verifikasi permohonan Logistik Vaksin anda sebagai berikut:
            </div>
            <br>
            <br>
            <div class="row">
                <div class="card-box-col-2">
                    ID Permohonan
                    <br>
                    <b>{{ $data->id }}</b>
                </div>
                <div class="card-box-col-2">
                    Status Permohonan
                    <br>
                    <b style="color: red">Ditolak</b>
                </div>
            </div>
            <br>
            <div class="row card-box">
                Alasan Penolakan<br>
                {!! $data->note !!}
                <br>
                @foreach ($notes as $note)
                {!! $note !!}<br>
                @endforeach
            </div>
            <br>
            <br>
            <br>
            <div class="row col-12">
                <p>Mohon Maaf atas ketidaknyamanan ini. Mohon ajukan permohonan kembali jika masih membutuhkan logistik untuk instansi Anda.</p>
            </div>
            <br>
        </div>
        <br>
        <div class="row col-12">
            <div><b>Salam hormat kami,</b></div>
            <div>{{ $from }}</div>
            <div>Whatsapp Admin Logistik Vaksin Pikobar: {{ $hotLine }}</div>
            <div>Email: {{ $email }}</div>
        </div>

    </body>
</html>
