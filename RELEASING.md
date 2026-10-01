# Rilascio

1. Aggiorna lo stesso numero di versione in tre punti:
   - `siquis-recesso.php`: header `Version:` e costante `SIQUIS_RECESSO_VERSION`
   - `readme.txt`: `Stable tag:`
2. Commit delle modifiche.
3. Esegui `./release.sh` (Git Bash). Lo script controlla che le tre versioni coincidano,
   pubblica il tag `vX.Y.Z`, crea `package.zip` (cartella `siquis-recesso/`) e la release su GitHub.

I siti vedono l'aggiornamento entro 12 ore in Bacheca > Aggiornamenti
(o subito con "Controlla aggiornamenti" nella lista plugin).

Installazione su un nuovo sito: scarica `package.zip` dall'ultima release e caricalo da
Plugin > Aggiungi nuovo > Carica plugin.
