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
        @foreach ($texts as $text)
        <p>{{ $text }}</p>
        @endforeach
        <h3>{{ $token }}</h3>
        @foreach ($notes as $note)
        <p>{{ $note }}</p>
        @endforeach
    </div>
    <div>
        <div>Salam,</div>
        <div>{{ $from }}</div>
        <div>Hotline Pikobar: {{ $hotLine }}</div>
        <div>Email: digital.service@jabarprov.go.id</div>
    </div>
</body>
</html>