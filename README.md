# A.V.R. Informatica — Gestionale Licenze Microsoft 365

Gestionale web per la gestione clienti e abbonamenti Microsoft 365 / Office 365.

## Funzionalità

- **Gestione Clienti** — anagrafica completa (nome, cognome, email, azienda, P.IVA, ecc.)
- **Gestione Licenze** — catalogo licenze con prezzi mensili/annuali
- **Gestione Abbonamenti** — associa licenze ai clienti con date di inizio/scadenza
- **Promemoria automatici** — email a cliente e fornitore 6 mesi, 1 mese e 1 settimana prima della scadenza
- **Import Excel** — importa clienti da file .xlsx (compatibile con export Microsoft 365 Admin)
- **Export Excel** — esporta clienti e abbonamenti in formato .xlsx formattato
- **Report scadenze** — vista filtrata degli abbonamenti in scadenza

## Requisiti

- Python 3.10+
- Pip

## Installazione

```bash
# 1. Clona il repository
git clone https://github.com/samueleattina04/gestione_clientiavr.git
cd gestione_clientiavr

# 2. Crea ambiente virtuale (consigliato)
python3 -m venv venv
source venv/bin/activate        # Linux/Mac
# oppure: venv\Scripts\activate  # Windows

# 3. Installa dipendenze
pip install -r requirements.txt

# 4. Configura le variabili d'ambiente
cp .env.example .env
nano .env   # modifica con i tuoi dati SMTP e email

# 5. Aggiungi i loghi in static/img/
#    - banner.png  (immagine orizzontale con testo)
#    - logo.png    (immagine per sidebar)
#    - icon.png    (icona quadrata / favicon)

# 6. Avvia l'applicazione
python3 app.py
```

Apri il browser su `http://localhost:5000`

**Credenziali default:**
- Username: `admin`
- Password: `avr2024!`

> ⚠️ **Cambia la password immediatamente dopo il primo accesso!**

## Configurazione Email (.env)

```env
SECRET_KEY=stringa-casuale-molto-lunga
MAIL_SERVER=smtp.gmail.com
MAIL_PORT=587
MAIL_USE_TLS=True
MAIL_USERNAME=tua@email.com
MAIL_PASSWORD=password-app-gmail
MAIL_DEFAULT_SENDER=A.V.R. Informatica <tua@email.com>
RESELLER_EMAIL=info@avrinformatica.it
RESELLER_NAME=A.V.R. Informatica
```

Per Gmail usa una **App Password** (non la password normale):
Impostazioni Google → Sicurezza → Verifica in 2 passaggi → Password per le app

## Produzione (server Linux)

```bash
pip install gunicorn
gunicorn -w 4 -b 0.0.0.0:5000 app:app
```

Con Nginx come reverse proxy, configurare il virtualhost per puntare a `localhost:5000`.

## Struttura progetto

```
├── app.py              # Applicazione Flask principale
├── models.py           # Modelli database (SQLAlchemy)
├── config.py           # Configurazione
├── email_service.py    # Invio email HTML
├── scheduler.py        # Controllo automatico scadenze (ogni giorno ore 8)
├── excel_service.py    # Import/export Excel
├── requirements.txt
├── .env.example
├── static/
│   ├── css/style.css
│   ├── js/main.js
│   └── img/            # Loghi A.V.R. Informatica
└── templates/
    ├── base.html
    ├── login.html
    ├── dashboard.html
    ├── customers/
    ├── subscriptions/
    ├── licenses/
    ├── reports/
    └── import.html
```
