// blog-router.js
// Handles in-site blog navigation: intercept .entrada-link clicks, fetch entrada.php, replace <main id="blog-main"> and pushState
(function(){
    // Disable browser's automatic scroll restoration for SPA navigation
    if ('scrollRestoration' in history) {
        history.scrollRestoration = 'manual';
    }
    function findProjectPrefix(){
        // Preserve project folder for WAMP (e.g. /yaninaparisi)
        var parts = location.pathname.split('/').filter(Boolean);
        // If site is served in a subfolder, the first segment is the project root.
        // We will return '/<root>' (or empty string if on root).
        if (parts.length > 0) {
            // Heuristic: if first part is 'ca' or 'es' then no project prefix
            if (parts[0] === 'ca' || parts[0] === 'es') return '';
            return '/' + parts[0];
        }
        return '';
    }

    function extractMainHtml(htmlText){
        var parser = new DOMParser();
        var doc = parser.parseFromString(htmlText, 'text/html');
        var main = doc.querySelector('main');
        var mainHtml = main ? main.innerHTML : '';
        // Detect an entry hero anywhere in the fetched document, not only inside <main>.
        // This is important because some templates render the hero outside <main> (full-width).
        var hasEntryHero = false;
        var entryHeroHtml = '';
        try {
            var heroEl = doc.querySelector('.entry-hero');
            hasEntryHero = !!heroEl;
            if (hasEntryHero) entryHeroHtml = heroEl.outerHTML;
        } catch(e) { hasEntryHero = false; }
        var title = (doc.querySelector('title')||{}).textContent||'';
        var description = '';
        var metaDesc = doc.querySelector('meta[name="description"]');
        if (metaDesc && metaDesc.getAttribute) description = metaDesc.getAttribute('content') || '';
        return { mainHtml: mainHtml, title: title, description: description, hasEntryHero: hasEntryHero, entryHeroHtml: entryHeroHtml };
    }

    function replaceMainContent(html){
        var container = document.querySelector('main#blog-main');
        if (!container) return;
        var parsed = extractMainHtml(html);
        // If the fetched document contains an entry hero, remove any top-level hero outside <main>
        if (parsed.hasEntryHero) {
            document.querySelectorAll('.hero').forEach(function(h){
                if (!h.closest('main')) {
                    h.parentNode && h.parentNode.removeChild(h);
                }
            });
            // If the fetched entry hero was rendered outside <main> (full-width), insert it into the document
            if (parsed.entryHeroHtml) {
                // Insert before the container (so it sits above the main content)
                var frag = document.createRange().createContextualFragment(parsed.entryHeroHtml);
                container.parentNode.insertBefore(frag, container);
            }
        }

        if (parsed.mainHtml) container.innerHTML = parsed.mainHtml;
        if (parsed.title) document.title = parsed.title;
        // Update meta description if present
        if (parsed.description) {
            var meta = document.querySelector('meta[name="description"]');
            if (meta) meta.setAttribute('content', parsed.description);
        }
        // Blur any focused element to avoid scroll restoration
        if (document.activeElement && typeof document.activeElement.blur === 'function') {
            document.activeElement.blur();
        }
        // Remove autofocus from any element in the new content
        setTimeout(function() {
            var autoEls = document.querySelectorAll('[autofocus]');
            autoEls.forEach(function(el){ el.removeAttribute('autofocus'); });
        }, 10);
        // Workaround: if page is too short to scroll, add a spacer, scroll, then remove it
        function robustScrollTop(attempts) {
            var needSpacer = (document.documentElement.scrollHeight <= window.innerHeight + 5);
            var spacer = null;
            if (needSpacer) {
                spacer = document.createElement('div');
                spacer.style.height = '120vh';
                spacer.style.pointerEvents = 'none';
                spacer.style.opacity = '0';
                document.body.appendChild(spacer);
            }
            window.scrollTo({ top: 0, left: 0, behavior: 'smooth' });
            if (attempts > 1) {
                setTimeout(function(){ robustScrollTop(attempts-1); }, 60);
            } else if (spacer) {
                setTimeout(function(){ document.body.removeChild(spacer); }, 200);
            }
        }
        robustScrollTop(5);
        // Optionally re-run any client-side inits for new content
        if (window.afterBlogReplace && typeof window.afterBlogReplace === 'function') {
            window.afterBlogReplace();
        }
    }

    function fetchAndReplace(url, pushUrl){
    // (No scroll here: only after content is loaded)
        fetch(url, { credentials: 'same-origin' }).then(function(resp){
            if (!resp.ok) throw new Error('Network error');
            return resp.text();
        }).then(function(text){
            replaceMainContent(text);
            if (pushUrl) history.pushState({url: pushUrl}, '', pushUrl);
        }).catch(function(err){
            console.error('Error loading entry:', err);
            // Fallback: full navigation
            location.href = url;
        });
    }

    function onLinkClick(e){
        var a = e.target.closest('a.entrada-link');
        if (!a) return;
        // Only intercept same-origin
        if (a.target === '_blank' || a.hasAttribute('download')) return;
        e.preventDefault();
        var href = a.getAttribute('href');
        if (!href) return;
        // Build pretty url if possible: keep project prefix and language/blog path
        var slugMatch = href.match(/[?&]slug=([^&]+)/);
        var idMatch = href.match(/[?&]id=(\d+)/);
        // Compute prefix (project folder) and language segment robustly for both root and subfolder installs
        var pathParts = location.pathname.split('/').filter(Boolean);
        var prefix = '';
        var langSegment = '';
        if (pathParts.length > 0) {
            if (pathParts[0] === 'ca' || pathParts[0] === 'es') {
                // URL like /ca/...
                prefix = '';
                langSegment = '/' + pathParts[0];
            } else if (pathParts.length > 1 && (pathParts[1] === 'ca' || pathParts[1] === 'es')) {
                // URL like /project/ca/...
                prefix = '/' + pathParts[0];
                langSegment = '/' + pathParts[1];
            } else {
                // Fallback: try to find ca/es anywhere
                var found = pathParts.find(function(p){ return p === 'ca' || p === 'es'; });
                if (found) langSegment = '/' + found;
            }
        }
        var pretty = null;
        if (slugMatch) {
            // New pretty URL format: /<prefix>/<lang>/<slug>   (e.g. /yaninaparisi/ca/mi-slug)
            pretty = prefix + langSegment + '/' + decodeURIComponent(slugMatch[1]);
        } else if (idMatch) {
            pretty = prefix + langSegment + '/' + idMatch[1];
        }
        fetchAndReplace(href, pretty);
    }

    function onPopState(e){
        var state = e.state;
        // If state contains url, fetch it; otherwise fall back to location
        var url = (state && state.url) ? state.url : location.pathname + location.search;
        // If url appears to be a pretty entry path, allow an optional project prefix: /<prefix>/ca/slug or /ca/slug
        var m = url.match(/^\/(?:([^\/]+)\/)?(ca|es)\/([^\/]+)\/?$/);
        if (m) {
            // m[1] = optional prefix (project folder), m[2] = lang, m[3] = slug
            var lang = m[2];
            var slug = m[3];
            var translated = '/' + lang + '/entrada.php?slug=' + encodeURIComponent(slug);
            // If there was a prefix, prepend it to the translated path so fetch goes to correct folder
            if (m[1]) translated = '/' + m[1] + translated;
            fetchAndReplace(translated, null);
            return;
        }
        // Otherwise treat as normal
        fetchAndReplace(url, null);
    }

    function initRouter(){
        document.addEventListener('click', onLinkClick);
        window.addEventListener('popstate', onPopState);
    }

    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', initRouter); else initRouter();
})();
