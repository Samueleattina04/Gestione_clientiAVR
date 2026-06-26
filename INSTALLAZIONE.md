# Guida all'Installazione — Gestionale A.V.R. Informatica

Guida completa per installare il gestionale licenze Microsoft 365 su Windows.

---

## Requisiti

Prima di iniziare, assicurati di avere installato:

| Software | Versione minima | Download |
|----------|----------------|---------|
| PHP | 8.2 o superiore | https://windows.php.net/download (Thread Safe, x64) |
| Composer | ultima versione | https://getcomposer.org/Composer-Setup.exe |
| MySQL | 8.0 o superiore | https://dev.mysql.com/downloads/installer/ |
| Git | ultima versione | https://git-scm.com/download/win |
| Node.js | 18 o superiore (opzionale) | https://nodejs.org |

---

## Passo 1 — Installa PHP

1. Scarica PHP (versione **Thread Safe x64**) da https://windows.php.net/download
2. Estrai la cartella in `C:\php`
3. Rinomina `php.ini-development` in `php.ini`
4. Apri `php.ini` con Blocco Note e cerca e **decommenta** (rimuovi il `;` davanti) queste righe:
   ```
   extension=curl
   extension=fileinfo
   extension=gd
   extension=mbstring
   extension=mysqli
   extension=openssl
   extension=pdo_mysql
   extension=zip
   ```
5. Aggiungi PHP al PATH di Windows:
   - Cerca "Variabili d'ambiente" nel menu Start
   - Variabili di sistema → `Path` → Modifica → Nuovo → `C:\php`
6. Verifica aprendo un nuovo terminale:
   ```
   php -v
   ```
   Deve mostrare la versione di PHP.

---

## Passo 2 — Installa Composer

1. Scarica e lancia `Composer-Setup.exe`
2. Durante l'installazione punta all'eseguibile `C:\php\php.exe`
3. Verifica:
   ```
   composer -V
   ```

---

## Passo 3 — Installa MySQL

1. Scarica MySQL Installer da https://dev.mysql.com/downloads/installer/
2. Scegli **MySQL Server** durante l'installazione
3. Scegli una password per l'utente `root` e **annotala**
4. Verifica:
   ```
   mysql -u root -p
   ```
   Inserisci la password → se entra, MySQL funziona.

---

## Passo 4 — Scarica il progetto

Apri il terminale (PowerShell o CMD) nella cartella dove vuoi installare il gestionale (es. `C:\`) e lancia:

```bash
git clone https://github.com/samueleattina04/gestione_clientiavr.git
cd gestione_clientiavr
```

---

## Passo 5 — Installa le dipendenze PHP

```bash
composer install
```

Attendi il completamento (scarica tutti i pacchetti necessari).

---

## Passo 6 — Crea il database

Apri MySQL dal terminale:

```bash
mysql -u root -p
```

Poi esegui:

```sql
CREATE DATABASE avr_gestionale CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
EXIT;
```

---

## Passo 7 — Configura il file .env

Copia il file di esempio:

```bash
copy .env.example .env
```

Poi apri `.env` con Blocco Note o qualsiasi editor di testo e compila i campi:

```env
APP_NAME="A.V.R. Informatica"
APP_ENV=local
APP_KEY=
APP_DEBUG=false
APP_URL=http://localhost:8000

# ── Database ──────────────────────────────────────
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=avr_gestionale
DB_USERNAME=root
DB_PASSWORD=TUA_PASSWORD_MYSQL

# ── Sessioni e cache ───────────────────────────────
SESSION_DRIVER=file
SESSION_LIFETIME=480
CACHE_STORE=file

# ── Email SMTP (Gmail) ─────────────────────────────
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=tua@gmail.com
MAIL_PASSWORD=xxxxxxxxxxxxxxxx
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="info@avrinformatica.it"
MAIL_FROM_NAME="A.V.R. Informatica"

# ── Notifiche rivenditore ──────────────────────────
RESELLER_EMAIL=tua@gmail.com
RESELLER_NAME="A.V.R. Informatica"
```

> ⚠️ **MAIL_PASSWORD**: non usare la password Gmail normale. Segui il Passo 8 per ottenere l'App Password.

---

## Passo 8 — Configura l'App Password Gmail

Le email automatiche vengono inviate tramite Gmail. Per farlo serve un'**App Password**:

1. Vai su https://myaccount.google.com
2. Clicca su **Sicurezza**
3. Attiva la **Verifica in due passaggi** (se non già attiva)
4. Torna in Sicurezza → cerca **"Password per le app"**
5. Seleziona: App → **Posta** / Dispositivo → **Windows**
6. Clicca **Genera**
7. Copia la password di 16 caratteri (es. `abcdabcdabcdabcd`)
8. Incollala nel `.env` come `MAIL_PASSWORD=abcdabcdabcdabcd` **(senza spazi)**

---

## Passo 9 — Genera la chiave e le tabelle

```bash
php artisan key:generate
php artisan migrate --seed
```

Il comando `--seed` crea automaticamente:
- Le **12 licenze Microsoft 365** pre-caricate
- L'utente amministratore

---

## Passo 10 — Avvia il gestionale

```bash
php artisan serve
```

Apri il browser su: **http://localhost:8000**

**Credenziali di accesso:**
- Email: `admin@avrinformatica.it`
- Password: `avr2024!`

> ⚠️ Cambia la password dopo il primo accesso dal database MySQL:
> ```sql
> UPDATE users SET password = '$2y$12$NUOVA_HASH' WHERE email = 'admin@avrinformatica.it';
> ```
> Oppure chiedi al tecnico di aggiungere una funzione di cambio password.

---

## Passo 11 — Sostituisci i loghi

Copia i file immagine nella cartella `public\img\`:

| File | Utilizzo |
|------|---------|
| `logo.png` | Icona quadrata nella sidebar e favicon |
| `banner.png` | Banner orizzontale nella login e nelle email |

---

## Email automatiche — Come funzionano

Il sistema invia email automaticamente **senza nessuna configurazione aggiuntiva**:

- Ogni volta che si apre il gestionale, l'app verifica le scadenze
- Se un abbonamento sta per scadere, invia automaticamente:
  - **6 mesi prima** → email di preavviso
  - **1 mese prima** → email di avviso
  - **1 settimana prima** → email urgente
- Ogni email viene inviata **una sola volta** per evitare duplicati
- Ricevi una copia tu (rivenditore) e una il cliente

**Requisito:** il gestionale deve essere aperto almeno una volta al giorno.

---

## Installazione su server con IIS (produzione)

Se vuoi hostare il gestionale su un server Windows con IIS:

### 1. Installa PHP per IIS
Usa **Web Platform Installer** o scarica PHP e configuralo come FastCGI in IIS.

### 2. Configura il sito IIS
- Il **Document Root** deve puntare alla cartella `public\` del progetto, non alla radice
- Esempio: se il progetto è in `C:\inetpub\gestionale`, il percorso fisico del sito IIS è `C:\inetpub\gestionale\public`

### 3. Aggiungi il file web.config
Crea il file `public\web.config` con questo contenuto:

```xml
<?xml version="1.0" encoding="UTF-8"?>
<configuration>
  <system.webServer>
    <rewrite>
      <rules>
        <rule name="Laravel Routes" stopProcessing="true">
          <match url="^(.*)$" />
          <conditions>
            <add input="{REQUEST_FILENAME}" matchType="IsFile" negate="true" />
            <add input="{REQUEST_FILENAME}" matchType="IsDirectory" negate="true" />
          </conditions>
          <action type="Rewrite" url="index.php/{R:1}" />
        </rule>
      </rules>
    </rewrite>
  </system.webServer>
</configuration>
```

### 4. Aggiorna il .env per la produzione
```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://tuodominio.it
```

### 5. Ottimizza per la produzione
```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### 6. Logo nelle email in produzione
Con `APP_URL` impostato al dominio reale, il logo apparirà automaticamente nelle email. Non serve nessuna configurazione aggiuntiva.

---

## Risoluzione problemi comuni

### "could not find driver" (SQLite)
Il file `.env` ha ancora `DB_CONNECTION=sqlite`. Assicurati che sia `DB_CONNECTION=mysql`.

### "Table sessions doesn't exist"
Il file `.env` ha `SESSION_DRIVER=database`. Cambia in `SESSION_DRIVER=file`.

### Variabili duplicate nel .env
Se hai la stessa variabile due volte, PHP usa l'ultima. Rimuovi i duplicati.

### "Failed to parse dotenv file. Encountered unexpected whitespace"
L'App Password Gmail è stata incollata con spazi. Rimuovili: `abcd abcd abcd abcd` → `abcdabcdabcdabcd`.

### Email non arrivano
1. Verifica che `MAIL_USERNAME` e `MAIL_PASSWORD` siano corretti nel `.env`
2. Esegui `php artisan config:clear` dopo ogni modifica al `.env`
3. Testa con: `php artisan subscriptions:check-expiring`
4. Controlla la cartella Spam del destinatario

---

## Aggiornamenti futuri

Per aggiornare il gestionale a una versione più recente:

```bash
git pull origin main
composer install
php artisan migrate
php artisan config:cache
```

---

*Gestionale sviluppato da **Samuele Attinà** per A.V.R. Informatica*
