<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Maintenance — {{ config('app.name') }}</title>
<style>
*{margin:0;padding:0;box-sizing:border-box;}
body{font-family:system-ui,sans-serif;background:#f8f7ff;display:flex;align-items:center;justify-content:center;min-height:100vh;padding:24px;}
.card{background:#fff;border-radius:20px;padding:48px 40px;text-align:center;max-width:480px;width:100%;box-shadow:0 4px 24px rgba(0,0,0,.08);}
.icon{font-size:48px;margin-bottom:20px;}
h1{font-size:26px;font-weight:700;color:#1E1B4B;margin-bottom:12px;}
p{color:#6b7280;line-height:1.6;font-size:15px;}
</style>
</head>
<body>
<div class="card">
  <div class="icon">🛠️</div>
  <h1>Under Maintenance</h1>
  <p>{{ $message }}</p>
</div>
</body>
</html>
