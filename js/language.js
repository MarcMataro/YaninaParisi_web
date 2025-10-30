// Funció per canviar d'idioma i redirigir a la mateixa pàgina en l'altre idioma
function changeLanguage(lang) {
    // Enhanced language switch: if we're on an entry page, map slug to target language via API
    try {
        var path = window.location.pathname;
        var parts = path.split('/').filter(Boolean);
        var projectPrefix = '';
        var currentLang = null;
        var currentSlug = null;

        // Detect patterns: /ca/slug  or /project/ca/slug
        if (parts.length >= 2 && (parts[0] === 'ca' || parts[0] === 'es')) {
            projectPrefix = '';
            currentLang = parts[0];
            currentSlug = parts[1];
        } else if (parts.length >= 3 && (parts[1] === 'ca' || parts[1] === 'es')) {
            projectPrefix = '/' + parts[0];
            currentLang = parts[1];
            currentSlug = parts[2];
        }

        // Also handle when on entrada.php?slug=...
        if (!currentSlug) {
            var filename = window.location.pathname.split('/').pop();
            if (filename === 'entrada.php') {
                var params = new URLSearchParams(window.location.search);
                if (params.has('slug')) {
                    currentSlug = params.get('slug');
                    // try to detect lang from path (folder)
                    var p = window.location.pathname.split('/').filter(Boolean);
                    if (p.length >= 2 && (p[0] === 'ca' || p[0] === 'es')) currentLang = p[0];
                    else if (p.length >= 3 && (p[1] === 'ca' || p[1] === 'es')) currentLang = p[1];
                }
            }
        }

        if (currentSlug && currentLang) {
            // ask API for mapping
            var apiBase = projectPrefix + '/api/entrada_map.php';
            var url = apiBase + '?slug=' + encodeURIComponent(currentSlug) + '&lang=' + encodeURIComponent(currentLang);
            fetch(url, { credentials: 'same-origin' }).then(function(resp){
                if (!resp.ok) throw new Error('network');
                return resp.json();
            }).then(function(data){
                if (data && data.success) {
                    var targetSlug = (lang === 'ca') ? data.slug_ca : data.slug_es;
                    var targetId = data.id_entrada;
                    if (targetSlug) {
                        // Prefer friendly URL: /ca/slug o /es/slug
                        var target = projectPrefix + '/' + lang + '/' + encodeURIComponent(targetSlug);
                        window.location.href = target;
                        return;
                    } else if (targetId) {
                        // Fallback: use entrada.php?id=...
                        var target = projectPrefix + '/' + lang + '/entrada.php?id=' + encodeURIComponent(targetId);
                        window.location.href = target;
                        return;
                    }
                }
                // fallback to file-based switch
                fallbackSwitch(lang);
            }).catch(function(){ fallbackSwitch(lang); });
            return;
        }
    } catch (e) {
        // ignore and fallback
    }
    // fallback
    fallbackSwitch(lang);
}

function fallbackSwitch(lang) {
        var filename = window.location.pathname.split('/').pop();
        var params = new URLSearchParams(window.location.search);
        var extra = '';
        if (filename === 'entrada.php') {
            if (params.has('slug')) {
                extra = '?slug=' + encodeURIComponent(params.get('slug'));
            } else if (params.has('id')) {
                extra = '?id=' + encodeURIComponent(params.get('id'));
            }
        }
        if (lang === 'es') {
            window.location.href = '../es/' + filename + extra;
        } else if (lang === 'ca') {
            window.location.href = '../ca/' + filename + extra;
        }
}
