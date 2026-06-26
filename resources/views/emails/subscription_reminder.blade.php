<!DOCTYPE html>
<html lang="it">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1"></head>
<body style="margin:0;font-family:Arial,sans-serif;background:#f5f5f5">
<div style="max-width:600px;margin:30px auto;background:#fff;border-radius:12px;overflow:hidden;box-shadow:0 4px 20px rgba(0,0,0,.1)">
  <div style="background:{{ $monthsLeft<=1?'#C8102E':($monthsLeft<=3?'#fd7e14':'#0d6efd') }};padding:28px 32px;text-align:center">
    <h1 style="color:#fff;margin:0;font-size:22px">&#9200; Promemoria Scadenza Abbonamento</h1>
    <p style="color:rgba(255,255,255,.9);margin:6px 0 0">{{ config('avr.reseller_name') }} &mdash; Gestione Licenze Microsoft 365</p>
  </div>
  <div style="padding:32px">
    @if($recipientType==='customer')
    <p>Gentile <strong>{{ $subscription->customer->full_name }}</strong>,</p>
    <p>Il tuo abbonamento <strong>{{ $subscription->licenseType->name }}</strong> scade il <strong>{{ $subscription->end_date->format('d/m/Y') }}</strong> ({{ $monthsLeft }} mes{{ $monthsLeft===1?'e':'i' }} rimanenti).</p>
    <p>Per rinnovare contatta: <strong>{{ config('avr.reseller_name') }}</strong> &mdash; <a href="mailto:{{ config('avr.reseller_email') }}">{{ config('avr.reseller_email') }}</a></p>
    <div style="background:#fff3cd;border-left:4px solid #ffc107;padding:12px;margin:16px 0;border-radius:4px">&#9888; <strong>Attenzione:</strong> La mancata rinnovazione potrebbe causare perdita dei dati. {{ config('avr.reseller_name') }} non si assume responsabilit&agrave; per dati persi.</div>
    @else
    <p><strong>Promemoria interno &mdash; {{ config('avr.reseller_name') }}</strong></p>
    <p>Abbonamento in scadenza: <strong>{{ $subscription->customer->full_name }}{{ $subscription->customer->company?' ('.$subscription->customer->company.')':'' }}</strong></p>
    <ul>
      <li><strong>Licenza:</strong> {{ $subscription->licenseType->name }}</li>
      <li><strong>Quantit&agrave;:</strong> {{ $subscription->quantity }}</li>
      <li><strong>Scadenza:</strong> {{ $subscription->end_date->format('d/m/Y') }} ({{ $monthsLeft }} mes{{ $monthsLeft===1?'e':'i' }})</li>
      <li><strong>Prezzo:</strong> &euro;{{ number_format($subscription->effective_price,2,',','.') }}</li>
    </ul>
    @endif
  </div>
  <div style="background:#f8f9fa;padding:20px 32px;text-align:center;font-size:12px;color:#6c757d">
    <p>Email inviata automaticamente da <strong>{{ config('avr.reseller_name') }}</strong>.</p>
  </div>
</div>
</body>
</html>
