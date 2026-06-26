from flask import Flask, render_template, redirect, url_for, request, flash, send_file, jsonify, abort
from flask_login import LoginManager, login_user, logout_user, login_required, current_user
from datetime import datetime, date, timedelta
from config import Config
from models import db, User, Customer, LicenseType, Subscription, EmailLog
from excel_service import export_customers_excel, export_subscriptions_excel, import_customers_excel
from scheduler import start_scheduler

app = Flask(__name__)
app.config.from_object(Config)

db.init_app(app)

login_manager = LoginManager(app)
login_manager.login_view = 'login'
login_manager.login_message = 'Accedi per continuare.'
login_manager.login_message_category = 'warning'


@login_manager.user_loader
def load_user(user_id):
    return User.query.get(int(user_id))


# ── Auth ─────────────────────────────────────────────────────────────────────

@app.route('/login', methods=['GET', 'POST'])
def login():
    if current_user.is_authenticated:
        return redirect(url_for('dashboard'))
    if request.method == 'POST':
        user = User.query.filter_by(username=request.form.get('username')).first()
        if user and user.check_password(request.form.get('password')):
            login_user(user, remember=request.form.get('remember'))
            return redirect(request.args.get('next') or url_for('dashboard'))
        flash('Credenziali non valide.', 'danger')
    return render_template('login.html')


@app.route('/logout')
@login_required
def logout():
    logout_user()
    return redirect(url_for('login'))


# ── Dashboard ─────────────────────────────────────────────────────────────────

@app.route('/')
@login_required
def dashboard():
    today = date.today()
    total_customers = Customer.query.filter_by(is_active=True).count()
    total_subs = Subscription.query.filter_by(status='active').count()

    expiring_30 = Subscription.query.filter(
        Subscription.status == 'active',
        Subscription.end_date <= today + timedelta(days=30),
        Subscription.end_date >= today
    ).count()

    expiring_90 = Subscription.query.filter(
        Subscription.status == 'active',
        Subscription.end_date <= today + timedelta(days=90),
        Subscription.end_date >= today
    ).count()

    expiring_180 = Subscription.query.filter(
        Subscription.status == 'active',
        Subscription.end_date <= today + timedelta(days=180),
        Subscription.end_date >= today
    ).count()

    expired = Subscription.query.filter(
        Subscription.status == 'active',
        Subscription.end_date < today
    ).count()

    recent_subs = (Subscription.query
                   .join(Customer).join(LicenseType)
                   .filter(Subscription.status == 'active')
                   .order_by(Subscription.end_date.asc())
                   .limit(10).all())

    # Revenue stats
    active_subs = Subscription.query.filter_by(status='active').all()
    monthly_revenue = sum(
        s.effective_price / 12 if s.billing_cycle == 'yearly' else s.effective_price
        for s in active_subs
    )
    yearly_revenue = sum(
        s.effective_price if s.billing_cycle == 'yearly' else s.effective_price * 12
        for s in active_subs
    )

    # License distribution
    license_stats = db.session.query(
        LicenseType.name,
        db.func.count(Subscription.id).label('count')
    ).join(Subscription, Subscription.license_type_id == LicenseType.id)\
     .filter(Subscription.status == 'active')\
     .group_by(LicenseType.name).all()

    return render_template('dashboard.html',
                           today=today,
                           total_customers=total_customers,
                           total_subs=total_subs,
                           expiring_30=expiring_30,
                           expiring_90=expiring_90,
                           expiring_180=expiring_180,
                           expired=expired,
                           recent_subs=recent_subs,
                           monthly_revenue=monthly_revenue,
                           yearly_revenue=yearly_revenue,
                           license_stats=license_stats)


# ── Customers ─────────────────────────────────────────────────────────────────

@app.route('/customers')
@login_required
def customers():
    q = request.args.get('q', '').strip()
    page = request.args.get('page', 1, type=int)
    query = Customer.query.filter_by(is_active=True)
    if q:
        like = f'%{q}%'
        query = query.filter(
            db.or_(Customer.first_name.ilike(like),
                   Customer.last_name.ilike(like),
                   Customer.email.ilike(like),
                   Customer.company.ilike(like))
        )
    pagination = query.order_by(Customer.last_name).paginate(page=page, per_page=20, error_out=False)
    return render_template('customers/list.html', pagination=pagination, q=q)


@app.route('/customers/add', methods=['GET', 'POST'])
@login_required
def customer_add():
    licenses = LicenseType.query.filter_by(is_active=True).order_by(LicenseType.name).all()
    if request.method == 'POST':
        customer = Customer(
            first_name=request.form.get('first_name', '').strip(),
            last_name=request.form.get('last_name', '').strip(),
            email=request.form.get('email', '').strip(),
            phone=request.form.get('phone', '').strip(),
            company=request.form.get('company', '').strip(),
            fiscal_code=request.form.get('fiscal_code', '').strip(),
            vat_number=request.form.get('vat_number', '').strip(),
            address=request.form.get('address', '').strip(),
            city=request.form.get('city', '').strip(),
            notes=request.form.get('notes', '').strip(),
        )
        db.session.add(customer)
        db.session.flush()

        # Optional immediate subscription
        lic_id = request.form.get('license_type_id')
        start_str = request.form.get('start_date')
        end_str = request.form.get('end_date')
        if lic_id and start_str and end_str:
            sub = Subscription(
                customer_id=customer.id,
                license_type_id=int(lic_id),
                quantity=int(request.form.get('quantity', 1)),
                start_date=datetime.strptime(start_str, '%Y-%m-%d').date(),
                end_date=datetime.strptime(end_str, '%Y-%m-%d').date(),
                billing_cycle=request.form.get('billing_cycle', 'yearly'),
                custom_price=float(request.form.get('custom_price')) if request.form.get('custom_price') else None,
                notes=request.form.get('sub_notes', '').strip(),
            )
            db.session.add(sub)

        db.session.commit()
        flash(f'Cliente {customer.full_name} aggiunto con successo!', 'success')
        return redirect(url_for('customer_view', id=customer.id))

    return render_template('customers/add.html', licenses=licenses)


@app.route('/customers/<int:id>')
@login_required
def customer_view(id):
    customer = Customer.query.get_or_404(id)
    return render_template('customers/view.html', customer=customer, today=date.today())


@app.route('/customers/<int:id>/edit', methods=['GET', 'POST'])
@login_required
def customer_edit(id):
    customer = Customer.query.get_or_404(id)
    if request.method == 'POST':
        customer.first_name = request.form.get('first_name', '').strip()
        customer.last_name = request.form.get('last_name', '').strip()
        customer.email = request.form.get('email', '').strip()
        customer.phone = request.form.get('phone', '').strip()
        customer.company = request.form.get('company', '').strip()
        customer.fiscal_code = request.form.get('fiscal_code', '').strip()
        customer.vat_number = request.form.get('vat_number', '').strip()
        customer.address = request.form.get('address', '').strip()
        customer.city = request.form.get('city', '').strip()
        customer.notes = request.form.get('notes', '').strip()
        db.session.commit()
        flash('Cliente aggiornato con successo!', 'success')
        return redirect(url_for('customer_view', id=customer.id))
    return render_template('customers/edit.html', customer=customer)


@app.route('/customers/<int:id>/delete', methods=['POST'])
@login_required
def customer_delete(id):
    customer = Customer.query.get_or_404(id)
    customer.is_active = False
    db.session.commit()
    flash(f'Cliente {customer.full_name} archiviato.', 'info')
    return redirect(url_for('customers'))


# ── Licenses ─────────────────────────────────────────────────────────────────

@app.route('/licenses')
@login_required
def licenses():
    items = LicenseType.query.order_by(LicenseType.category, LicenseType.name).all()
    return render_template('licenses/list.html', licenses=items)


@app.route('/licenses/add', methods=['GET', 'POST'])
@login_required
def license_add():
    if request.method == 'POST':
        lic = LicenseType(
            name=request.form.get('name', '').strip(),
            description=request.form.get('description', '').strip(),
            category=request.form.get('category', 'Microsoft 365').strip(),
            price_monthly=float(request.form.get('price_monthly') or 0),
            price_yearly=float(request.form.get('price_yearly') or 0),
        )
        db.session.add(lic)
        db.session.commit()
        flash(f'Licenza "{lic.name}" aggiunta!', 'success')
        return redirect(url_for('licenses'))
    return render_template('licenses/add.html')


@app.route('/licenses/<int:id>/edit', methods=['GET', 'POST'])
@login_required
def license_edit(id):
    lic = LicenseType.query.get_or_404(id)
    if request.method == 'POST':
        lic.name = request.form.get('name', '').strip()
        lic.description = request.form.get('description', '').strip()
        lic.category = request.form.get('category', 'Microsoft 365').strip()
        lic.price_monthly = float(request.form.get('price_monthly') or 0)
        lic.price_yearly = float(request.form.get('price_yearly') or 0)
        lic.is_active = 'is_active' in request.form
        db.session.commit()
        flash('Licenza aggiornata!', 'success')
        return redirect(url_for('licenses'))
    return render_template('licenses/edit.html', license=lic)


@app.route('/licenses/<int:id>/delete', methods=['POST'])
@login_required
def license_delete(id):
    lic = LicenseType.query.get_or_404(id)
    lic.is_active = False
    db.session.commit()
    flash(f'Licenza "{lic.name}" disattivata.', 'info')
    return redirect(url_for('licenses'))


@app.route('/api/license/<int:id>/price')
@login_required
def license_price_api(id):
    lic = LicenseType.query.get_or_404(id)
    return jsonify({'price_monthly': lic.price_monthly, 'price_yearly': lic.price_yearly})


# ── Subscriptions ─────────────────────────────────────────────────────────────

@app.route('/subscriptions')
@login_required
def subscriptions():
    today = date.today()
    status_filter = request.args.get('status', 'active')
    q = request.args.get('q', '').strip()
    page = request.args.get('page', 1, type=int)

    query = (Subscription.query
             .join(Customer).join(LicenseType)
             .filter(Subscription.status == status_filter))
    if q:
        like = f'%{q}%'
        query = query.filter(
            db.or_(Customer.first_name.ilike(like),
                   Customer.last_name.ilike(like),
                   Customer.email.ilike(like),
                   LicenseType.name.ilike(like))
        )

    pagination = query.order_by(Subscription.end_date.asc()).paginate(page=page, per_page=25, error_out=False)
    return render_template('subscriptions/list.html', pagination=pagination,
                           status_filter=status_filter, q=q, today=today)


@app.route('/subscriptions/add', methods=['GET', 'POST'])
@login_required
def subscription_add():
    customers_list = Customer.query.filter_by(is_active=True).order_by(Customer.last_name).all()
    licenses = LicenseType.query.filter_by(is_active=True).order_by(LicenseType.name).all()
    if request.method == 'POST':
        sub = Subscription(
            customer_id=int(request.form.get('customer_id')),
            license_type_id=int(request.form.get('license_type_id')),
            quantity=int(request.form.get('quantity', 1)),
            start_date=datetime.strptime(request.form.get('start_date'), '%Y-%m-%d').date(),
            end_date=datetime.strptime(request.form.get('end_date'), '%Y-%m-%d').date(),
            billing_cycle=request.form.get('billing_cycle', 'yearly'),
            custom_price=float(request.form.get('custom_price')) if request.form.get('custom_price') else None,
            microsoft_tenant_id=request.form.get('microsoft_tenant_id', '').strip(),
            microsoft_subscription_id=request.form.get('microsoft_subscription_id', '').strip(),
            notes=request.form.get('notes', '').strip(),
        )
        db.session.add(sub)
        db.session.commit()
        flash('Abbonamento aggiunto!', 'success')
        return redirect(url_for('subscriptions'))
    return render_template('subscriptions/add.html', customers=customers_list, licenses=licenses)


@app.route('/subscriptions/<int:id>/edit', methods=['GET', 'POST'])
@login_required
def subscription_edit(id):
    sub = Subscription.query.get_or_404(id)
    customers_list = Customer.query.filter_by(is_active=True).order_by(Customer.last_name).all()
    licenses = LicenseType.query.filter_by(is_active=True).order_by(LicenseType.name).all()
    if request.method == 'POST':
        sub.customer_id = int(request.form.get('customer_id'))
        sub.license_type_id = int(request.form.get('license_type_id'))
        sub.quantity = int(request.form.get('quantity', 1))
        sub.start_date = datetime.strptime(request.form.get('start_date'), '%Y-%m-%d').date()
        sub.end_date = datetime.strptime(request.form.get('end_date'), '%Y-%m-%d').date()
        sub.billing_cycle = request.form.get('billing_cycle', 'yearly')
        sub.custom_price = float(request.form.get('custom_price')) if request.form.get('custom_price') else None
        sub.status = request.form.get('status', 'active')
        sub.auto_renew = 'auto_renew' in request.form
        sub.microsoft_tenant_id = request.form.get('microsoft_tenant_id', '').strip()
        sub.microsoft_subscription_id = request.form.get('microsoft_subscription_id', '').strip()
        sub.notes = request.form.get('notes', '').strip()
        db.session.commit()
        flash('Abbonamento aggiornato!', 'success')
        return redirect(url_for('subscriptions'))
    return render_template('subscriptions/edit.html', sub=sub, customers=customers_list, licenses=licenses)


@app.route('/subscriptions/<int:id>/delete', methods=['POST'])
@login_required
def subscription_delete(id):
    sub = Subscription.query.get_or_404(id)
    sub.status = 'cancelled'
    db.session.commit()
    flash('Abbonamento annullato.', 'info')
    return redirect(url_for('subscriptions'))


@app.route('/subscriptions/<int:id>/send-reminder', methods=['POST'])
@login_required
def send_manual_reminder(id):
    from email_service import send_subscription_reminder
    sub = Subscription.query.get_or_404(id)
    days = sub.days_to_expiry
    months = max(1, days // 30)
    results = send_subscription_reminder(sub, months)
    ok_count = sum(1 for _, ok, _ in results if ok)
    flash(f'Promemoria inviato a {ok_count}/{len(results)} destinatari.', 'success' if ok_count else 'danger')
    return redirect(request.referrer or url_for('subscriptions'))


# ── Expiring report ───────────────────────────────────────────────────────────

@app.route('/reports/expiring')
@login_required
def report_expiring():
    today = date.today()
    days_filter = request.args.get('days', 180, type=int)
    subs = (Subscription.query
            .join(Customer).join(LicenseType)
            .filter(Subscription.status == 'active',
                    Subscription.end_date >= today,
                    Subscription.end_date <= today + timedelta(days=days_filter))
            .order_by(Subscription.end_date.asc()).all())
    return render_template('reports/expiring.html', subs=subs, days_filter=days_filter, today=today)


# ── Excel Import/Export ───────────────────────────────────────────────────────

@app.route('/export/customers')
@login_required
def export_customers():
    customers_list = Customer.query.filter_by(is_active=True).order_by(Customer.last_name).all()
    buf = export_customers_excel(customers_list)
    fname = f'avr_clienti_{datetime.now().strftime("%Y%m%d_%H%M")}.xlsx'
    return send_file(buf, mimetype='application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                     as_attachment=True, download_name=fname)


@app.route('/export/subscriptions')
@login_required
def export_subscriptions():
    subs = (Subscription.query.join(Customer).join(LicenseType)
            .order_by(Subscription.end_date.asc()).all())
    buf = export_subscriptions_excel(subs)
    fname = f'avr_abbonamenti_{datetime.now().strftime("%Y%m%d_%H%M")}.xlsx'
    return send_file(buf, mimetype='application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                     as_attachment=True, download_name=fname)


@app.route('/import/customers', methods=['GET', 'POST'])
@login_required
def import_customers():
    if request.method == 'POST':
        file = request.files.get('file')
        if not file or not file.filename.endswith('.xlsx'):
            flash('Carica un file .xlsx valido.', 'danger')
            return redirect(request.url)

        records, errors = import_customers_excel(file.stream)
        imported = 0
        for r in records:
            if not Customer.query.filter_by(email=r['email'], is_active=True).first():
                db.session.add(Customer(**r))
                imported += 1
        db.session.commit()

        if imported:
            flash(f'{imported} clienti importati con successo!', 'success')
        if errors:
            flash(f'Errori su {len(errors)} righe: ' + '; '.join(errors[:5]), 'warning')
        return redirect(url_for('customers'))

    return render_template('import.html')


# ── Init DB & seed ────────────────────────────────────────────────────────────

def seed_db():
    if not User.query.first():
        admin = User(username='admin', email='admin@avrinformatica.it')
        admin.set_password('avr2024!')
        db.session.add(admin)

    default_licenses = [
        ('Microsoft 365 Business Basic', 'Microsoft 365', 'Include app web + Teams + Exchange', 5.60, 56.40),
        ('Microsoft 365 Business Standard', 'Microsoft 365', 'Include app desktop + Teams + Exchange', 11.70, 117.60),
        ('Microsoft 365 Business Premium', 'Microsoft 365', 'Business Standard + sicurezza avanzata', 20.60, 207.60),
        ('Microsoft 365 Apps for Business', 'Microsoft 365', 'App Office desktop + 1 TB OneDrive', 8.25, 82.80),
        ('Microsoft 365 E3', 'Microsoft 365 Enterprise', 'Licenza enterprise con compliance', 32.00, 384.00),
        ('Microsoft 365 E5', 'Microsoft 365 Enterprise', 'Enterprise + sicurezza + analytics', 54.80, 657.60),
        ('Office 365 E1', 'Office 365', 'App web + Teams + Exchange 50 GB', 7.20, 86.40),
        ('Office 365 E3', 'Office 365', 'App desktop + Teams + Exchange 100 GB', 18.00, 216.00),
        ('Exchange Online Plan 1', 'Exchange', 'Solo posta elettronica 50 GB', 3.40, 40.80),
        ('Exchange Online Plan 2', 'Exchange', 'Posta elettronica 100 GB + archiviazione', 7.00, 84.00),
        ('Teams Essentials', 'Teams', 'Solo Microsoft Teams', 3.30, 39.60),
        ('Azure AD Premium P1', 'Azure', 'Accesso condizionale + MFA', 5.60, 67.20),
    ]

    for name, category, desc, pm, py in default_licenses:
        if not LicenseType.query.filter_by(name=name).first():
            db.session.add(LicenseType(name=name, category=category, description=desc,
                                       price_monthly=pm, price_yearly=py))
    db.session.commit()


with app.app_context():
    db.create_all()
    seed_db()

start_scheduler(app)

if __name__ == '__main__':
    app.run(debug=True, host='0.0.0.0', port=5000)
