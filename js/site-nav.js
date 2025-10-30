// site-nav.js
// Centralized navigation scripts: language switch, mobile menu toggle, header scrolled
(function(){
    function initSiteNav(){
        // Language buttons
        document.querySelectorAll('.lang-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                var lang = this.getAttribute('data-lang');
                // Remove active from all
                document.querySelectorAll('.lang-btn').forEach(b => b.classList.remove('active'));
                document.querySelectorAll('.lang-btn[data-lang="' + lang + '"]').forEach(b => b.classList.add('active'));
                // Close mobile menu if open
                var mobileMenuToggle = document.querySelector('.mobile-menu-toggle');
                var navMenu = document.querySelector('.nav-menu ul');
                if (mobileMenuToggle && navMenu) {
                    mobileMenuToggle.classList.remove('active');
                    navMenu.classList.remove('show');
                }
                // Call global changeLanguage if available
                if (typeof changeLanguage === 'function') changeLanguage(lang);
            });
        });

        // Mobile menu toggle
        var mobileMenuToggle = document.querySelector('.mobile-menu-toggle');
        var navMenu = document.querySelector('.nav-menu ul');
        if (mobileMenuToggle && navMenu) {
            mobileMenuToggle.addEventListener('click', function() {
                this.classList.toggle('active');
                navMenu.classList.toggle('show');
            });

            // Close menu when clicking a link
            document.querySelectorAll('.nav-menu ul li a').forEach(link => {
                link.addEventListener('click', function() {
                    mobileMenuToggle.classList.remove('active');
                    navMenu.classList.remove('show');
                });
            });

            // Close when clicking outside
            document.addEventListener('click', function(e) {
                if (!mobileMenuToggle.contains(e.target) && !navMenu.contains(e.target)) {
                    mobileMenuToggle.classList.remove('active');
                    navMenu.classList.remove('show');
                }
            });
        }

        // Header scrolled class
        window.addEventListener('scroll', function() {
            var header = document.querySelector('header');
            if (!header) return;
            if (window.scrollY > 50) {
                header.classList.add('scrolled');
            } else {
                header.classList.remove('scrolled');
            }
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initSiteNav);
    } else {
        initSiteNav();
    }
})();
