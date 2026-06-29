# Guida all'Installazione — Gestionale A.V.R. Informatica
### Server Linux · host.it Reseller · Dominio su Filehost

---

## Versioni richieste

| Software | Versione minima | Consigliata |
|----------|----------------|-------------|
| PHP | **8.2** | 8.3 |
| MySQL | **8.0** | 8.0 |
| Composer | 2.x | ultima |
| Git | qualsiasi | ultima |
| Web server | Apache 2.4 o Nginx | Apache 2.4 |

---

## Panoramica del processo

1. Acquisto dominio su Filehost
2. Configurazione server Linux su host.it
3. Installazione software (PHP, MySQL, Apache, Composer, Git)
4. Download e configurazione del gestionale
5. Configurazione email SMTP Microsoft
6. Primo accesso

---

## Passo 1 — Acquisto dominio su Filehost

1. Vai su **filehost.it** e cerca il dominio desiderato (es. `avrgestionale.it`)
2. Acquistalo e vai nel pannello di gestione DNS
3. Crea un record **A** che punta all'IP del tuo server host.it:
   ```
   Tipo: A
   Nome: @ (o il sottodominio, es. gestionale)
   Valore: IP_DEL_TUO_SERVER
   TTL: 3600
   ```
4. Se usi un sottodominio (es. `gestionale.avrinformatica.it`), crea il record A con nome `gestionale`

> ⏱️ La propagazione DNS può richiedere da 15 minuti a 24 ore.

---

## Passo 2 — Accesso al server host.it

Accedi al server tramite SSH dal terminale:

```bash
ssh root@IP_DEL_TUO_SERVER
```

Oppure usa le credenziali che ti ha fornito host.it nel pannello reseller.

---

## Passo 3 — Aggiornamento sistema

Prima di tutto aggiorna il sistema:

```bash
apt update && apt upgrade -y
```

---

## Passo 4 — Installazione Apache

```bash
apt install -y apache2
systemctl enable apache2
systemctl start apache2
```

Abilita i moduli necessari per Laravel:

```bash
a2enmod rewrite headers expires deflate
systemctl restart apache2
```

Verifica aprendo `http://IP_DEL_TUO_SERVER` nel browser — deve apparire la pagina di default di Apache.

---

## Passo 5 — Installazione PHP 8.3

```bash
apt install -y software-properties-common
add-apt-repository ppa:ondrej/php -y
apt update
apt install -y php8.3 php8.3-cli php8.3-fpm php8.3-mysql php8.3-mbstring \
    php8.3-xml php8.3-curl php8.3-zip php8.3-gd php8.3-bcmath php8.3-intl \
    libapache2-mod-php8.3
```

Verifica:

```bash
php -v
```

Deve mostrare `PHP 8.3.x`.

---

## Passo 6 — Installazione MySQL 8.0

```bash
apt install -y mysql-server
systemctl enable mysql
systemctl start mysql
```

Esegui la configurazione sicura:

```bash
mysql_secure_installation
```

Rispondi alle domande:
- **Validate password plugin?** → `n`
- **Set root password?** → `y` → inserisci una password sicura e **annotala**
- **Remove anonymous users?** → `y`
- **Disallow root login remotely?** → `y`
- **Remove test database?** → `y`
- **Reload privilege tables?** → `y`

Crea il database per il gestionale:

```bash
mysql -u root -p
```

Poi dentro MySQL:

```sql
CREATE DATABASE avr_gestionale CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'avr_user'@'localhost' IDENTIFIED BY 'ScegliunaPasswordSicura!';
GRANT ALL PRIVILEGES ON avr_gestionale.* TO 'avr_user'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

---

## Passo 7 — Installazione Composer

```bash
curl -sS https://getcomposer.org/installer | php
mv composer.phar /usr/local/bin/composer
chmod +x /usr/local/bin/composer
```

Verifica:

```bash
composer -V
```

---

## Passo 8 — Installazione Git

```bash
apt install -y git
```

---

## Passo 9 — Download del gestionale

Posizionati nella cartella web e scarica il progetto:

```bash
cd /var/www
git clone https://github.com/samueleattina04/gestione_clientiavr.git gestionale
cd gestionale
```

Installa le dipendenze PHP:

```bash
composer install --optimize-autoloader --no-dev
```

---

## Passo 10 — Configurazione .env

Copia il file di configurazione:

```bash
cp .env.example .env
```

Modifica il file:

```bash
nano .env
```

Compila con i tuoi dati:

```env
APP_NAME="A.V.R. Informatica"
APP_ENV=production
APP_KEY=
APP_DEBUG=false
APP_URL=https://tuodominio.it

APP_LOCALE=it
APP_FALLBACK_LOCALE=it

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=avr_gestionale
DB_USERNAME=avr_user
DB_PASSWORD=ScegliunaPasswordSicura!

SESSION_DRIVER=file
SESSION_LIFETIME=480
CACHE_STORE=file

MAIL_MAILER=smtp
MAIL_HOST=smtp.office365.com
MAIL_PORT=587
MAIL_USERNAME=tua@email.it
MAIL_PASSWORD=tuapassword
MAIL_ENCRYPTION=starttls
MAIL_FROM_ADDRESS="tua@email.it"
MAIL_FROM_NAME="A.V.R. Informatica"

RESELLER_EMAIL=tua@email.it
RESELLER_NAME="A.V.R. Informatica"
```

Salva con **Ctrl+X → Y → Invio**.

Genera la chiave dell'app:

```bash
php artisan key:generate
```

---

## Passo 11 — Database e dati iniziali

```bash
php artisan migrate --seed
```

Questo crea tutte le tabelle e inserisce:
- Le 12 licenze Microsoft 365 pre-caricate
- L'utente amministratore

---

## Passo 12 — Permessi cartelle

```bash
chown -R www-data:www-data /var/www/gestionale
chmod -R 755 /var/www/gestionale
chmod -R 775 /var/www/gestionale/storage
chmod -R 775 /var/www/gestionale/bootstrap/cache
```

---

## Passo 13 — Configurazione Apache (Virtual Host)

Crea il file di configurazione del sito:

```bash
nano /etc/apache2/sites-available/gestionale.conf
```

Incolla questo contenuto (sostituisci `tuodominio.it` con il tuo dominio):

```apache
<VirtualHost *:80>
    ServerName tuodominio.it
    ServerAlias www.tuodominio.it
    DocumentRoot /var/www/gestionale/public

    <Directory /var/www/gestionale/public>
        Options -Indexes +FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>

    ErrorLog ${APACHE_LOG_DIR}/gestionale_error.log
    CustomLog ${APACHE_LOG_DIR}/gestionale_access.log combined
</VirtualHost>
```

Salva con **Ctrl+X → Y → Invio**.

Abilita il sito e riavvia Apache:

```bash
a2ensite gestionale.conf
a2dissite 000-default.conf
systemctl restart apache2
```

---

## Passo 14 — Certificato SSL (HTTPS gratuito)

Installa Certbot per il certificato SSL gratuito Let's Encrypt:

```bash
apt install -y certbot python3-certbot-apache
certbot --apache -d tuodominio.it -d www.tuodominio.it
```

Segui le istruzioni a schermo. Certbot configurerà HTTPS automaticamente.

Aggiorna `APP_URL` nel `.env`:

```bash
nano /var/www/gestionale/.env
# Cambia APP_URL=https://tuodominio.it
```

Poi:

```bash
php artisan config:cache
```

Il certificato si rinnova automaticamente. Per verificare:

```bash
certbot renew --dry-run
```

---

## Passo 15 — Ottimizzazione per produzione

```bash
cd /var/www/gestionale
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

---

## Passo 16 — Loghi

Copia i file immagine nella cartella `public/img/`:

```bash
# Dal tuo PC, carica i file con SCP:
scp logo.png root@IP_SERVER:/var/www/gestionale/public/img/logo.png
scp banner.png root@IP_SERVER:/var/www/gestionale/public/img/banner.png
```

Oppure usa un client FTP/SFTP come **FileZilla**.

---

## Passo 17 — Primo accesso

Apri il browser su `https://tuodominio.it`

**Credenziali di accesso:**
- Email: `admin@avrinformatica.it`
- Password: `avr2024!`

> ⚠️ **Cambia subito la password** dal menu impostazioni (rotellina ⚙️ in basso a sinistra nella sidebar).

---

## Configurazione email SMTP Microsoft (Outlook / Microsoft 365)

Dopo aver effettuato l'accesso, clicca sulla **rotellina ⚙️** accanto al tuo nome nella sidebar e vai su **Configurazione Email SMTP**.

Clicca il pulsante **"Outlook / Microsoft 365"** per precompilare automaticamente i campi, poi:

| Campo | Valore |
|-------|--------|
| Server SMTP | `smtp.office365.com` |
| Porta | `587` |
| Cifratura | `STARTTLS` |
| Email mittente | la tua email Microsoft (es. `info@avrinformatica.it`) |
| Password SMTP | la password del tuo account Microsoft |
| Nome mittente | `A.V.R. Informatica` |

> 💡 **Se hai l'autenticazione a due fattori (MFA) attiva** su Microsoft 365, devi generare una **App Password**:
> 1. Vai su [myaccount.microsoft.com](https://myaccount.microsoft.com)
> 2. Sicurezza → Verifica in due passaggi → App Password
> 3. Crea una nuova App Password e usala al posto della password normale

Clicca **Salva Configurazione**, poi **Invia Email di Test** per verificare che funzioni.

Le email vengono inviate automaticamente ogni giorno alla prima apertura del gestionale senza nessuna configurazione aggiuntiva.

---

## Aggiornamenti futuri

Per aggiornare il gestionale a una nuova versione:

```bash
cd /var/www/gestionale
git pull origin main
composer install --optimize-autoloader --no-dev
php artisan migrate
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

---

## Risoluzione problemi comuni

### Errore 500 dopo l'installazione
```bash
# Controlla i log
tail -f /var/www/gestionale/storage/logs/laravel.log
tail -f /var/log/apache2/gestionale_error.log
```

### Permessi negati su storage
```bash
chmod -R 775 /var/www/gestionale/storage
chown -R www-data:www-data /var/www/gestionale/storage
```

### Email non arrivano
1. Verifica le impostazioni SMTP nella rotellina ⚙️ impostazioni admin
2. Usa il pulsante **"Invia Email di Test"** per diagnosticare
3. Controlla che la porta 587 non sia bloccata dal firewall del server:
   ```bash
   ufw allow out 587
   ```

### Il sito non si apre dopo aver puntato il DNS
La propagazione DNS richiede fino a 24 ore. Verifica che il record A sia corretto su Filehost.

### mod_rewrite non funziona (URL non trovati)
```bash
a2enmod rewrite
systemctl restart apache2
```

---

*Gestionale sviluppato da **Samuele Attinà** per A.V.R. Informatica*
