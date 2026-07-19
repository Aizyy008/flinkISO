<!doctype html>
<html>
<head><meta charset="utf-8"><meta name="viewport" content="width=device-width"></head>
<body style="margin:0;padding:0;background:#ecf0f5;font-family:Arial,Helvetica,sans-serif;color:#333;">
  <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#ecf0f5;padding:24px 0;">
    <tr><td align="center">
      <table role="presentation" width="600" cellpadding="0" cellspacing="0" style="max-width:600px;width:100%;background:#ffffff;border:1px solid #d2d6de;border-radius:4px;overflow:hidden;">

        {{-- Header (skin-blue, matching the FlinkISO app) --}}
        <tr><td style="background:#3c8dbc;padding:16px 24px;">
          <span style="color:#ffffff;font-size:18px;font-weight:bold;">FlinkISO</span>
          <span style="color:#cfe6f5;font-size:18px;"> QMS</span>
        </td></tr>

        {{-- Body --}}
        <tr><td style="padding:24px;">
          <div style="display:inline-block;background:#f4f4f4;border:1px solid #d2d6de;border-radius:3px;padding:3px 10px;font-size:12px;color:#555;text-transform:capitalize;margin-bottom:14px;">
            {{ str_replace('_', ' ', $type) }}
          </div>
          <h2 style="margin:0 0 10px;font-size:18px;color:#333;">{{ $title }}</h2>
          @if($body)<p style="margin:0 0 18px;font-size:14px;line-height:1.5;color:#555;">{{ $body }}</p>@endif

          @if($link)
          <table role="presentation" cellpadding="0" cellspacing="0"><tr><td style="background:#3c8dbc;border-radius:3px;">
            <a href="{{ $link }}" style="display:inline-block;padding:10px 20px;color:#ffffff;text-decoration:none;font-size:14px;font-weight:bold;">Open in QMS</a>
          </td></tr></table>
          @endif
        </td></tr>

        {{-- Footer --}}
        <tr><td style="background:#f9fafb;border-top:1px solid #d2d6de;padding:16px 24px;font-size:12px;color:#999;">
          This is an automated message from the FlinkISO Quality Management System.
          Please do not reply to this email.
        </td></tr>

      </table>
    </td></tr>
  </table>
</body>
</html>
