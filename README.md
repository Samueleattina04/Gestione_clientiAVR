# A.V.R. Informatica — Gestionale Licenze Microsoft 365

Gestionale web per la gestione clienti e abbonamenti Microsoft 365 / Office 365.  
Stack: **Laravel · PHP · MySQL · Bootstrap 5 · HTML · CSS · JS**

## Funzionalità

- **Gestione Clienti** — anagrafica completa (nome, cognome, email, azienda, P.IVA, CF, ecc.)
- **Gestione Licenze** — catalogo licenze con prezzi mensili/annuali (12 licenze M365 pre-caricate)
- **Gestione Abbonamenti** — associa licenze ai clienti con date di inizio/scadenza
- **Promemoria automatici** — email HTML a cliente e fornitore: 6 mesi, 1 mese, 1 settimana prima della scadenza
- **Import Excel** — importa clienti da .xlsx (compatibile con export Microsoft 365 Admin Center)
- **Export Excel** — esporta clienti e abbonamenti in .xlsx formattato
- **Report scadenze** — vista filtrata per 30/60/90/180/365 giorni
- **Dashboard** — KPI, ricavi stimati, distribuzione licenze, scadenze imminenti

## Requisiti

- PHP 8.2+
- Composer
- MySQL 8.0+
- Node.js (opzionale, per compilazione asset)

## Installazione

```bash
# 1. Clona il repository
git clone https://github.com/samueleattina04/gestione_clientiavr.git
cd gestione_clientiavr

# 2. Installa dipendenze PHP
composer install

# 3. Copia configurazione
cp .env.example .env
php artisan key:generate

# 4. Configura .env (MySQL, email, ecc.)
nano .env

# 5. Crea il database MySQL
mysql -u root -p -e "CREATE DATABASE avr_gestionale CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

# 6. Esegui migrations e seeder
php artisan migrate --seed

# 7. (Opzionale) Sostituisci i loghi in public/img/
#    banner.png — immagine orizzontale con testo A.V.R. Informatica
#    logo.png   — logo per sidebar
#    icon.png   — icona quadrata / favicon

# 8. Avvia il server
php artisan serve
```

Apri il browser su `http://localhost:8000`

**Credenziali default:**
- Email: `admin@avrinformatica.it`
- Password: `avr2024!`

> ⚠️ **Cambia la password dal database dopo il primo accesso!**

## Configurazione .env

```env
APP_NAME="A.V.R. Informatica"
APP_URL=http://tuodominio.it

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=avr_gestionale
DB_USERNAME=root
DB_PASSWORD=tuapassword

MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=tua@email.com
MAIL_PASSWORD=password-app-gmail
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="info@avrinformatica.it"
MAIL_FROM_NAME="A.V.R. Informatica"

RESELLER_EMAIL=info@avrinformatica.it
RESELLER_NAME="A.V.R. Informatica"
```

## Promemoria automatici (Cron)

Aggiungi questa riga al crontab del server:

```bash
* * * * * cd /percorso/progetto && php artisan schedule:run >> /dev/null 2>&1
```

Il sistema controllerà le scadenze ogni giorno alle **08:00** e invierà email automaticamente.

## Produzione (Apache/Nginx)

- Il document root deve puntare alla cartella `public/`
- Configura `APP_DEBUG=false` e `APP_ENV=production`
- Esegui `php artisan config:cache` e `php artisan route:cache`

```bash
# Con Nginx
server {
    listen 80;
    server_name tuodominio.it;
    root /var/www/gestionale/public;
    index index.php;
    location / { try_files $uri $uri/ /index.php?$query_string; }
    location ~ \.php$ { fastcgi_pass unix:/var/run/php/php8.2-fpm.sock; include fastcgi_params; fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name; }
}
```

## Struttura progetto

```
├── app/
│   ├── Http/Controllers/      # DashboardController, CustomerController, ecc.
│   ├── Models/                # Customer, Subscription, LicenseType, User, EmailLog
│   ├── Exports/               # CustomersExport, SubscriptionsExport
│   ├── Imports/               # CustomersImport
│   ├── Mail/                  # SubscriptionReminder (email HTML)
│   ├── Services/              # EmailReminderService
│   └── Console/Commands/      # CheckExpiringSubscriptions
├── database/
│   ├── migrations/            # 5 tabelle
│   └── seeders/               # 12 licenze M365 + utente admin
├── public/
│   ├── css/style.css          # CSS personalizzato A.V.R. Informatica
│   ├── js/main.js             # JS sidebar, animazioni
│   └── img/                   # Loghi (da sostituire)
├── resources/views/
│   ├── layouts/app.blade.php  # Layout principale con sidebar
│   ├── auth/login.blade.php   # Pagina login
│   ├── dashboard.blade.php
│   ├── customers/             # index, create, show, edit, import
│   ├── licenses/              # index, create, edit
│   ├── subscriptions/         # index, create, edit
│   ├── reports/expiring.blade.php
│   └── emails/subscription_reminder.blade.php
└── routes/web.php
```
