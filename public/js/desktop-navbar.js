/**
 * DESKTOP NAVBAR SAAS INTERACTIVITY & DROPDOWNS CONTROLLER
 * Handles standalone popover dropdowns, keyboard navigation, outside clicks & ARIA state.
 */

document.addEventListener('DOMContentLoaded', () => {
    const navbarContainer = document.getElementById('desktopNavItems');
    if (!navbarContainer) return;

    const triggers = Array.from(navbarContainer.querySelectorAll('.nav-dropdown-trigger'));
    const dropdowns = Array.from(navbarContainer.querySelectorAll('.nav-dropdown-menu'));
    const allTopLevelBtns = Array.from(navbarContainer.querySelectorAll('.nav-item-btn'));

    let activeTrigger = null;
    let activeDropdown = null;

    /**
     * Position & align dropdown relative to viewport
     */
    function adjustDropdownPosition(dropdown) {
        if (!dropdown) return;
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
        if (!activeDropdown) return;

        if (activeTrigger) {
            activeTrigger.setAttribute('aria-expanded', 'false');
            activeTrigger.classList.remove('dropdown-open');
        }

        activeDropdown.classList.remove('show');

        activeTrigger = null;
        activeDropdown = null;
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

            if (isOpen) {
                closeActiveDropdown();
            } else {
                openDropdown(trigger, dropdown);
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
});
