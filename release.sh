#!/bin/sh
# Pubblica una nuova versione: ./release.sh
# Richiede: git, gh (GitHub CLI) autenticato, versione già aggiornata e committata.
set -e
cd "$(dirname "$0")"

HEADER=$(grep -m1 -E '^\s*\*\s*Version:' siquis-recesso.php | sed -E 's/.*Version:\s*//' | tr -d '[:space:]')
CONST=$(grep -m1 "SIQUIS_RECESSO_VERSION'" siquis-recesso.php | sed -E "s/.*'([0-9][^']*)'.*/\1/")
STABLE=$(grep -m1 -E '^Stable tag:' readme.txt | sed -E 's/Stable tag:\s*//' | tr -d '[:space:]')
TAG="v$HEADER"

echo "Header=$HEADER  Costante=$CONST  Stable tag=$STABLE"
[ "$HEADER" = "$CONST" ] && [ "$HEADER" = "$STABLE" ] || { echo "Errore: le tre versioni non coincidono."; exit 1; }
[ -z "$(git status --porcelain)" ] || { echo "Errore: ci sono modifiche non committate."; exit 1; }
git rev-parse "$TAG" >/dev/null 2>&1 && { echo "Errore: il tag $TAG esiste già."; exit 1; }

git push origin main
git tag "$TAG"
git push origin "$TAG"

TMP=$(mktemp -d)
git archive --format=zip --prefix=siquis-recesso/ -o "$TMP/package.zip" "$TAG"
gh release create "$TAG" "$TMP/package.zip" --title "$TAG" --notes "$TAG"
rm -rf "$TMP"
echo "Pubblicata $TAG"
