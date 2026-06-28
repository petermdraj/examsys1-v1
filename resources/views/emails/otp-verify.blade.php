<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Email Verification</title>
<style>
  body{margin:0;padding:0;background:#f5f4ff;font-family:'Inter',Arial,sans-serif;}
  .wrap{max-width:520px;margin:40px auto;background:#fff;border-radius:16px;overflow:hidden;box-shadow:0 4px 24px rgba(108,46,99,.08);}
  .header{background:#6C2E63;padding:32px 40px;text-align:center;}
  .header-logo{font-size:22px;font-weight:800;color:#fff;letter-spacing:-.5px;}
  .header-logo span{color:#E0A431;}
  .body{padding:40px;}
  .greeting{font-size:18px;font-weight:600;color:#1a1a2e;margin-bottom:8px;}
  .msg{font-size:15px;color:#555;line-height:1.65;margin-bottom:28px;}
  .otp-box{background:#f5f4ff;border:2px dashed #6C2E63;border-radius:14px;text-align:center;padding:24px 20px;margin-bottom:28px;}
  .otp-label{font-size:12px;font-weight:700;letter-spacing:.12em;text-transform:uppercase;color:#6C2E63;margin-bottom:10px;}
  .otp-code{font-size:44px;font-weight:800;letter-spacing:12px;color:#1a1a2e;font-family:'Courier New',monospace;}
  .otp-expiry{font-size:13px;color:#888;margin-top:10px;}
  .footer-msg{font-size:13px;color:#888;line-height:1.6;border-top:1px solid #eee;padding-top:20px;margin-top:4px;}
  .footer{background:#fafafa;padding:20px 40px;text-align:center;font-size:12px;color:#aaa;}
</style>
</head>
<body>
<div class="wrap">
  <div class="header">
    <div class="header-logo">{{ $appName }}</div>
  </div>
  <div class="body">
    <div class="greeting">{{ __('emails.otp_greeting', ['name' => $user->name]) }} 👋</div>
    <p class="msg">{{ __('emails.otp_intro') }}</p>
    <div class="otp-box">
      <div class="otp-label">{{ __('emails.otp_label') }}</div>
      <div class="otp-code">{{ $otp }}</div>
      <div class="otp-expiry">⏱ {{ __('emails.otp_expiry') }}</div>
    </div>
    <p class="footer-msg">
      {{ __('emails.otp_ignore', ['app' => $appName]) }}
      {{ __('emails.otp_never_share') }}
    </p>
  </div>
  <div class="footer">
    © {{ date('Y') }} {{ $appName }}. {{ __('emails.footer_rights') }}
  </div>
</div>
</body>
</html>
