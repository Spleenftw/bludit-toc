/* Bludit ToC Plugin — toc.js
   Reads config from window.BLTOC (injected inline by plugin.php).
   No dependencies; works with any Bludit theme that wraps the article
   body in a .content element (Bludit's default page template does this). */
(function () {
    'use strict';

    var cfg = window.BLTOC || {};
    var TITLE         = cfg.title        || 'On this page';
    var NAVBAR_HEIGHT = cfg.navbarHeight != null ? cfg.navbarHeight : 80;
    var MIN_WIDTH     = cfg.minWidth     != null ? cfg.minWidth     : 1280;

    /* ---- Inject the dynamic min-width media query for the sidebar ---- */
    var styleEl = document.createElement('style');
    styleEl.textContent =
        '@media (min-width: ' + MIN_WIDTH + 'px) { .bltoc-sidebar.bltoc-ready { display: block; } }' +
        '@media (max-width: ' + (MIN_WIDTH - 1) + 'px) { .bltoc-fab.bltoc-ready { display: flex; } }';
    document.head.appendChild(styleEl);

    /* ---- Find the article content area ---- */
    var content = document.querySelector('.content')
               || document.querySelector('article .entry-content')
               || document.querySelector('.entry-content')
               || document.querySelector('.post-content')
               || document.querySelector('article')
               || document.querySelector('main');

    if (!content) return;

    /* ---- Collect headings ---- */
    var headings = content.querySelectorAll('h2, h3, h4');
    if (!headings.length) return;

    /* ---- Assign stable IDs to any heading that lacks one ---- */
    var usedIds = {};
    Array.prototype.forEach.call(headings, function (h) {
        if (h.id) {
            usedIds[h.id] = true;
        } else {
            var base = h.textContent.trim().toLowerCase()
                .replace(/[^a-z0-9\s-]/g, '')
                .replace(/\s+/g, '-')
                .replace(/^-+|-+$/g, '') || 'heading';
            var id = base, n = 2;
            while (usedIds[id]) { id = base + '-' + (n++); }
            usedIds[id] = true;
            h.id = id;
        }
        /* Offset anchor jumps so the heading clears the fixed navbar */
        h.style.scrollMarginTop = NAVBAR_HEIGHT + 'px';
    });

    /* ---- Build the <ul> list ---- */
    var ul = document.createElement('ul');
    ul.className = 'bltoc-list';

    Array.prototype.forEach.call(headings, function (h) {
        var li = document.createElement('li');
        li.className = 'bltoc-item bltoc-' + h.tagName.toLowerCase();

        var a = document.createElement('a');
        a.href = '#' + h.id;
        a.textContent = h.textContent;
        a.className = 'bltoc-link';

        li.appendChild(a);
        ul.appendChild(li);
    });

    /* ---- Populate #bltoc-nav and reveal the sidebar ---- */
    var nav = document.getElementById('bltoc-nav');
    if (nav) {
        nav.appendChild(ul);
    }

    var sidebar = document.getElementById('bltoc-sidebar');
    if (sidebar) {
        sidebar.classList.add('bltoc-ready');
    }

    /* ---- Scroll-spy: highlight the last heading above the viewport fold ---- */
    var headingArr = Array.prototype.slice.call(headings);

    function updateActive() {
        var scrollY    = window.scrollY || window.pageYOffset;
        var threshold  = scrollY + NAVBAR_HEIGHT + 16; /* small extra gap */
        var active     = null;

        headingArr.forEach(function (h) {
            if (h.getBoundingClientRect().top + scrollY <= threshold) {
                active = h;
            }
        });

        if (!nav) return;
        var links = nav.querySelectorAll('.bltoc-link');
        Array.prototype.forEach.call(links, function (a) {
            a.classList.remove('active');
        });
        if (active) {
            var link = nav.querySelector('a[href="#' + active.id + '"]');
            if (link) link.classList.add('active');
        }
    }

    window.addEventListener('scroll', updateActive, { passive: true });
    updateActive();

    /* ---- Mobile: floating button + bottom drawer ---- */
    var fab = document.createElement('button');
    fab.type = 'button';
    fab.className = 'bltoc-fab bltoc-ready';
    fab.setAttribute('aria-label', TITLE);
    fab.innerHTML =
        '<svg viewBox="0 0 24 24" width="20" height="20" fill="none" ' +
        'stroke="currentColor" stroke-width="2" stroke-linecap="round" ' +
        'stroke-linejoin="round" aria-hidden="true">' +
        '<path d="M8 6h13M8 12h13M8 18h13M3 6h.01M3 12h.01M3 18h.01"/>' +
        '</svg>';

    var drawer = document.createElement('div');
    drawer.className = 'bltoc-drawer';
    drawer.setAttribute('role', 'dialog');
    drawer.setAttribute('aria-modal', 'true');
    drawer.setAttribute('aria-label', TITLE);

    var panel = document.createElement('div');
    panel.className = 'bltoc-drawer-panel';

    var drawerTitle = document.createElement('div');
    drawerTitle.className = 'bltoc-drawer-title';
    drawerTitle.textContent = TITLE;

    var clone = ul.cloneNode(true);

    panel.appendChild(drawerTitle);
    panel.appendChild(clone);
    drawer.appendChild(panel);

    document.body.appendChild(fab);
    document.body.appendChild(drawer);

    function openDrawer() {
        drawer.classList.add('is-open');
        document.body.style.overflow = 'hidden';
        fab.setAttribute('aria-expanded', 'true');
    }

    function closeDrawer() {
        drawer.classList.remove('is-open');
        document.body.style.overflow = '';
        fab.setAttribute('aria-expanded', 'false');
    }

    fab.setAttribute('aria-expanded', 'false');
    fab.addEventListener('click', openDrawer);

    /* Close when clicking the backdrop */
    drawer.addEventListener('click', function (e) {
        if (e.target === drawer) closeDrawer();
    });

    /* Close when a drawer link is followed */
    Array.prototype.forEach.call(clone.querySelectorAll('a'), function (a) {
        a.addEventListener('click', closeDrawer);
    });

    /* Close on Escape */
    document.addEventListener('keydown', function (e) {
        if ((e.key === 'Escape' || e.key === 'Esc') && drawer.classList.contains('is-open')) {
            closeDrawer();
            fab.focus();
        }
    });
})();
