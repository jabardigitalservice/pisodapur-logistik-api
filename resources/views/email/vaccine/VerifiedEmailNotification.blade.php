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
        </style>
    </head>
    <body>

        <div>
            <div>Kepada Yth. {{ $data->applicant_fullname }}</div>
        </div>
        <div>
            <div>{{ $data->agency_name }}</div>
        </div>
        <div>
            @foreach ($texts as $text)
            {!! $text !!}<br>
            @endforeach

            @if ($tables)
            <table id="customers">
                <tr>
                    <th>No</th>
                    <th>Nama Barang</th>
                    <th>Jumlah</th>
                    <th>Satuan</th>
                    <th>Status</th>
                    <th>Realisasi</th>
                    <th>Jumlah</th>
                    <th>Satuan</th>
                </tr>

                @foreach ($tables as $key => $table)
                <tr>
                    <td>{{ ($key + 1) }}</td>
                    <td>{{ optional($table->vaccineProduct)->name }}</td>
                    <td>{{ $table->quantity }}</td>
                    <td>{{ $table->unit }}</td>
                    <td>{{ $table->finalized_status }}</td>
                    <td>{{ $table->finalized_product_name }}</td>
                    <td>{{ $table->finalized_quantity }}</td>
                    <td>{{ $table->finalized_UoM }}</td>
                </tr>
                @endforeach

            </table>
            @endif
            <br>

            @foreach ($notes as $note)
            {!! $note !!}<br>
            @endforeach
        </div>
        <br>
        <div>
            <div><b>Salam hormat kami,</b></div>
            <div>{{ $from }}</div>
            <div>Whatsapp Admin Logistik Vaksin Pikobar: {{ $hotLine }}</div>
            <div>Email: {{ $email }}</div>
        </div>

    </body>
</html>
