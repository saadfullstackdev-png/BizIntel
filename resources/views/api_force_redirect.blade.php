<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
</head>
<body>
</body>
<script type="text/javascript">
    $(document).ready(function (){
        setTimeout(() => {
            window.location = "{!! $url !!}"; // will result in error message if app not installed
        }, 1000);
    });
</script>
</html>