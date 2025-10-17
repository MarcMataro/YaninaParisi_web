// Funció per canviar d'idioma i redirigir a la mateixa pàgina en l'altre idioma
function changeLanguage(lang) {
    console.log('Canviant idioma a:', lang);
    var filename = window.location.pathname.split('/').pop();
    if (lang === 'es') {
        window.location.href = '../es/' + filename;
    } else if (lang === 'ca') {
        window.location.href = '../ca/' + filename;
    }
}
