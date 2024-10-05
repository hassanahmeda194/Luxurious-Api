<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Registration Verfication Code</title>
</head>

<body>
    <p>Hello {{ $user->name }},</p>
    <p>Your password reset OTP code is: <strong>{{ $otp }}</strong></p>
    <p>Please enter this code in the app to reset your password.</p>
    <p>This code is valid for 10 minutes.</p>
    <p>If you did not request a password reset, please ignore this email.</p>
</body>

</html>
