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

            console.log('✅ Expérience Utilisateur (UX) synchronisée avec AlertService');
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
