<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Email Verification</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
        }
        .email-container {
            max-width: 600px;
            margin: 50px auto;
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        .header, .footer {
            text-align: center;
            padding: 10px 0;
        }
        .header {
            border-bottom: 1px solid #ddd;
        }
        .footer {
            border-top: 1px solid #ddd;
            font-size: 12px;
            color: #aaa;
            margin-top: 20px;
        }
        .content {
            padding: 20px;
            text-align: center;
        }
        .btn {
            display: inline-block;
            padding: 10px 20px;
            font-size: 16px;
            color: #fff;
            background-color: #333;
            text-decoration: none;
            border-radius: 5px;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="email-container">
        <div class="header">
            <h1 style="margin: 0; font-size: 24px; color: #333;">Pawarta</h1>
        </div>
        <div class="content">
            <h2 style="font-size: 20px; color: #333;">Verifikasi Email Anda</h2>
            <p style="font-size: 16px; color: #555;">Klik tombol di bawah untuk memverifikasi User Anda.</p>
            <a href="{{ url('/api/verify-email?email=' . urlencode($user->email)) }}" style="display: inline-block; padding: 10px 20px; font-size: 16px; color: #fff; background-color: #333; text-decoration: none; border-radius: 5px;">
                Verifikasi Email
            </a>
            <p style="font-size: 16px; color: #555; margin-top: 20px;">Terimakasih,<br>Pawarta</p>
        </div>
        <div class="footer">
            © 2024 Pawarta. All rights reserved.
        </div>
    </div>
</body>
</html>
