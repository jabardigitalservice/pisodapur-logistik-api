<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>E-Mail PIKOBAR</title>
</head>
<body>
    <div>
        <div>Kepada Yth</div>
        <div>{{ $data->applicant_fullname }}</div>
    </div>
    <div>
        <div>{{ $data->agency_name }}</div>
    </div>
    <div>
        @foreach ($texts as $text)
        <p>{!! $text !!}</p>
        @endforeach
        @foreach ($notes as $note)
        <p>{!! $text !!}</p>
        @endforeach
    </div>
    <div>
        <div>Salam hormat kami,</div>
        <div>{{ $from }}</div>
        <div>Whatsapp Admin Logistik Vaksin Pikobar: {{ $hotLine }}</div>
        <div>Email: {{ $email }}</div>
    </div>
</body>
</html>
