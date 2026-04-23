<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Код подтверждения</title>
</head>
<body style="margin:0;padding:0;background:#0f1014;font-family:Arial,Helvetica,sans-serif;color:#f4f4f6;">
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background:#0f1014;padding:28px 12px;">
        <tr>
            <td align="center">
                <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="max-width:620px;background:#171922;border:1px solid #2a2d37;border-radius:14px;overflow:hidden;">
                    <tr>
                        <td style="padding:24px 28px;background:linear-gradient(135deg,#1f2430 0%,#141822 100%);border-bottom:1px solid #2a2d37;">
                            <div style="font-size:13px;color:#c2c6d2;letter-spacing:0.3px;">AVA VPN</div>
                            <div style="margin-top:8px;font-size:24px;line-height:1.3;font-weight:700;color:#ffffff;">Код подтверждения</div>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:22px 28px 10px 28px;color:#e4e7ef;font-size:15px;line-height:1.7;">
                            <p style="margin:0 0 12px 0;">Кто-то пытается привязать этот email к Telegram-аккаунту в AVA VPN.</p>
                            <p style="margin:0 0 12px 0;">Введите код ниже в боте:</p>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:0 28px 18px 28px;">
                            <div style="display:inline-block;background:#0f1014;border:1px solid #2a2d37;border-radius:12px;padding:14px 18px;">
                                <div style="font-size:28px;letter-spacing:6px;font-weight:800;color:#ffffff;">{{ $code }}</div>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:0 28px 22px 28px;color:#adb3c2;font-size:13px;line-height:1.6;">
                            Код действует <strong style="color:#ffffff;">{{ $ttlMinutes }} мин.</strong> Если это были не вы — просто проигнорируйте письмо.
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:16px 28px 24px 28px;border-top:1px solid #2a2d37;color:#8f95a6;font-size:12px;line-height:1.6;">
                            Поддержка: <a href="mailto:support@avavpn.ru" style="color:#d6d9e4;">support@avavpn.ru</a>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>

