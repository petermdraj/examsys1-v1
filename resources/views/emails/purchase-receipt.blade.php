<!DOCTYPE html>
<html>
<head><meta charset="utf-8"><title>Purchase Receipt</title>
<style>
body { font-family: Arial, sans-serif; background: #f5f5f5; margin: 0; padding: 20px; }
.card { max-width: 560px; margin: 0 auto; background: #fff; border-radius: 12px; overflow: hidden; }
.header { background: #6C2E63; color: #fff; padding: 32px; text-align: center; }
.body { padding: 32px; }
.stat { display: flex; justify-content: space-between; padding: 12px 0; border-bottom: 1px solid #eee; }
.cta { display: block; margin: 24px auto 0; padding: 14px 32px; background: #E0A431; color: #fff; text-decoration: none; border-radius: 8px; text-align: center; font-weight: bold; }
.footer { padding: 16px 32px; background: #f9f9f9; font-size: 12px; color: #999; text-align: center; }
</style>
</head>
<body>
<div class="card">
    <div class="header">
        <h1>{{ __('emails.receipt_heading') }}</h1>
        <p>{{ __('emails.receipt_order_number', ['id' => substr($order->id, 0, 8)]) }}</p>
    </div>
    <div class="body">
        <p>{{ __('emails.hi_name', ['name' => $user->name]) }},</p>
        <p>{{ __('emails.receipt_intro') }}</p>
        <div class="stat"><span>{{ __('emails.receipt_label_quiz') }}</span><strong>{{ $quiz?->title }}</strong></div>
        <div class="stat"><span>{{ __('emails.receipt_label_amount') }}</span><strong>{{ $sym }}{{ number_format($order->amount, 2) }}</strong></div>
        <div class="stat"><span>{{ __('emails.receipt_label_date') }}</span><strong>{{ $order->paid_at?->format('d M Y, h:i A') }}</strong></div>
        <a href="{{ url('/quiz/' . ($quiz?->slug ?? '')) }}" class="cta">{{ __('emails.receipt_cta') }}</a>
    </div>
    <div class="footer">© {{ date('Y') }} {{ __('common.app_name') }}. {{ __('emails.footer_rights') }}</div>
</div>
</body>
</html>
