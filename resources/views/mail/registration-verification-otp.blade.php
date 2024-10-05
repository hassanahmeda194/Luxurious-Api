<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Registration Verfication Code</title>
</head>

<body>
    <p>Dear {{ $user->name }},</p>
    <p>Your verification code is: <strong>{{ $code }}</strong></p>
    <p>Please enter this code in the app to verify your email.</p>
    <p>Thank you!</p>
</body>

</html>
