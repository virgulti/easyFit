# EasyFit

App personale per tracciare peso corporeo, alimentazione e obiettivi di fitness, giorno per
giorno. Nata come esperimento di sviluppo multi-agente (orchestratore Claude/Sonnet + Antigravity
per il frontend Vue + un modello locale via aider/LM Studio per il lavoro ripetitivo).

## Funzionalità

**Misurazioni corporee**
- Form di inserimento giornaliero (peso, % grasso, % muscolo, BMI, orario e durata del sonno),
  pensato per essere compilato in pochi secondi ogni giorno.
- Metriche derivate: progress, progress BMI, peso adipe, peso muscoli, indicatore di
  miglioramento rispetto alla misurazione precedente.
- Storico completo, editabile, ordinato dal più recente.
- Dashboard con 8 grafici (progress ultimi 5 giorni/5 settimane/6 mesi, storico completo di
  progress/peso/adipe/muscoli/progress BMI) e pagine dedicate a schermo intero per ciascuno.

**Pasti**
- Catalogo personale di pasti riutilizzabili (descrizione, tipo, peso di riferimento, calorie,
  proteine).
- Registrazione di un pasto scegliendolo dal catalogo — con ricalcolo automatico di
  calorie/proteine se si cambia il peso — oppure come "pasto inusuale" con valori inseriti a
  mano (con possibilità di salvarlo nel catalogo per il futuro).
- Vista giornaliera con totali di calorie, proteine e costo.
- Pagina di gestione con lo storico completo di tutti i pasti registrati, modificabili
  singolarmente.
- Costo opzionale per pasto, con totale giornaliero.

**Obiettivi**
- Soglie personalizzabili (% grasso massima, proteine minime al giorno, calorie massime al
  giorno e a settimana), tutte opzionali.
- Confronto visivo (verde/rosso) tra i valori del giorno e le soglie impostate, nascosto quando
  una soglia non è stata definita.

## Stack tecnologico

- **Backend**: Laravel 13, PHP 8.3+, Fortify (autenticazione, 2FA, passkey)
- **Frontend**: Vue 3 + Inertia.js v3, Tailwind CSS 4
- **Routing tipizzato**: Laravel Wayfinder
- **Test**: Pest 4
- **Database**: SQLite (di default)

## Requisiti

- PHP >= 8.3
- Composer
- Node.js + npm
- Laravel Herd (o un altro ambiente locale equivalente)

## Installazione

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate
npm install
npm run build
```

Per popolare il database con dati demo realistici (misurazioni e pasti degli ultimi ~90 giorni):

```bash
php artisan db:seed
```

## Sviluppo

```bash
composer run dev   # server Artisan + queue listener + Vite, in parallelo
```

Oppure, se l'app è servita da Laravel Herd, è sufficiente `npm run dev` per il frontend.

## Test e qualità del codice

```bash
php artisan test --compact       # suite Pest
vendor/bin/pint                  # formattazione PHP
vendor/bin/phpstan analyse       # analisi statica
npm run lint                     # ESLint
npm run types:check              # controllo tipi TypeScript (vue-tsc)
```
