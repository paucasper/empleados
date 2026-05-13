<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $subjectLine }}</title>
</head>
<body style="margin:0; padding:0; background-color:#f6f7f2; font-family: Arial, Helvetica, sans-serif; color:#1f2937;">
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="background-color:#f6f7f2; margin:0; padding:24px 12px;">
        <tr>
            <td align="center">
                <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="max-width:680px;">
                    
                    {{-- Encabezado --}}
                    <tr>
                        <td style="padding:0 0 16px 0;">
                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="background:linear-gradient(135deg, #2f4a27 0%, #3b5a31 100%); border-radius:24px 24px 0 0;">
                                <tr>
                                    <td style="padding:28px 32px;">
                                        <div style="font-size:12px; letter-spacing:0.24em; text-transform:uppercase; color:#c5a35d; font-weight:700;">
                                            Portal interno
                                        </div>
                                        <div style="margin-top:10px; font-size:28px; line-height:1.2; font-weight:700; color:#ffffff;">
                                            Dcoop · Tramitaciones
                                        </div>
                                        <div style="margin-top:10px; font-size:15px; line-height:1.6; color:rgba(255,255,255,0.78);">
                                            Comunicación automática del flujo de ausencias y gastos.
                                        </div>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    {{-- Tarjeta principal --}}
                    <tr>
                        <td>
                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="background:#ffffff; border:1px solid #e5e7eb; border-top:none; border-radius:0 0 24px 24px; box-shadow:0 14px 40px rgba(15,23,42,0.06);">
                                <tr>
                                    <td style="padding:32px;">
                                        
                                        {{-- Título --}}
                                        <div style="font-size:30px; line-height:1.2; font-weight:700; color:#2f4a27;">
                                            {{ $title }}
                                        </div>

                                        {{-- Intro --}}
                                        <div style="margin-top:14px; font-size:15px; line-height:1.7; color:#4b5563;">
                                            {{ $intro }}
                                        </div>

                                        {{-- Estado visual --}}
                                        @php
                                            $estado = $details['Estado'] ?? null;
                                            $isRejected = $estado && str_contains(strtolower($estado), 'rechaz');
                                            $isApproved = $estado && (str_contains(strtolower($estado), 'aprob') || str_contains(strtolower($estado), 'export'));
                                            $badgeBg = $isRejected ? '#fef2f2' : ($isApproved ? '#eef6ea' : '#f2f4ed');
                                            $badgeText = $isRejected ? '#b91c1c' : ($isApproved ? '#2f4a27' : '#2f4a27');
                                            $badgeBorder = $isRejected ? '#fecaca' : ($isApproved ? '#dbe7d1' : '#dfe6d6');
                                        @endphp

                                        @if($estado)
                                            <div style="margin-top:22px;">
                                                <span style="display:inline-block; padding:10px 16px; border-radius:999px; font-size:12px; font-weight:700; letter-spacing:0.08em; text-transform:uppercase; background-color:{{ $badgeBg }}; color:{{ $badgeText }}; border:1px solid {{ $badgeBorder }};">
                                                    {{ $estado }}
                                                </span>
                                            </div>
                                        @endif

                                        {{-- Caja de detalles --}}
                                        @if(!empty($details))
                                            <div style="margin-top:28px; border:1px solid #edf0e7; border-radius:20px; overflow:hidden; background:#fcfcf9;">
                                                <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0">
                                                    @foreach($details as $label => $value)
                                                        @if(!is_null($value) && $value !== '')
                                                            <tr>
                                                                <td style="width:38%; padding:15px 18px; border-bottom:1px solid #edf0e7; font-size:13px; font-weight:700; color:#2f4a27; background:#f7f8f3;">
                                                                    {{ $label }}
                                                                </td>
                                                                <td style="padding:15px 18px; border-bottom:1px solid #edf0e7; font-size:14px; line-height:1.6; color:#374151;">
                                                                    {{ $value }}
                                                                </td>
                                                            </tr>
                                                        @endif
                                                    @endforeach
                                                </table>
                                            </div>
                                        @endif

                                        {{-- Nota final --}}
                                        <div style="margin-top:28px; font-size:13px; line-height:1.6; color:#6b7280;">
                                            Este es un mensaje automático del portal de tramitaciones de Dcoop.  
                                            Por favor, no respondas a este correo.
                                        </div>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    {{-- Footer --}}
                    <tr>
                        <td style="padding:18px 8px 0 8px; text-align:center;">
                            <div style="font-size:11px; line-height:1.6; letter-spacing:0.08em; text-transform:uppercase; color:#9ca3af;">
                                Dcoop · Departamento de Sistemas
                            </div>
                        </td>
                    </tr>

                </table>
            </td>
        </tr>
    </table>
</body>
</html>