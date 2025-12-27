<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>
</head>
<body>

<div class="container text-center">
    <p>Hi {{$user->name ?? ""}}, </p>
    <p>Your One Time Passcode Is <b>{{$password}}</b></p>
    <p>Regards,</p>
    <p>3D Lifestyle</p>
</div>

</body>
</html>