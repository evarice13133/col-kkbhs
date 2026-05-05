/**
 * GESTIONNAIRE GLOBAL D'ALERTES PREMIUM - NotesMaster
 * 
 * Ce module centralise toutes les notifications de l'application et assure
 * une cohérence visuelle haut de gamme via SweetAlert2.
 * 
 * @module AlertService
 */

const AlertService = {
    /**
     * Configuration par défaut pour toutes les alertes.
     * Applique les classes CSS personnalisées définies dans alerts-premium.css.
     */
    baseConfig: {
        customClass: {
            container: 'premium-swal-container',
            popup: 'premium-swal-popup',
            header: 'premium-swal-header',
            title: 'premium-swal-title',
            closeButton: 'premium-swal-close-btn',
            icon: 'premium-swal-icon',
            image: 'premium-swal-image',
            content: 'premium-swal-content',
            htmlContainer: 'premium-swal-html',
            input: 'premium-swal-input',
            inputLabel: 'premium-swal-input-label',
            validationMessage: 'premium-swal-validation',
            actions: 'premium-swal-actions',
            confirmButton: 'premium-swal-confirm-btn',
            denyButton: 'premium-swal-deny-btn',
            cancelButton: 'premium-swal-cancel-btn',
            loader: 'premium-swal-loader',
            footer: 'premium-swal-footer'
        },
        buttonsStyling: false, // On utilise notre propre CSS
        showCloseButton: true,
        focusConfirm: false,
        heightAuto: false
    },

    /**
     * Affiche une alerte de SUCCÈS.
     * Idéal pour : Création réussie, mise à jour, sauvegarde.
     * 
     * @param {string} title Titre de l'alerte
     * @param {string} message Message descriptif
     */
    success(title, message = '') {
        return Swal.fire({
            ...this.baseConfig,
            icon: 'success',
            title: title,
            html: message,
            confirmButtonText: 'Continuer',
            confirmButtonColor: '#1ea896' // nm-teal
        });
    },

    /**
     * Affiche une alerte d'ERREUR.
     * Idéal pour : Échec de validation, erreur serveur, accès refusé.
     * 
     * @param {string} title Titre de l'alerte
     * @param {string} message Message descriptif
     */
    error(title, message = '') {
        return Swal.fire({
            ...this.baseConfig,
            icon: 'error',
            title: title,
            html: message,
            confirmButtonText: 'Fermer',
            confirmButtonColor: '#d1495b' // nm-red
        });
    },

    /**
     * Affiche une alerte d'AVERTISSEMENT.
     * Idéal pour : Perte de données potentielle, action irréversible.
     * 
     * @param {string} title Titre de l'alerte
     * @param {string} message Message descriptif
     */
    warning(title, message = '') {
        return Swal.fire({
            ...this.baseConfig,
            icon: 'warning',
            title: title,
            html: message,
            confirmButtonText: 'Compris',
            confirmButtonColor: '#f4b942' // nm-gold
        });
    },

    /**
     * Affiche une alerte d'INFORMATION.
     * Idéal pour : Conseils, guidage, détails mineurs.
     */
    info(title, message = '') {
        return Swal.fire({
            ...this.baseConfig,
            icon: 'info',
            title: title,
            html: message,
            confirmButtonText: 'Ok',
            confirmButtonColor: '#2f6fed' // nm-blue
        });
    },

    /**
     * Affiche une demande de CONFIRMATION.
     * Idéal pour : Suppression, archivage, déconnexion.
     * 
     * @param {Object} options Configuration personnalisée
     * @returns {Promise} Résultat de SweetAlert2
     */
    confirm(options = {}) {
        const config = {
            title: options.title || 'Êtes-vous sûr ?',
            html: options.message || 'Cette action ne pourra pas être annulée.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: options.confirmText || 'Confirmer',
            cancelButtonText: options.cancelText || 'Annuler',
            confirmButtonColor: '#d1495b', // Défaut rouge si critique
            cancelButtonColor: '#eef4fb',
            reverseButtons: true, // Annuler à gauche, Confirmer à droite (plus standard)
            ...this.baseConfig,
            ...options
        };

        return Swal.fire(config);
    },

    /**
     * Spécialisé pour les SUPPRESSIONS (Noir sur Blanc / Compact)
     */
    confirmDelete(title, message, options = {}) {
        return this.confirm({
            title: title,
            html: `<div style="color: #000; font-size: 0.9rem;">${message}</div>`,
            icon: 'warning',
            confirmText: 'Supprimer',
            cancelText: 'Annuler',
            confirmButtonColor: '#000000', // Noir sur Blanc
            background: '#ffffff',
            width: '320px',
            customClass: {
                popup: 'rounded-4 shadow-sm p-3 border border-light',
                title: 'text-black fw-bolder fs-5',
                confirmButton: 'btn btn-dark btn-sm w-100 mb-2 rounded-pill',
                cancelButton: 'btn btn-light btn-sm w-100 rounded-pill',
                actions: 'd-flex flex-column w-100 gap-1'
            },
            ...options
        });
    },

    /**
     * Affiche un mini-message TOAST (discret, en haut à droite).
     * Idéal pour : Actions rapides en arrière-plan.
     */
    toast(icon, message) {
        const Toast = Swal.mixin({
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 3000,
            timerProgressBar: true,
            didOpen: (toast) => {
                toast.addEventListener('mouseenter', Swal.stopTimer);
                toast.addEventListener('mouseleave', Swal.resumeTimer);
            }
        });

        return Toast.fire({
            icon: icon,
            title: message,
            customClass: {
                popup: 'premium-toast'
            }
        });
    },

    /**
     * Affiche une alerte persistante lors d'un CHARGEMENT long.
     */
    loading(title = 'Traitement en cours...', message = 'Veuillez patienter') {
        Swal.fire({
            title: title,
            html: message,
            allowOutsideClick: false,
            allowEscapeKey: false,
            didOpen: () => {
                Swal.showLoading();
            },
            ...this.baseConfig
        });
    }
};

// Injection globale pour accès immédiat dans toute l'application
window.AlertService = AlertService;
