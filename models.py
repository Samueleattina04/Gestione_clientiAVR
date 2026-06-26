from datetime import datetime
from flask_sqlalchemy import SQLAlchemy
from flask_login import UserMixin
from werkzeug.security import generate_password_hash, check_password_hash

db = SQLAlchemy()


class User(UserMixin, db.Model):
    __tablename__ = 'users'
    id = db.Column(db.Integer, primary_key=True)
    username = db.Column(db.String(80), unique=True, nullable=False)
    email = db.Column(db.String(120), unique=True, nullable=False)
    password_hash = db.Column(db.String(256), nullable=False)
    created_at = db.Column(db.DateTime, default=datetime.utcnow)

    def set_password(self, password):
        self.password_hash = generate_password_hash(password)

    def check_password(self, password):
        return check_password_hash(self.password_hash, password)


class LicenseType(db.Model):
    __tablename__ = 'license_types'
    id = db.Column(db.Integer, primary_key=True)
    name = db.Column(db.String(100), nullable=False, unique=True)
    description = db.Column(db.Text)
    price_monthly = db.Column(db.Float, default=0.0)
    price_yearly = db.Column(db.Float, default=0.0)
    category = db.Column(db.String(50), default='Microsoft 365')
    is_active = db.Column(db.Boolean, default=True)
    created_at = db.Column(db.DateTime, default=datetime.utcnow)

    subscriptions = db.relationship('Subscription', backref='license_type', lazy=True)

    def __repr__(self):
        return f'<LicenseType {self.name}>'


class Customer(db.Model):
    __tablename__ = 'customers'
    id = db.Column(db.Integer, primary_key=True)
    first_name = db.Column(db.String(100), nullable=False)
    last_name = db.Column(db.String(100), nullable=False)
    email = db.Column(db.String(120), nullable=False)
    phone = db.Column(db.String(20))
    company = db.Column(db.String(150))
    fiscal_code = db.Column(db.String(20))
    vat_number = db.Column(db.String(20))
    address = db.Column(db.Text)
    city = db.Column(db.String(100))
    notes = db.Column(db.Text)
    is_active = db.Column(db.Boolean, default=True)
    created_at = db.Column(db.DateTime, default=datetime.utcnow)
    updated_at = db.Column(db.DateTime, default=datetime.utcnow, onupdate=datetime.utcnow)

    subscriptions = db.relationship('Subscription', backref='customer', lazy=True, cascade='all, delete-orphan')

    @property
    def full_name(self):
        return f'{self.first_name} {self.last_name}'

    @property
    def active_subscriptions_count(self):
        return sum(1 for s in self.subscriptions if s.status == 'active')

    def __repr__(self):
        return f'<Customer {self.full_name}>'


class Subscription(db.Model):
    __tablename__ = 'subscriptions'
    id = db.Column(db.Integer, primary_key=True)
    customer_id = db.Column(db.Integer, db.ForeignKey('customers.id'), nullable=False)
    license_type_id = db.Column(db.Integer, db.ForeignKey('license_types.id'), nullable=False)
    microsoft_tenant_id = db.Column(db.String(100))
    microsoft_subscription_id = db.Column(db.String(100))
    quantity = db.Column(db.Integer, default=1)
    start_date = db.Column(db.Date, nullable=False)
    end_date = db.Column(db.Date, nullable=False)
    billing_cycle = db.Column(db.String(20), default='yearly')  # monthly / yearly
    custom_price = db.Column(db.Float)
    status = db.Column(db.String(20), default='active')  # active / expired / cancelled / suspended
    auto_renew = db.Column(db.Boolean, default=True)
    reminder_6m_sent = db.Column(db.Boolean, default=False)
    reminder_1m_sent = db.Column(db.Boolean, default=False)
    reminder_1w_sent = db.Column(db.Boolean, default=False)
    notes = db.Column(db.Text)
    created_at = db.Column(db.DateTime, default=datetime.utcnow)
    updated_at = db.Column(db.DateTime, default=datetime.utcnow, onupdate=datetime.utcnow)

    @property
    def effective_price(self):
        if self.custom_price is not None:
            return self.custom_price
        if self.billing_cycle == 'monthly':
            return (self.license_type.price_monthly or 0) * self.quantity
        return (self.license_type.price_yearly or 0) * self.quantity

    @property
    def days_to_expiry(self):
        delta = self.end_date - datetime.utcnow().date()
        return delta.days

    @property
    def expiry_class(self):
        days = self.days_to_expiry
        if days < 0:
            return 'expired'
        if days <= 30:
            return 'critical'
        if days <= 90:
            return 'warning'
        if days <= 180:
            return 'soon'
        return 'ok'

    def __repr__(self):
        return f'<Subscription {self.customer_id} - {self.license_type_id}>'


class EmailLog(db.Model):
    __tablename__ = 'email_logs'
    id = db.Column(db.Integer, primary_key=True)
    subscription_id = db.Column(db.Integer, db.ForeignKey('subscriptions.id'))
    recipient_email = db.Column(db.String(120))
    recipient_type = db.Column(db.String(20))  # customer / reseller
    email_type = db.Column(db.String(30))  # reminder_6m / reminder_1m / reminder_1w / expired
    sent_at = db.Column(db.DateTime, default=datetime.utcnow)
    success = db.Column(db.Boolean, default=True)
    error_message = db.Column(db.Text)
