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
        <div>{{ $applicantName }}</div>
    </div>
    <div>
        <div>dari</div>
        <div>{{ $agency }}</div>
    </div>
    <div>
        @foreach ($texts as $text)
        <p>{{ $text }}</p>
        @endforeach
        @foreach ($notes as $note)
        <p>{{ $note }}</p>
        @endforeach
    </div>
    <div>
        <div>Salam,</div>
        <div>Admin Logistik Pikobar</div>
        <div>Hotline Pikobar: {{ $hotLine }}</div>
        <div>Email: digital.service@jabarprov.go.id</div>
    </div>
</body>
</html>