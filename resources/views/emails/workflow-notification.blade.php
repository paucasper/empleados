<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>{{ $subjectLine }}</title>
</head>
<body style="font-family: Arial, Helvetica, sans-serif; background:#f6f7f2; color:#1f2937; padding:24px;">
    <div style="max-width:700px; margin:0 auto; background:#ffffff; border:1px solid #e5e7eb; border-radius:16px; overflow:hidden;">
        <div style="background:#2f4a27; color:white; padding:24px 28px;">
            <h1 style="margin:0; font-size:22px;">{{ $title }}</h1>
        </div>

        <div style="padding:28px;">
            <p style="margin-top:0; font-size:15px; line-height:1.6;">
                {{ $intro }}
            </p>

            @if(!empty($details))
                <table style="width:100%; border-collapse:collapse; margin-top:20px;">
                    <tbody>
                    @foreach($details as $label => $value)
                        <tr>
                            <td style="padding:10px 0; border-bottom:1px solid #f0f0f0; width:220px; font-weight:bold; color:#2f4a27;">
                                {{ $label }}
                            </td>
                            <td style="padding:10px 0; border-bottom:1px solid #f0f0f0;">
                                {{ $value }}
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            @endif

            <p style="margin-top:24px; font-size:13px; color:#6b7280;">
                Correo automático del portal de tramitaciones de Dcoop.
            </p>
        </div>
    </div>
</body>
</html>