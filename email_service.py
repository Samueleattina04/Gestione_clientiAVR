import smtplib
from email.mime.text import MIMEText
from email.mime.multipart import MIMEMultipart
from datetime import datetime
from flask import current_app


def send_email(to_email, subject, html_body, text_body=None):
    cfg = current_app.config
    if not cfg.get('MAIL_USERNAME'):
        current_app.logger.warning('Email non configurata - salto invio a %s', to_email)
        return False, 'Email non configurata'

    try:
        msg = MIMEMultipart('alternative')
        msg['Subject'] = subject
        msg['From'] = cfg['MAIL_DEFAULT_SENDER']
        msg['To'] = to_email

        if text_body:
            msg.attach(MIMEText(text_body, 'plain', 'utf-8'))
        msg.attach(MIMEText(html_body, 'html', 'utf-8'))

        with smtplib.SMTP(cfg['MAIL_SERVER'], cfg['MAIL_PORT']) as server:
            if cfg.get('MAIL_USE_TLS'):
                server.starttls()
            server.login(cfg['MAIL_USERNAME'], cfg['MAIL_PASSWORD'])
            server.sendmail(cfg['MAIL_DEFAULT_SENDER'], to_email, msg.as_string())

        return True, None
    except Exception as e:
        return False, str(e)


def build_reminder_html(subscription, recipient_type, months_left):
    customer = subscription.customer
    license_name = subscription.license_type.name
    end_date = subscription.end_date.strftime('%d/%m/%Y')
    reseller_name = current_app.config.get('RESELLER_NAME', 'A.V.R. Informatica')
    reseller_email = current_app.config.get('RESELLER_EMAIL', '')

    if recipient_type == 'customer':
        intro = f"Gentile {customer.full_name},"
        body = f"""
            <p>Ti informiamo che il tuo abbonamento <strong>{license_name}</strong>
            ({subscription.quantity} licenz{'a' if subscription.quantity == 1 else 'e'})
            è in scadenza il <strong>{end_date}</strong> ({months_left} mes{'e' if months_left == 1 else 'i'} rimanenti).</p>
            <p>Per procedere al rinnovo contatta il tuo fornitore:</p>
            <p><strong>{reseller_name}</strong><br>
            📧 <a href="mailto:{reseller_email}">{reseller_email}</a></p>
            <div style="background:#fff3cd;border-left:4px solid #ffc107;padding:12px;margin:16px 0;border-radius:4px;">
            ⚠️ <strong>Attenzione:</strong> Se l'abbonamento scade senza rinnovo si potrebbe
            verificare la perdita dei dati associati. {reseller_name} non si assume responsabilità
            per i dati persi a causa di mancato rinnovo.
            </div>
        """
    else:
        company_label = f" ({customer.company})" if customer.company else ""
        intro = f"Promemoria interno — {reseller_name},"
        body = f"""
            <p>L'abbonamento del cliente <strong>{customer.full_name}{company_label}</strong>
            ({customer.email}) è in scadenza.</p>
            <ul>
              <li><strong>Licenza:</strong> {license_name}</li>
              <li><strong>Quantità:</strong> {subscription.quantity}</li>
              <li><strong>Scadenza:</strong> {end_date} ({months_left} mes{'e' if months_left == 1 else 'i'})</li>
              <li><strong>Prezzo:</strong> €{subscription.effective_price:.2f}</li>
            </ul>
            <p>Contatta il cliente per procedere al rinnovo.</p>
        """

    urgency_color = '#dc3545' if months_left <= 1 else '#fd7e14' if months_left <= 3 else '#0d6efd'

    return f"""<!DOCTYPE html>
<html lang="it">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1"></head>
<body style="margin:0;font-family:Arial,sans-serif;background:#f5f5f5">
  <div style="max-width:600px;margin:30px auto;background:#fff;border-radius:12px;overflow:hidden;box-shadow:0 4px 20px rgba(0,0,0,.1)">
    <div style="background:{urgency_color};padding:28px 32px;text-align:center">
      <h1 style="color:#fff;margin:0;font-size:22px">⏰ Promemoria Scadenza Abbonamento</h1>
      <p style="color:rgba(255,255,255,.9);margin:6px 0 0">{reseller_name} — Gestione Licenze Microsoft 365</p>
    </div>
    <div style="padding:32px">
      <p style="font-size:16px">{intro}</p>
      {body}
    </div>
    <div style="background:#f8f9fa;padding:20px 32px;text-align:center;font-size:12px;color:#6c757d">
      <p>Questa email è stata inviata automaticamente dal sistema di gestione licenze di <strong>{reseller_name}</strong>.</p>
    </div>
  </div>
</body>
</html>"""


def send_subscription_reminder(subscription, months_left):
    from models import EmailLog, db

    subject = f"[A.V.R. Informatica] Scadenza Abbonamento {subscription.license_type.name} — {months_left} mes{'e' if months_left == 1 else 'i'}"

    reseller_email = current_app.config.get('RESELLER_EMAIL')
    customer_email = subscription.customer.email

    results = []
    for recipient_type, email_addr in [('reseller', reseller_email), ('customer', customer_email)]:
        if not email_addr:
            continue
        html = build_reminder_html(subscription, recipient_type, months_left)
        ok, err = send_email(email_addr, subject, html)
        log = EmailLog(
            subscription_id=subscription.id,
            recipient_email=email_addr,
            recipient_type=recipient_type,
            email_type=f'reminder_{months_left}m',
            success=ok,
            error_message=err,
        )
        db.session.add(log)
        results.append((recipient_type, ok, err))

    db.session.commit()
    return results
