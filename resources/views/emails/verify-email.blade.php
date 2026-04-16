<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Подтверждение email</title>
</head>
<body style="margin:0;padding:0;background:#0f1014;font-family:Arial,Helvetica,sans-serif;color:#f4f4f6;">
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background:#0f1014;padding:28px 12px;">
        <tr>
            <td align="center">
                <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="max-width:620px;background:#171922;border:1px solid #2a2d37;border-radius:14px;overflow:hidden;">
                    <tr>
                        <td style="padding:24px 28px;background:linear-gradient(135deg,#1f2430 0%,#141822 100%);border-bottom:1px solid #2a2d37;">
                            <div style="font-size:13px;color:#c2c6d2;letter-spacing:0.3px;">AVA VPN</div>
                            <div style="margin-top:8px;font-size:28px;line-height:1.3;font-weight:700;color:#ffffff;">Подтвердите вашу почту</div>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:26px 28px 8px 28px;color:#e4e7ef;font-size:15px;line-height:1.7;">
                            <p style="margin:0 0 14px 0;">Здравствуйте, {{ $userName }}.</p>
                            <p style="margin:0 0 14px 0;">Спасибо за регистрацию в AVA VPN. Нажмите кнопку ниже, чтобы подтвердить email <strong style="color:#ffffff;">{{ $email }}</strong> и завершить вход в кабинет.</p>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:8px 28px 18px 28px;">
                            <a href="{{ $verificationUrl }}" style="display:inline-block;background:#e53935;color:#ffffff;text-decoration:none;font-size:16px;font-weight:700;padding:14px 22px;border-radius:10px;">Подтвердить email</a>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:0 28px 22px 28px;color:#adb3c2;font-size:13px;line-height:1.6;">
                            Ссылка действует ограниченное время. Если кнопку не получается открыть, используйте ссылку:
                            <div style="margin-top:8px;word-break:break-all;color:#cfd3df;">{{ $verificationUrl }}</div>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:16px 28px 24px 28px;border-top:1px solid #2a2d37;color:#8f95a6;font-size:12px;line-height:1.6;">
                            Если это письмо пришло по ошибке, просто проигнорируйте его.<br>
                            Поддержка: <a href="mailto:support@avavpn.ru" style="color:#d6d9e4;">support@avavpn.ru</a>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
