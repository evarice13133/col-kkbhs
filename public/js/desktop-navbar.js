/**
 * DESKTOP NAVBAR SAAS INTERACTIVITY & DROPDOWNS CONTROLLER
 * Handles standalone popover dropdowns, keyboard navigation, outside clicks & ARIA state.
 */

document.addEventListener('DOMContentLoaded', () => {
    const navbarContainer = document.getElementById('desktopNavItems');
    if (!navbarContainer) return;

    const sidebar = navbarContainer.closest('.desktop-nav-menu-bar');
    const desktopNavbar = document.getElementById('desktopNavbarContainer');
    const sidebarToggle = document.getElementById('desktopNavCollapseToggle');
    const sidebarHeading = sidebar?.querySelector('.desktop-nav-sidebar-heading');
    const collapsedStateKey = 'notesmaster.desktopNavCollapsed';
    const triggers = Array.from(navbarContainer.querySelectorAll('.nav-dropdown-trigger'));
    const dropdowns = Array.from(navbarContainer.querySelectorAll('.nav-dropdown-menu'));
    const allTopLevelBtns = Array.from(navbarContainer.querySelectorAll('.nav-item-btn'));
    const quickAccessGrid = document.getElementById('quickAccessMenuGrid');
    const quickAccessCount = document.getElementById('quickAccessMenuCount');

    if (quickAccessGrid) {
        const menuLinks = Array.from(navbarContainer.querySelectorAll('.dropdown-nav-link'));
        const quickAccessItems = [];
        const seenMenuUrls = new Set();
        const priorityUrls = [
            '/students/create',
            '/payments',
            '/school_fees/versements',
            '/school_fees/insolvables',
            '/sequences',
            '/notes',
            '/bulletins',
            '/honors',
            '/proces-verbal',
            '/access-cards',
            '/identity-cards',
            '/student-passes',
            '/subjects',
            '/classes',
            '/subject-groups',
            '/timetables',
            '/timetables/print',
            '/timetables/wizard',
            '/timetables/slots',
            '/timetables/rooms',
            '/timetables/weeks',
        ];
        const priorityByUrl = new Map(priorityUrls.map((url, index) => [url, index]));

        menuLinks.forEach((menuLink, index) => {
            const href = menuLink.getAttribute('href');
            const label = menuLink.querySelector('.link-title')?.textContent.trim();
            const sourceIcon = menuLink.querySelector('.link-icon-box i');
            if (!href || !label || !sourceIcon) return;

            const menuUrl = new URL(href, window.location.origin);
            menuUrl.pathname = menuUrl.pathname.replace(/\/+$/, '') || '/';
            menuUrl.searchParams.sort();
            const uniqueUrl = `${menuUrl.origin}${menuUrl.pathname}${menuUrl.search}${menuUrl.hash}`;
            if (seenMenuUrls.has(uniqueUrl)) return;
            seenMenuUrls.add(uniqueUrl);

            const link = document.createElement('a');
            link.className = 'quick-access-link';
            link.setAttribute('href', href);
            link.setAttribute('aria-label', label);
            if (menuLink.classList.contains('active')) {
                link.classList.add('is-current');
                link.setAttribute('aria-current', 'page');
            }

            const icon = document.createElement('span');
            icon.className = 'quick-access-icon';
            icon.setAttribute('aria-hidden', 'true');
            const iconElement = document.createElement('i');
            iconElement.className = sourceIcon.className;
            icon.appendChild(iconElement);

            const text = document.createElement('span');
            text.className = 'quick-access-text';
            text.textContent = label;

            link.append(icon, text);
            quickAccessItems.push({
                element: link,
                priority: priorityByUrl.get(href) ?? priorityUrls.length,
                index,
            });
        });

        quickAccessItems.sort((a, b) => a.priority - b.priority || a.index - b.index);
        const fragment = document.createDocumentFragment();
        quickAccessItems.forEach(({ element }) => fragment.appendChild(element));
        quickAccessGrid.replaceChildren(fragment);

        if (quickAccessCount) {
            const suffix = quickAccessCount.getAttribute('data-count-suffix')?.trim();
            quickAccessCount.textContent = [String(seenMenuUrls.size), suffix].filter(Boolean).join(' ');
        }
    }

    let activeTrigger = null;
    let activeDropdown = null;
    let closeTimer = null;
    const desktopHover = window.matchMedia('(min-width: 992px) and (hover: hover) and (pointer: fine)');

    function cancelScheduledClose() {
        if (closeTimer !== null) {
            window.clearTimeout(closeTimer);
            closeTimer = null;
        }
    }

    /**
     * Position & align dropdown relative to viewport
     */
    function adjustDropdownPosition(dropdown) {
        if (!dropdown) return;

        if (sidebar && activeTrigger) {
            const triggerRect = activeTrigger.getBoundingClientRect();
            const sidebarRect = sidebar.getBoundingClientRect();
            const dropdownRect = dropdown.getBoundingClientRect();
            const top = Math.max(12, Math.min(triggerRect.top, window.innerHeight - dropdownRect.height - 12));

            dropdown.style.left = `${Math.min(sidebarRect.right + 8, window.innerWidth - dropdownRect.width - 12)}px`;
            dropdown.style.top = `${top}px`;
            dropdown.classList.remove('align-right');
            return;
        }

        dropdown.classList.remove('align-right');
        const rect = dropdown.getBoundingClientRect();
        const viewportWidth = window.innerWidth || document.documentElement.clientWidth;

        if (rect.right > viewportWidth - 16) {
            dropdown.classList.add('align-right');
        }
    }

    /**
     * Open specific dropdown menu
     */
    function openDropdown(trigger, dropdown) {
        cancelScheduledClose();
        if (activeDropdown && activeDropdown !== dropdown) {
            closeActiveDropdown();
        }

        trigger.setAttribute('aria-expanded', 'true');
        trigger.classList.add('dropdown-open');
        dropdown.classList.add('show');
        
        activeTrigger = trigger;
        activeDropdown = dropdown;

        adjustDropdownPosition(dropdown);
    }

    /**
     * Close currently active dropdown
     */
    function closeActiveDropdown() {
        cancelScheduledClose();
        if (!activeDropdown) return;

        if (activeTrigger) {
            activeTrigger.setAttribute('aria-expanded', 'false');
            activeTrigger.classList.remove('dropdown-open');
        }

        activeDropdown.classList.remove('show');

        activeTrigger = null;
        activeDropdown = null;
    }

    function setSidebarCollapsed(collapsed) {
        if (!sidebar || !sidebarToggle) return;

        const collapseLabel = sidebarToggle.getAttribute('data-label-collapse') || 'Collapse navigation';
        const expandLabel = sidebarToggle.getAttribute('data-label-expand') || 'Expand navigation';
        sidebar.classList.toggle('is-collapsed', collapsed);
        if (desktopNavbar) desktopNavbar.classList.toggle('is-sidebar-collapsed', collapsed);
        sidebarToggle.setAttribute('aria-expanded', String(!collapsed));
        sidebarToggle.setAttribute('aria-label', collapsed ? expandLabel : collapseLabel);
        sidebarToggle.setAttribute('title', collapsed ? expandLabel : collapseLabel);
        if (sidebarHeading) {
            sidebarHeading.setAttribute('aria-hidden', String(collapsed));
        }

        const label = sidebarToggle.querySelector('span');
        const icon = sidebarToggle.querySelector('i');
        if (label) label.textContent = collapsed ? expandLabel : collapseLabel;
        if (icon) {
            icon.classList.toggle('bi-chevron-bar-left', !collapsed);
            icon.classList.toggle('bi-chevron-bar-right', collapsed);
        }

        triggers.forEach((trigger) => {
            const labelElement = trigger.querySelector('span');
            const title = labelElement ? labelElement.textContent.trim() : '';
            trigger.setAttribute('aria-label', title);
            if (collapsed) {
                trigger.setAttribute('title', title);
            } else {
                trigger.removeAttribute('title');
            }
        });

        if (activeDropdown) adjustDropdownPosition(activeDropdown);
    }

    if (sidebar && sidebarToggle) {
        setSidebarCollapsed(localStorage.getItem(collapsedStateKey) === 'true');
        sidebarToggle.addEventListener('click', () => {
            const collapsed = !sidebar.classList.contains('is-collapsed');
            localStorage.setItem(collapsedStateKey, String(collapsed));
            setSidebarCollapsed(collapsed);
        });
    }

    navbarContainer.addEventListener('click', (event) => {
        const target = event.target;
        if (!(target instanceof Element)) return;

        const link = target.closest('.dropdown-nav-link');
        if (!(link instanceof HTMLAnchorElement) || !navbarContainer.contains(link)) return;

        localStorage.setItem(collapsedStateKey, 'true');
        closeActiveDropdown();
        setSidebarCollapsed(true);
    });

    if (quickAccessGrid) {
        quickAccessGrid.addEventListener('click', (event) => {
            const target = event.target;
            if (!(target instanceof Element)) return;

            const link = target.closest('.quick-access-link');
            if (!(link instanceof HTMLAnchorElement) || !quickAccessGrid.contains(link)) return;

            localStorage.setItem(collapsedStateKey, 'true');
            closeActiveDropdown();
            setSidebarCollapsed(true);
        });
    }

    function scheduleClose(trigger) {
        cancelScheduledClose();
        closeTimer = window.setTimeout(() => {
            if (activeTrigger === trigger) {
                closeActiveDropdown();
            }
        }, 180);
    }

    /**
     * Setup Trigger Click Event Listeners
     */
    triggers.forEach(trigger => {
        const targetId = trigger.getAttribute('data-nav-target');
        const dropdown = document.getElementById(targetId);

        if (!dropdown) return;

        trigger.addEventListener('click', (e) => {
            e.stopPropagation();
            const isOpen = trigger.getAttribute('aria-expanded') === 'true';

            if (desktopHover.matches && e.detail > 0) {
                openDropdown(trigger, dropdown);
                return;
            }

            if (isOpen) {
                closeActiveDropdown();
            } else {
                openDropdown(trigger, dropdown);
            }
        });

        const wrapper = trigger.closest('.nav-dropdown-wrapper');
        if (!wrapper) return;

        wrapper.addEventListener('pointerenter', (e) => {
            if (!desktopHover.matches || e.pointerType === 'touch') return;
            openDropdown(trigger, dropdown);
        });

        wrapper.addEventListener('pointerleave', (e) => {
            if (!desktopHover.matches || e.pointerType === 'touch') return;
            scheduleClose(trigger);
        });

        wrapper.addEventListener('focusin', () => {
            openDropdown(trigger, dropdown);
        });

        wrapper.addEventListener('focusout', (e) => {
            if (!wrapper.contains(e.relatedTarget)) {
                scheduleClose(trigger);
            }
        });
    });

    /**
     * Outside Click Handler
     */
    document.addEventListener('click', (e) => {
        if (!activeDropdown) return;
        
        const isClickInsideNavbar = navbarContainer.contains(e.target);
        if (!isClickInsideNavbar) {
            closeActiveDropdown();
        }
    });

    /**
     * Global Keyboard Navigation (Escape, Arrows)
     */
    document.addEventListener('keydown', (e) => {
        // Escape Key -> Close Dropdown & Focus Trigger
        if (e.key === 'Escape' || e.key === 'Esc') {
            if (activeDropdown) {
                e.preventDefault();
                const triggerToFocus = activeTrigger;
                closeActiveDropdown();
                if (triggerToFocus) triggerToFocus.focus();
            }
        }
    });

    // Keyboard Arrow Navigation across Top Level Buttons
    allTopLevelBtns.forEach((btn, index) => {
        btn.addEventListener('keydown', (e) => {
            if (e.key === 'ArrowRight') {
                e.preventDefault();
                const nextBtn = allTopLevelBtns[(index + 1) % allTopLevelBtns.length];
                if (nextBtn) nextBtn.focus();
            } else if (e.key === 'ArrowLeft') {
                e.preventDefault();
                const prevBtn = allTopLevelBtns[(index - 1 + allTopLevelBtns.length) % allTopLevelBtns.length];
                if (prevBtn) prevBtn.focus();
            } else if (e.key === 'ArrowDown') {
                e.preventDefault();
                const targetId = btn.getAttribute('data-nav-target');
                const dropdown = targetId ? document.getElementById(targetId) : null;
                
                if (dropdown) {
                    if (!dropdown.classList.contains('show')) {
                        openDropdown(btn, dropdown);
                    }
                    const firstLink = dropdown.querySelector('.dropdown-nav-link');
                    if (firstLink) firstLink.focus();
                }
            }
        });
    });

    // Keyboard Navigation Inside Dropdown Links
    dropdowns.forEach(dropdown => {
        const links = Array.from(dropdown.querySelectorAll('.dropdown-nav-link'));
        
        links.forEach((link, idx) => {
            link.addEventListener('keydown', (e) => {
                if (e.key === 'ArrowDown') {
                    e.preventDefault();
                    const nextLink = links[(idx + 1) % links.length];
                    if (nextLink) nextLink.focus();
                } else if (e.key === 'ArrowUp') {
                    e.preventDefault();
                    if (idx === 0) {
                        if (activeTrigger) activeTrigger.focus();
                    } else {
                        const prevLink = links[(idx - 1 + links.length) % links.length];
                        if (prevLink) prevLink.focus();
                    }
                } else if (e.key === 'Escape' || e.key === 'Esc') {
                    e.preventDefault();
                    const triggerToFocus = activeTrigger;
                    closeActiveDropdown();
                    if (triggerToFocus) triggerToFocus.focus();
                }
            });
        });
    });

    // Handle Window Resize
    window.addEventListener('resize', () => {
        if (activeDropdown) {
            adjustDropdownPosition(activeDropdown);
        }
    });

    navbarContainer.addEventListener('scroll', () => {
        if (activeDropdown) adjustDropdownPosition(activeDropdown);
    });
});
