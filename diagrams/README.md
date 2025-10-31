Arquitectura del lloc — diagrama

Arxius:
- site-architecture.dot — Graphviz DOT que representa l'arquitectura pública (idiomes, pàgines principals, blog i posts)

Com generar una imatge (requereix Graphviz instal·lat):

Windows PowerShell (assumeu que `dot` està en PATH):

```powershell
# des de la carpeta diagrams
cd c:\wamp64\www\yaninaparisi\diagrams
# generar PNG
dot -Tpng site-architecture.dot -o site-architecture.png
# generar SVG
dot -Tsvg site-architecture.dot -o site-architecture.svg
```

Notes Ràpides d'Anàlisi SEO (inicial):
- La pàgina d'inici redirigeix des de `/` (index.php) cap a `/ca/home.php` o `/es/home.php` segons idioma; s'indica hreflang al `sitemap.xml`.
- S'ha implementat JSON-LD (LocalBusiness/Psychologist) a `ca/home.php` i `ca/sobremi.php`; cal revisar i substituir dades fictícies (telèfon, adreça, socials) per evitar informació placeholder.
- El sitemap inclou les principals rutes i hreflang alternates; robots.txt exclou l'àrea d'administració — correcte.
- Revisions recomanades: canonical tags consistents, assegurar HTTPS absolut per Open Graph i JSON-LD; comprovar que no hi hagi pàgines duplicades sense rel=canonical.

Si vols, puc:
- Analitzar automàticament (scan) tots els `ca/*.php` i `es/*.php` per extreure títol, meta description, h1 i rel=canonical i generar un CSV amb resultats.
- Generar el PNG/SVG aquí (si em dones accés a generar imatges) o simplement deixar el DOT perquè el generis localment.
- Fer una auditoria més completa (velocitat, mobile, accesibilitat) amb instruccions i proves.
