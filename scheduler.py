from datetime import datetime, timedelta
from apscheduler.schedulers.background import BackgroundScheduler
from apscheduler.triggers.cron import CronTrigger


def check_expiring_subscriptions(app):
    with app.app_context():
        from models import Subscription, db
        from email_service import send_subscription_reminder

        today = datetime.utcnow().date()
        active_subs = Subscription.query.filter_by(status='active').all()

        for sub in active_subs:
            days_left = (sub.end_date - today).days

            if days_left < 0:
                sub.status = 'expired'
                db.session.commit()
                continue

            if 175 <= days_left <= 185 and not sub.reminder_6m_sent:
                send_subscription_reminder(sub, 6)
                sub.reminder_6m_sent = True
                db.session.commit()

            elif 28 <= days_left <= 32 and not sub.reminder_1m_sent:
                send_subscription_reminder(sub, 1)
                sub.reminder_1m_sent = True
                db.session.commit()

            elif 5 <= days_left <= 8 and not sub.reminder_1w_sent:
                send_subscription_reminder(sub, 0)
                sub.reminder_1w_sent = True
                db.session.commit()


def start_scheduler(app):
    scheduler = BackgroundScheduler()
    scheduler.add_job(
        func=check_expiring_subscriptions,
        args=[app],
        trigger=CronTrigger(hour=8, minute=0),
        id='check_expiring',
        replace_existing=True,
    )
    scheduler.start()
    return scheduler
