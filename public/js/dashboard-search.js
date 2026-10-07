// Keep collapsed flyouts beside their icons while the fixed sidebar scrolls.
(() => {
    const sidebar = document.getElementById('sidebar');
    if (!sidebar) return;
    function positionFlyout(event) {
        if (document.documentElement.dataset.sidebar !== 'icon') return;
        const slide = event?.target?.closest?.('.pe-has-sub')
            || sidebar.querySelector('.pe-main-menu > .pe-has-sub:is(:hover, :focus-within)');
        if (!slide || !sidebar.contains(slide)) return;
        const menu = slide.querySelector(':scope > .pe-slide-menu');
        const link = slide.querySelector(':scope > .pe-nav-link');
        if (!menu || !link) return;
        const bounds = link.getBoundingClientRect();
        const height = Math.min(menu.scrollHeight, innerHeight - 32);
        const top = Math.max(16, Math.min(bounds.top, innerHeight - height - 16));
        const sidebarBounds = sidebar.getBoundingClientRect();
        const left = document.documentElement.dir === 'rtl' ? sidebarBounds.left - menu.offsetWidth : sidebarBounds.right;
        menu.style.setProperty('--sidebar-flyout-top', `${top}px`);
        menu.style.setProperty('--sidebar-flyout-left', `${left}px`);
    }
    sidebar.addEventListener('pointerover', positionFlyout);
    sidebar.addEventListener('focusin', positionFlyout);
    sidebar.addEventListener('scroll', positionFlyout, true);
    window.addEventListener('resize', () => positionFlyout());
})();

(() => {
    'use strict';
    const dialog = document.getElementById('dashboard-search');
    if (!dialog) return;
    const input = document.getElementById('dashboard-search-input');
    const list = document.getElementById('dashboard-search-results');
    const status = document.getElementById('dashboard-search-status');
    const filters = [...dialog.querySelectorAll('[data-search-filter]')];
    const isMac = /Mac|iPhone|iPad/.test(navigator.userAgentData?.platform || navigator.platform);
    document.querySelectorAll('[data-search-shortcut]').forEach(node => { node.textContent = isMac ? '⌘ K' : 'Ctrl K'; });
    const scopePath = new URL(dialog.dataset.endpoint, location.href).pathname.replace(/search$/, '');
    const access = JSON.parse(document.getElementById('dashboard-search-access')?.textContent || '{}');
    const normalize = value => value.normalize('NFD').replace(/[\u0300-\u036f]/g, '').toLowerCase().replace(/[_/\-]+/g, ' ').trim();
    const aliases = [
        [/rider|customer/i, 'customers riders passengers people'],
        [/driver/i, 'drivers chauffeurs people'],
        [/manual_booking/i, 'add create new booking dispatch reservation'],
        [/later_booking/i, 'scheduled upcoming reservations bookings'],
        [/vehicle/i, 'fleet cars registration plate'],
        [/fare|fee|surge/i, 'pricing rates tariffs cost'],
        [/statement|data-suite/i, 'reports analytics revenue earnings export'],
        [/account|payment|payout/i, 'billing invoices finance wallet money'],
        [/operation-team/i, 'staff team employees permissions roles'],
        [/settings|site_setting/i, 'preferences configuration account branding'],
        [/support|help/i, 'assistance tickets issues'],
        [/map|service-areas/i, 'locations zones coverage geography'],
    ];
    let navigation = [], records = [], visible = [], active = 0, filter = 'all';
    let pending = false, failed = false, hasMore = false, timer, request, generation = 0;

    function safeUrl(value) {
        try {
            const url = new URL(value, location.href);
            return url.origin === location.origin && url.pathname.startsWith(scopePath) ? url.href : null;
        } catch { return null; }
    }

    function indexNavigation() {
        const seen = new Set();
        navigation = [];
        [...document.querySelectorAll('#sidebar .pe-nav-link'), ...document.querySelectorAll('#appHeader a.dropdown-item, #appHeader .header-dropdown a.btn')].forEach(link => {
            const href = link.getAttribute('href') || '';
            if (!href || href.startsWith('#')) return;
            const url = safeUrl(href);
            if (!url || /\/(logout|delete[^/]*|lang|authenticate)(\/|$)/i.test(new URL(url).pathname) || seen.has(url)) return;
            const title = link.textContent.trim().replace(/\s+/g, ' ');
            if (!title) return;
            seen.add(url);
            const parent = link.closest('.pe-has-sub');
            const section = parent?.querySelector(':scope > .pe-nav-link .pe-nav-content')?.textContent.trim();
            const path = new URL(url).pathname;
            if (access[path] === false) return;
            const icon = parent?.querySelector('.pe-nav-icon') || link.querySelector('i');
            navigation.push({
                id: url, url, title, description: section ? `${section} · Open page` : 'Workspace · Open page',
                group: 'Menus & features', kind: 'navigation', icon: 'bi-grid', iconNode: icon,
                keywords: normalize(`${title} ${section || ''} ${path} ${aliases.filter(([pattern]) => pattern.test(path)).map(([, words]) => words).join(' ')}`),
            });
        });
        const features = JSON.parse(document.getElementById('dashboard-search-features')?.textContent || '[]');
        features.forEach(feature => {
            const url = safeUrl(feature.url);
            // The parent must also be available; disabled modules stay out of search.
            if (!url || seen.has(url) || !navigation.some(item => item.url === feature.parent)) return;
            navigation.push({ ...feature, url, id: url, description: 'Open creation form', group: 'Features', kind: 'navigation', icon: 'bi-plus-circle' });
        });
        const theme = document.querySelector('#toggleMode');
        if (theme) navigation.push({ id: 'appearance', title: 'Switch light / dark appearance', description: 'Workspace preference', group: 'Quick actions', kind: 'navigation', icon: 'bi-moon-stars', keywords: 'theme dark light mode appearance', action: () => theme.querySelector('button.active')?.click() });
        const sidebar = document.getElementById('toggleSidebar');
        if (sidebar) navigation.push({ id: 'sidebar', title: 'Toggle sidebar', description: 'Expand or collapse navigation', group: 'Quick actions', kind: 'navigation', icon: 'bi-arrow-bar-left', keywords: 'sidebar expand collapse navigation menu', action: () => sidebar.click() });
    }

    function matchingNavigation(query) {
        const words = normalize(query).split(/\s+/).filter(Boolean);
        return navigation.filter(item => words.every(word => normalize(item.title + ' ' + item.keywords).includes(word)))
            .sort((a, b) => {
                const score = item => normalize(item.title) === normalize(query) ? 0 : normalize(item.title).startsWith(normalize(query)) ? 1 : 2;
                return score(a) - score(b);
            });
    }

    function highlight(node, value, query) {
        // Build text nodes instead of interpolating account data as HTML.
        const text = String(value);
        const start = text.toLowerCase().indexOf(query.toLowerCase());
        if (!query || start < 0) { node.textContent = text; return; }
        node.append(document.createTextNode(text.slice(0, start)));
        const mark = document.createElement('mark');
        mark.textContent = text.slice(start, start + query.length);
        node.append(mark, document.createTextNode(text.slice(start + query.length)));
    }

    function setActive(index, scroll = false) {
        active = index;
        [...list.querySelectorAll('[role="option"]')].forEach((node, i) => node.setAttribute('aria-selected', String(i === active)));
        const selected = list.querySelector(`[data-result-index="${active}"]`);
        if (selected) {
            input.setAttribute('aria-activedescendant', selected.id);
            if (scroll) selected.scrollIntoView({ block: 'nearest' });
        } else input.removeAttribute('aria-activedescendant');
    }

    function openResult(item) {
        if (!item) return;
        dialog.close();
        if (item.action) item.action();
        else {
            const url = safeUrl(item.url);
            if (url) location.assign(url);
        }
    }

    function render() {
        const query = input.value.trim();
        let menus = matchingNavigation(query);
        if (!query) menus = menus.slice(0, 8);
        visible = [...(filter !== 'record' ? menus : []), ...(filter !== 'navigation' ? records : [])];
        list.replaceChildren();
        let group;
        visible.forEach((item, index) => {
            if (item.group !== group) {
                group = item.group;
                const heading = document.createElement('div');
                heading.className = 'dashboard-search-group';
                heading.setAttribute('role', 'presentation');
                heading.textContent = query ? group : 'Explore your workspace';
                list.append(heading);
            }
            const row = document.createElement('div');
            row.className = 'dashboard-search-result';
            row.id = `dashboard-search-option-${index}`;
            row.dataset.resultIndex = index;
            row.setAttribute('role', 'option');
            const icon = document.createElement('span');
            icon.className = 'dashboard-search-result-icon';
            const glyph = item.iconNode ? item.iconNode.cloneNode(true) : document.createElement('i');
            if (!item.iconNode) glyph.className = `bi ${item.icon || 'bi-search'}`;
            glyph.setAttribute('aria-hidden', 'true');
            icon.append(glyph);
            const copy = document.createElement('span');
            copy.className = 'dashboard-search-result-copy';
            const title = document.createElement('span');
            title.className = 'dashboard-search-result-title';
            highlight(title, item.title, query);
            const detail = document.createElement('span');
            detail.className = 'dashboard-search-result-detail';
            detail.textContent = item.description;
            copy.append(title, detail);
            const arrow = document.createElement('span');
            arrow.className = 'dashboard-search-result-arrow';
            arrow.setAttribute('aria-hidden', 'true');
            arrow.textContent = '↗';
            row.append(icon, copy, arrow);
            row.addEventListener('click', () => openResult(item));
            row.addEventListener('pointermove', () => setActive(index));
            list.append(row);
        });
        const recordsInScope = filter !== 'navigation';
        status.textContent = pending && recordsInScope ? 'Searching account records…'
            : failed && recordsInScope ? 'Account search is unavailable. You can still search menus and features.'
            : !query ? 'Jump to a page, or type a name, phone number, registration or record ID.'
            : query.length < 2 && recordsInScope ? 'Type at least 2 characters to include account records.'
            : `${visible.length} result${visible.length === 1 ? '' : 's'}${hasMore && recordsInScope ? ' · Top matches shown. Keep typing to narrow your search.' : ' · Only your accessible workspace.'}`;
        if (!visible.length) {
            const empty = document.createElement('div');
            empty.className = 'dashboard-search-empty';
            const title = document.createElement('strong');
            title.textContent = pending ? 'Finding your matches…' : query ? 'No matches yet' : 'Find a record in your account';
            const description = document.createElement('span');
            description.textContent = 'Try a menu name, a customer, a booking ID or a vehicle registration.';
            empty.append(title, description);
            list.append(empty);
        }
        setActive(visible.length ? Math.max(0, Math.min(active, visible.length - 1)) : -1);
    }

    function search() {
        clearTimeout(timer);
        request?.abort();
        const revision = ++generation;
        records = []; failed = false; hasMore = false; active = 0;
        const query = input.value.trim();
        pending = query.length >= 2 && filter !== 'navigation';
        render();
        if (!pending) return;
        timer = setTimeout(async () => {
            request = new AbortController();
            try {
                const url = new URL(dialog.dataset.endpoint, location.href);
                url.searchParams.set('q', query);
                const response = await fetch(url, { signal: request.signal, credentials: 'same-origin', headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' }, cache: 'no-store' });
                if (!response.ok) throw new Error('Search unavailable');
                const data = await response.json();
                if (revision !== generation || !dialog.open) return;
                records = (data.results || []).filter(item => safeUrl(item.url));
                hasMore = Boolean(data.hasMore);
            } catch (error) {
                if (revision !== generation || error.name === 'AbortError') return;
                failed = true;
            }
            if (revision === generation && dialog.open) { pending = false; render(); }
        }, 220);
    }

    function open() {
        if (dialog.open) { input.focus(); input.select(); return; }
        indexNavigation();
        input.value = ''; filter = 'all';
        filters.forEach(button => button.setAttribute('aria-pressed', String(button.dataset.searchFilter === filter)));
        dialog.showModal();
        search();
        input.focus();
    }

    document.querySelectorAll('[data-dashboard-search-open]').forEach(button => button.addEventListener('click', open));
    dialog.querySelector('[data-search-close]').addEventListener('click', () => dialog.close());
    dialog.addEventListener('click', event => {
        const rect = dialog.getBoundingClientRect();
        if (event.target === dialog && (event.clientX < rect.left || event.clientX > rect.right || event.clientY < rect.top || event.clientY > rect.bottom)) dialog.close();
    });
    dialog.addEventListener('close', () => { clearTimeout(timer); request?.abort(); generation++; });
    input.addEventListener('input', search);
    input.addEventListener('keydown', event => {
        if (['ArrowDown', 'ArrowUp'].includes(event.key)) {
            event.preventDefault();
            if (visible.length) setActive((active + (event.key === 'ArrowDown' ? 1 : -1) + visible.length) % visible.length, true);
        } else if (event.key === 'Enter') { event.preventDefault(); openResult(visible[active]); }
    });
    filters.forEach(button => button.addEventListener('click', () => {
        filter = button.dataset.searchFilter;
        filters.forEach(other => other.setAttribute('aria-pressed', String(other === button)));
        search(); input.focus();
    }));
    document.addEventListener('keydown', event => {
        if ((event.metaKey || event.ctrlKey) && !event.altKey && event.key.toLowerCase() === 'k') {
            event.preventDefault();
            if (dialog.open) dialog.close(); else open();
        }
    });
})();
