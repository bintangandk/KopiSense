<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Token Reset Password</title>
</head>

<body
    style="margin: 0; padding: 0; background-color: #f5f7fb; font-family: 'Segoe UI', Tahoma, Arial, sans-serif; color: #1f2937;">
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="padding: 24px 12px;">
        <tr>
            <td align="center">
                <table role="presentation" width="100%" cellspacing="0" cellpadding="0"
                    style="max-width: 560px; background: #ffffff; border-radius: 12px; border: 1px solid #e5e7eb; overflow: hidden;">
                    <tr>
                        <td
                            style="padding: 20px 24px; background: linear-gradient(135deg, #0f766e, #14b8a6); color: #ffffff;">
                            <h1 style="margin: 0; font-size: 20px; font-weight: 700;">Reset Password</h1>
                            <p style="margin: 8px 0 0; font-size: 14px; opacity: 0.95;">Token verifikasi untuk akun Anda
                            </p>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding: 24px;">
                            <p style="margin: 0 0 12px; font-size: 15px;">Halo <strong>{{ $username }}</strong>,</p>
                            <p style="margin: 0 0 16px; font-size: 14px; line-height: 1.6;">
                                Gunakan token berikut untuk melanjutkan proses reset password.
                            </p>

                            <div
                                style="margin: 0 0 18px; padding: 16px; border: 1px dashed #0f766e; border-radius: 10px; text-align: center; background: #f0fdfa;">
                                <span
                                    style="display: inline-block; font-size: 24px; letter-spacing: 2px; font-weight: 700; color: #0f766e;">
                                    {{ $token }}
                                </span>
                            </div>

                            <p style="margin: 0 0 10px; font-size: 13px; line-height: 1.6; color: #4b5563;">
                                Token ini berlaku selama <strong>{{ $expireMinutes }} menit</strong>.
                            </p>
                            <p style="margin: 0; font-size: 13px; line-height: 1.6; color: #4b5563;">
                                Jika Anda tidak merasa melakukan permintaan ini, abaikan email ini.
                            </p>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding: 14px 24px; background: #f9fafb; border-top: 1px solid #e5e7eb;">
                            <p style="margin: 0; font-size: 12px; color: #6b7280; text-align: center;">
                                Email ini dikirim otomatis. Mohon tidak membalas email ini.
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>

</html>
