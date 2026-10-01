# Rilascio

1. Aggiorna lo stesso numero di versione in tre punti:
   - `siquis-recesso.php`: header `Version:` e costante `SIQUIS_RECESSO_VERSION`
   - `readme.txt`: `Stable tag:`
2. Commit e push su `main`.
3. Crea e pubblica il tag:

   ```bash
   git tag v1.0.1
   git push origin v1.0.1
   ```

La Action `Release` controlla che le versioni coincidano con il tag, crea `package.zip`
(cartella `siquis-recesso/`) e pubblica la release. I siti vedono l'aggiornamento
entro 12 ore in Bacheca > Aggiornamenti (o subito con "Controlla aggiornamenti" nella lista plugin).

Installazione su un nuovo sito: scarica `package.zip` dall'ultima release e caricalo da Plugin > Aggiungi nuovo > Carica plugin.
