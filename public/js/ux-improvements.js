/**
 * UX & UI Utility Module - NotesMaster
 * Provides functions for AJAX LOADING, SKELETONS, LOADER, and TOASTS.
 */

const UX = (function () {
    "use strict";

    // --- Private Variables ---
    const LOADER_ID = 'global-loader';
    const TOAST_CONTAINER_ID = 'toast-container-main';
    const I18N = window.NM_I18N || {};
    let loaderTimeout = null;

    function t(key, fallback) {
        return I18N[key] || fallback;
    }

    // --- Private Methods ---

    /**
     * Create toast container if it doesn't exist
     */
    function ensureToastContainer() {
        let container = document.getElementById(TOAST_CONTAINER_ID);
        if (!container) {
            container = document.createElement('div');
            container.id = TOAST_CONTAINER_ID;
            container.className = 'toast-container';
            document.body.appendChild(container);
        }
        return container;
    }

    // --- API Publique ---
    return {
        /**
         * Initialise les éléments UX généraux.
         */
        init() {
            const mainContent = document.querySelector('main');
            if (mainContent) mainContent.classList.add('fade-in');

            // 1. Intercepte les soumissions de formulaires pour montrer le chargement
            document.addEventListener('submit', function (e) {
                const form = e.target;
                if (!form.hasAttribute('data-ajax') && !form.classList.contains('no-loader')) {
                    AlertService.loading();
                }
            });

            // 2. Initialise les confirmations d'actions
            this.initConfirmations();

            // 3. Résout le problème des backdrops de modals Bootstrap (les déplacer dans le body)
            this.fixNestedModals();

            // 4. Initialise les filtres de recherche d'élèves dans les modals
            this.initStudentFilters();

            // 5. Initialise les filtres de structure (Enseignement, Section, Cycle) pour les classes
            this.initClassFilters();

            // 6. Initialise les interactions de la navigation mobile (Search, Auto-close, Tables responsive)
            this.initMobileNavigation();

            console.log('✅ Expérience Utilisateur (UX) synchronisée avec AlertService');
        },

        /**
         * Déplace tous les modals Bootstrap directement sous document.body 
         * pour éviter les bugs d'empilement (stacking context) et de blocage d'écran (backdrop bug).
         */
        fixNestedModals() {
            document.querySelectorAll('.modal').forEach(function (modal) {
                if (modal.parentNode !== document.body) {
                    document.body.appendChild(modal);
                }
            });
        },

        /**
         * Initialise les filtres de recherche d'élèves en temps réel (insensible aux accents).
         */
        initStudentFilters() {
            document.addEventListener('input', function(e) {
                const filterInput = e.target.closest('.modal-student-filter');
                if (!filterInput) return;

                const parentModal = filterInput.closest('.modal');
                if (!parentModal) return;

                const select = parentModal.querySelector('select[name="student_id"]');
                if (!select) return;

                const query = filterInput.value.toLowerCase().normalize("NFD").replace(/[\u0300-\u036f]/g, "").trim();
                const options = select.querySelectorAll('option:not([disabled])');
                const optgroups = select.querySelectorAll('optgroup');

                if (!query) {
                    options.forEach(opt => opt.style.display = '');
                    optgroups.forEach(og => og.style.display = '');
                    return;
                }

                optgroups.forEach(og => {
                    let hasVisibleOption = false;
                    const ogOptions = og.querySelectorAll('option');
                    ogOptions.forEach(opt => {
                        const text = opt.textContent.toLowerCase().normalize("NFD").replace(/[\u0300-\u036f]/g, "");
                        if (text.includes(query)) {
                            opt.style.display = '';
                            hasVisibleOption = true;
                        } else {
                            opt.style.display = 'none';
                        }
                    });

                    if (hasVisibleOption) {
                        og.style.display = '';
                    } else {
                        og.style.display = 'none';
                    }
                });
            });

            // Réinitialiser le filtre lorsque le modal est fermé
            document.addEventListener('hidden.bs.modal', function(e) {
                const modal = e.target;
                const filterInput = modal.querySelector('.modal-student-filter');
                if (filterInput) {
                    filterInput.value = '';
                    filterInput.dispatchEvent(new Event('input', { bubbles: true }));
                }
            });
        },

        /**
         * Initialise les filtres structurels (Enseignement, Section, Cycle) 
         * pour les listes de classes dans les modals collectifs.
         */
        initClassFilters() {
            // Utiliser la délégation d'événements à l'écoute des changements sur les sélecteurs de filtres
            document.addEventListener('change', function(e) {
                const filter = e.target.closest('.filter-teaching-type, .filter-section, .filter-cycle');
                if (!filter) return;

                const modal = filter.closest('.modal');
                if (!modal) return;

                const classSelect = modal.querySelector('.class-select-element');
                if (!classSelect) return;

                const filterTeaching = modal.querySelector('.filter-teaching-type');
                const filterSection = modal.querySelector('.filter-section');
                const filterCycle = modal.querySelector('.filter-cycle');

                // Si c'est le premier filtre, on sauvegarde les options initiales dans une propriété de l'élément select
                if (!classSelect.dataset.optionsSaved) {
                    const originalOptions = [];
                    classSelect.querySelectorAll('option').forEach(opt => {
                        originalOptions.push({
                            value: opt.value,
                            text: opt.textContent,
                            disabled: opt.disabled,
                            selected: opt.selected,
                            teachingType: opt.getAttribute('data-teaching-type') || '',
                            section: opt.getAttribute('data-section') || '',
                            cycle: opt.getAttribute('data-cycle') || ''
                        });
                    });
                    classSelect.originalOptionsArray = originalOptions;
                    classSelect.dataset.optionsSaved = 'true';
                }

                const selectedTeaching = filterTeaching ? filterTeaching.value : '';
                const selectedSection = filterSection ? filterSection.value : '';
                const selectedCycle = filterCycle ? filterCycle.value : '';

                // Vider les options actuelles
                classSelect.innerHTML = '';

                classSelect.originalOptionsArray.forEach(opt => {
                    // Toujours conserver l'option par défaut
                    if (opt.disabled) {
                        const newOpt = document.createElement('option');
                        newOpt.value = opt.value;
                        newOpt.textContent = opt.text;
                        newOpt.disabled = true;
                        newOpt.selected = true;
                        classSelect.appendChild(newOpt);
                        return;
                    }

                    // Vérifier la correspondance des filtres
                    const matchTeaching = !selectedTeaching || opt.teachingType === selectedTeaching;
                    const matchSection = !selectedSection || opt.section === selectedSection;
                    const matchCycle = !selectedCycle || opt.cycle === selectedCycle;

                    if (matchTeaching && matchSection && matchCycle) {
                        const newOpt = document.createElement('option');
                        newOpt.value = opt.value;
                        newOpt.textContent = opt.text;
                        newOpt.setAttribute('data-teaching-type', opt.teachingType);
                        newOpt.setAttribute('data-section', opt.section);
                        newOpt.setAttribute('data-cycle', opt.cycle);
                        classSelect.appendChild(newOpt);
                    }
                });
            });

            // Réinitialiser les filtres à la fermeture du modal
            document.addEventListener('hidden.bs.modal', function(e) {
                const modal = e.target;
                const filterTeaching = modal.querySelector('.filter-teaching-type');
                const filterSection = modal.querySelector('.filter-section');
                const filterCycle = modal.querySelector('.filter-cycle');

                if (filterTeaching) filterTeaching.value = '';
                if (filterSection) filterSection.value = '';
                if (filterCycle) filterCycle.value = '';

                const classSelect = modal.querySelector('.class-select-element');
                if (classSelect && classSelect.originalOptionsArray) {
                    classSelect.innerHTML = '';
                    classSelect.originalOptionsArray.forEach(opt => {
                        const newOpt = document.createElement('option');
                        newOpt.value = opt.value;
                        newOpt.textContent = opt.text;
                        newOpt.disabled = opt.disabled;
                        newOpt.selected = opt.selected;
                        if (!opt.disabled) {
                            newOpt.setAttribute('data-teaching-type', opt.teachingType);
                            newOpt.setAttribute('data-section', opt.section);
                            newOpt.setAttribute('data-cycle', opt.cycle);
                        }
                        classSelect.appendChild(newOpt);
                    });
                }
            });
        },

        /**
         * Standardise les boutons d'action critique (suppression, démission, restauration).
         */
        initConfirmations() {
            document.addEventListener('click', (e) => {
                // DELETE Confirmation
                const deleteTrigger = e.target.closest('.btn-confirm-delete');
                if (deleteTrigger) {
                    e.preventDefault();
                    
                    // Sécurité spécifique pour les classes : interdire la suppression si non vide
                    const studentCount = parseInt(deleteTrigger.dataset.studentCount || '0');
                    if (studentCount > 0) {
                        AlertService.warning(
                            t('action_forbidden', 'Action Interdite'),
                            t('class_not_empty_error', 'Cette classe contient encore des élèves. Vous devez les transférer ou les supprimer avant de pouvoir supprimer la salle.')
                        );
                        return;
                    }

                    const url = deleteTrigger.getAttribute('href');
                    const message = deleteTrigger.dataset.confirm || t('confirm_delete_text', 'Cette action est irréversible.');

                    AlertService.confirmDelete(
                        t('warning_title', 'Attention'),
                        message
                    ).then((result) => {
                        if (result.isConfirmed) {
                            window.location.href = url;
                        }
                    });
                    return;
                }

                // WITHDRAW Confirmation
                const withdrawTrigger = e.target.closest('.btn-confirm-withdraw');
                if (withdrawTrigger) {
                    e.preventDefault();
                    const url = withdrawTrigger.getAttribute('href');
                    const message = withdrawTrigger.dataset.confirm || t('confirm_withdraw_text', 'Marquer cet élève comme démissionnaire ?');

                    AlertService.confirmDelete(
                        t('warning_title', 'Attention'),
                        message,
                        { confirmText: t('confirm_withdraw_action', 'Oui, démissionner') }
                    ).then((result) => {
                        if (result.isConfirmed) {
                            window.location.href = url;
                        }
                    });
                    return;
                }

                // RESTORE Confirmation
                const restoreTrigger = e.target.closest('.btn-confirm-restore');
                if (restoreTrigger) {
                    e.preventDefault();
                    const url = restoreTrigger.getAttribute('href');
                    const message = restoreTrigger.dataset.confirm || t('confirm_restore_text', 'Restaurer cet élève dans sa classe ?');

                    AlertService.confirmDelete(
                        t('info_title', 'Information'),
                        message,
                        { 
                            confirmText: t('confirm_restore_action', 'Oui, restaurer'),
                            icon: 'info'
                        }
                    ).then((result) => {
                        if (result.isConfirmed) {
                            window.location.href = url;
                        }
                    });
                    return;
                }

                // TOGGLE Confirmation
                const toggleTrigger = e.target.closest('.btn-confirm-toggle');
                if (toggleTrigger) {
                    e.preventDefault();
                    const url = toggleTrigger.getAttribute('href');
                    const message = toggleTrigger.dataset.confirm || t('confirm_toggle_text', 'Voulez-vous modifier le statut de cet élément ?');

                    AlertService.confirm({
                        title: t('confirmation', 'Confirmation'),
                        message: message,
                        icon: 'question',
                        confirmText: t('confirm', 'Confirmer'),
                        cancelText: t('cancel', 'Annuler'),
                        confirmButtonColor: '#3b82f6', // Bleu pour action non-critique
                    }).then((result) => {
                        if (result.isConfirmed) {
                            window.location.href = url;
                        }
                    });
                    return;
                }
            });
        },

        /**
         * Initialise la navigation mobile, le filtre temps réel de la sidebar mobile,
         * et l'enveloppement automatique des tableaux responsifs.
         */
        initMobileNavigation() {
            // 1. Bouton de recherche mobile -> Déclenche la Command Palette (Ctrl+K)
            const mobileSearchBtn = document.getElementById('openCmdPaletteTriggerMobile');
            if (mobileSearchBtn) {
                mobileSearchBtn.addEventListener('click', function () {
                    const cmdPalette = document.getElementById('commandPalette');
                    const cmdInput = document.getElementById('cmdPaletteInput');
                    if (cmdPalette) {
                        cmdPalette.classList.add('active');
                        if (cmdInput) {
                            setTimeout(() => cmdInput.focus(), 150);
                        }
                    }
                });
            }

            // 2. Filtre en temps réel des menus dans le drawer mobile
            const drawerSearchInput = document.getElementById('mobileDrawerSearch');
            if (drawerSearchInput) {
                drawerSearchInput.addEventListener('input', function (e) {
                    const query = e.target.value.toLowerCase().trim();
                    const accordionItems = document.querySelectorAll('#mobileRibbonAccordion .mobile-nav-group-item');

                    accordionItems.forEach(item => {
                        let groupHasMatch = false;
                        const itemLinks = item.querySelectorAll('.mobile-drawer-link');

                        itemLinks.forEach(link => {
                            const label = link.getAttribute('data-menu-label') || link.textContent.toLowerCase();
                            if (!query || label.includes(query)) {
                                link.style.display = 'flex';
                                groupHasMatch = true;
                            } else {
                                link.style.display = 'none';
                            }
                        });

                        const collapseEl = item.querySelector('.accordion-collapse');
                        if (query) {
                            if (groupHasMatch) {
                                item.style.display = '';
                                if (collapseEl && !collapseEl.classList.contains('show') && typeof bootstrap !== 'undefined') {
                                    const bsCollapse = bootstrap.Collapse.getOrCreateInstance(collapseEl, { toggle: false });
                                    bsCollapse.show();
                                }
                            } else {
                                item.style.display = 'none';
                            }
                        } else {
                            item.style.display = '';
                        }
                    });
                });
            }

            // 3. Fermeture automatique du drawer lors du clic sur un lien de navigation
            document.querySelectorAll('.mobile-drawer-link').forEach(link => {
                link.addEventListener('click', function () {
                    const drawerEl = document.getElementById('mobileRibbonDrawer');
                    if (drawerEl && typeof bootstrap !== 'undefined') {
                        const drawerInstance = bootstrap.Offcanvas.getInstance(drawerEl);
                        if (drawerInstance) drawerInstance.hide();
                    }
                });
            });

            // 4. Enveloppement automatique des tableaux non-responsifs
            document.querySelectorAll('table').forEach(table => {
                if (!table.closest('.table-responsive') && !table.closest('.table-responsive-custom') && !table.classList.contains('no-responsive')) {
                    const wrapper = document.createElement('div');
                    wrapper.className = 'table-responsive-custom shadow-sm my-2';
                    table.parentNode.insertBefore(wrapper, table);
                    wrapper.appendChild(table);
                }
            });
        },

        /**
         * Déclenche une notification TOAST professionnelle à l'aide de AlertService.
         */
        toast(title, message, type = 'success') {
            return AlertService.toast(type, message || title);
        },

        /**
         * Bascule FR/EN (redirige vers /locale avec retour sur la page courante).
         */
        switchLanguage(lang) {
            const path = window.location.pathname + window.location.search;
            const safe = path.startsWith('/') ? path : '/';
            window.location.href = '/locale?lang=' + encodeURIComponent(lang) + '&redirect=' + encodeURIComponent(safe);
        }
    };
})();

// Auto-initialisation
document.addEventListener('DOMContentLoaded', () => UX.init());
