<!DOCTYPE html>
<html lang="it">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1"></head>
<body style="margin:0;font-family:Arial,sans-serif;background:#f5f5f5">
<div style="max-width:620px;margin:30px auto;background:#fff;border-radius:12px;overflow:hidden;box-shadow:0 4px 20px rgba(0,0,0,.1)">

  {{-- Header colorato per urgenza --}}
  <div style="background:#1A1A1A;padding:20px 32px;text-align:center">
    <img src="{{ config('app.url') }}/img/banner.png" alt="{{ config('avr.reseller_name') }}" style="max-height:60px;max-width:260px">
  </div>
  <div style="background:{{ $monthsLeft<=1?'#C8102E':($monthsLeft<=3?'#fd7e14':'#0d6efd') }};padding:28px 32px;text-align:center">
    <h1 style="color:#fff;margin:0;font-size:22px">&#9200; Promemoria Scadenza Abbonamento</h1>
    <p style="color:rgba(255,255,255,.9);margin:8px 0 0;font-size:14px">{{ config('avr.reseller_name') }} &mdash; Gestione Licenze Microsoft 365</p>
  </div>

  <div style="padding:32px">

    @if($recipientType==='customer')
    {{-- ══════════════════════════════════════
         EMAIL AL CLIENTE
    ══════════════════════════════════════ --}}
    <p style="font-size:16px">Gentile <strong>{{ $subscription->customer->full_name }}</strong>,</p>

    <p>ti scriviamo per informarti che il tuo abbonamento è in prossima scadenza:</p>

    <div style="background:#f8f9fa;border-radius:8px;padding:16px 20px;margin:20px 0">
      <table style="width:100%;border-collapse:collapse;font-size:14px">
        <tr><td style="padding:6px 0;color:#666">Licenza</td><td style="padding:6px 0;font-weight:bold">{{ $subscription->licenseType->name }}</td></tr>
        <tr><td style="padding:6px 0;color:#666">Quantità</td><td style="padding:6px 0;font-weight:bold">{{ $subscription->quantity }}</td></tr>
        <tr><td style="padding:6px 0;color:#666">Scadenza</td><td style="padding:6px 0;font-weight:bold;color:{{ $monthsLeft<=1?'#C8102E':'#333' }}">{{ $subscription->end_date->format('d/m/Y') }}</td></tr>
        <tr><td style="padding:6px 0;color:#666">Tempo rimanente</td><td style="padding:6px 0;font-weight:bold">
          @if($monthsLeft >= 1)
            circa {{ $monthsLeft }} {{ $monthsLeft===1?'mese':'mesi' }}
          @else
            meno di 1 mese
          @endif
        </td></tr>
      </table>
    </div>

    <p>Per <strong>rinnovare il tuo abbonamento</strong> o ricevere assistenza, contattaci prima della scadenza:</p>
    <div style="text-align:center;margin:24px 0">
      <a href="mailto:{{ config('avr.reseller_email') }}"
         style="background:#C8102E;color:#fff;padding:12px 28px;border-radius:6px;text-decoration:none;font-weight:bold;font-size:15px">
        &#9993; Contatta {{ config('avr.reseller_name') }}
      </a>
    </div>

    {{-- Avviso perdita dati --}}
    <div style="background:#fff3cd;border-left:4px solid #ffc107;padding:16px;margin:24px 0;border-radius:4px">
      <p style="margin:0 0 8px;font-weight:bold;color:#856404">&#9888;&nbsp; Avviso importante — Rischio perdita dati</p>
      <p style="margin:0;font-size:14px;color:#664d03;line-height:1.6">
        In caso di <strong>mancato rinnovo entro la data di scadenza</strong>, Microsoft potrebbe sospendere e successivamente
        eliminare i dati associati all'abbonamento (email, file, contatti, calendari e altri contenuti Microsoft 365).
        Una volta eliminati, i dati potrebbero <strong>non essere recuperabili</strong>.
      </p>
    </div>

    {{-- Esonero responsabilità --}}
    <div style="background:#f8d7da;border-left:4px solid #C8102E;padding:16px;margin:0 0 24px;border-radius:4px">
      <p style="margin:0 0 8px;font-weight:bold;color:#842029">&#128221;&nbsp; Esonero di responsabilità</p>
      <p style="margin:0;font-size:13px;color:#6c1d24;line-height:1.6">
        <strong>{{ config('avr.reseller_name') }}</strong> declina ogni responsabilità per eventuali perdite di dati,
        interruzioni di servizio o danni derivanti dalla mancata o ritardata rinnovazione dell'abbonamento da parte del cliente.
        È responsabilità esclusiva del cliente provvedere al rinnovo entro i termini indicati.
      </p>
    </div>

    @else
    {{-- ══════════════════════════════════════
         EMAIL AL RIVENDITORE (promemoria interno)
    ══════════════════════════════════════ --}}
    <p style="font-size:16px"><strong>&#128203; Promemoria interno — Abbonamento in scadenza</strong></p>

    <div style="background:#f8f9fa;border-radius:8px;padding:16px 20px;margin:20px 0">
      <table style="width:100%;border-collapse:collapse;font-size:14px">
        <tr><td style="padding:6px 0;color:#666;width:140px">Cliente</td><td style="padding:6px 0;font-weight:bold">{{ $subscription->customer->full_name }}</td></tr>
        @if($subscription->customer->company)
        <tr><td style="padding:6px 0;color:#666">Azienda</td><td style="padding:6px 0">{{ $subscription->customer->company }}</td></tr>
        @endif
        <tr><td style="padding:6px 0;color:#666">Email cliente</td><td style="padding:6px 0"><a href="mailto:{{ $subscription->customer->email }}">{{ $subscription->customer->email }}</a></td></tr>
        @if($subscription->customer->phone)
        <tr><td style="padding:6px 0;color:#666">Telefono</td><td style="padding:6px 0">{{ $subscription->customer->phone }}</td></tr>
        @endif
        <tr><td style="padding:6px 0;color:#666">Licenza</td><td style="padding:6px 0;font-weight:bold">{{ $subscription->licenseType->name }}</td></tr>
        <tr><td style="padding:6px 0;color:#666">Quantità</td><td style="padding:6px 0">{{ $subscription->quantity }}</td></tr>
        <tr><td style="padding:6px 0;color:#666">Ciclo</td><td style="padding:6px 0">{{ $subscription->billing_cycle === 'monthly' ? 'Mensile' : 'Annuale' }}</td></tr>
        <tr><td style="padding:6px 0;color:#666">Scadenza</td><td style="padding:6px 0;font-weight:bold;color:{{ $monthsLeft<=1?'#C8102E':'#333' }}">{{ $subscription->end_date->format('d/m/Y') }}</td></tr>
        <tr><td style="padding:6px 0;color:#666">Tempo rimanente</td><td style="padding:6px 0;font-weight:bold">
          @if($monthsLeft >= 1)
            circa {{ $monthsLeft }} {{ $monthsLeft===1?'mese':'mesi' }}
          @else
            meno di 1 mese &mdash; <span style="color:#C8102E">URGENTE</span>
          @endif
        </td></tr>
        <tr><td style="padding:6px 0;color:#666">Valore</td><td style="padding:6px 0">&euro;{{ number_format($subscription->effective_price,2,',','.') }} / {{ $subscription->billing_cycle === 'monthly' ? 'mese' : 'anno' }}</td></tr>
      </table>
    </div>

    <p style="font-size:14px;color:#555">
      &#128222; Contatta il cliente per proporre il rinnovo prima della scadenza.
      L'email di promemoria è stata inviata anche al cliente all'indirizzo
      <a href="mailto:{{ $subscription->customer->email }}">{{ $subscription->customer->email }}</a>.
    </p>
    @endif

  </div>

  <div style="background:#1A1A1A;padding:20px 32px;text-align:center">
    <p style="color:rgba(255,255,255,.7);margin:0 0 4px;font-size:12px">
      Email inviata automaticamente dal gestionale di <strong style="color:#fff">{{ config('avr.reseller_name') }}</strong>
    </p>
    <p style="color:rgba(255,255,255,.4);margin:0;font-size:11px">
      Non rispondere a questa email &mdash; per assistenza scrivere a
      <a href="mailto:{{ config('avr.reseller_email') }}" style="color:#C8102E">{{ config('avr.reseller_email') }}</a>
    </p>
  </div>

</div>
</body>
</html>
