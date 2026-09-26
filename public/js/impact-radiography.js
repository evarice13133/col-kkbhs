/**
 * Radiographie d'Impact - Module JS Unifié
 * Gère le chargement AJAX de l'analyse, l'affichage du modal et l'exécution du smart-delete
 */

(function () {
    'use strict';

    const ImpactRadiography = {
        modalEl: null,
        bsModal: null,
        currentEntity: null,
        currentId: null,
        currentAnalysis: null,
        onSuccessCallback: null,

        text: function (key, fallbackFr, fallbackEn) {
            const lang = (window.NM_I18N?.lang || document.documentElement.lang || 'fr').toLowerCase();
            return window.NM_I18N?.impact?.[key] || (lang === 'en' ? fallbackEn : fallbackFr);
        },

        init: function () {
            this.modalEl = document.getElementById('impactRadiographyModal');
            if (!this.modalEl) return;

            this.bsModal = new bootstrap.Modal(this.modalEl, {
                backdrop: 'static',
                keyboard: false
            });

            this.bindGlobalListeners();
        },

        bindGlobalListeners: function () {
            const self = this;

            // Intercepter les clics sur les éléments avec data-impact-delete
            document.addEventListener('click', function (e) {
                const btn = e.target.closest('[data-impact-delete]');
                if (!btn) return;

                e.preventDefault();
                const entity = btn.getAttribute('data-impact-delete');
                const id = btn.getAttribute('data-id') || btn.getAttribute('data-entity-id');
                const csrf = btn.getAttribute('data-csrf') || (window.CSRF_TOKEN || '');

                if (entity && id) {
                    self.open({
                        entity: entity,
                        id: parseInt(id, 10),
                        csrfToken: csrf,
                        onSuccess: function (res) {
                            if (window.Toast) {
                                Toast.success(res.message || 'Opération effectuée avec succès.');
                            }
                            // Rechargement ou suppression de ligne dans le tableau
                            setTimeout(() => {
                                window.location.reload();
                            }, 800);
                        }
                    });
                }
            });

            // Action form submit (Smart delete)
            const form = document.getElementById('impactActionForm');
            if (form) {
                form.addEventListener('submit', function (e) {
                    e.preventDefault();
                    self.submitSmartDelete();
                });
            }
        },

        showAlert: function (type, title, message) {
            if (window.AlertService && typeof window.AlertService[type] === 'function') {
                window.AlertService[type](title, message || '');
            } else if (window.Swal) {
                Swal.fire({ icon: type, title: title, text: message || '' });
            } else {
                alert((title ? title + ' : ' : '') + (message || ''));
            }
        },

        showConfirm: function (title, message, callback) {
            if (window.AlertService && typeof window.AlertService.confirm === 'function') {
                window.AlertService.confirm({
                    title: title,
                    text: message,
                    confirmButtonText: this.text('confirmYes', 'Oui, confirmer', 'Yes, confirm'),
                    cancelButtonText: this.text('cancel', 'Annuler', 'Cancel'),
                    confirmButtonColor: '#ef4444'
                }).then(result => {
                    if (result.isConfirmed) callback();
                });
            } else if (window.Swal) {
                Swal.fire({
                    title: title,
                    text: message,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: this.text('confirmYes', 'Oui, confirmer', 'Yes, confirm'),
                    cancelButtonText: this.text('cancel', 'Annuler', 'Cancel'),
                    confirmButtonColor: '#ef4444'
                }).then(result => {
                    if (result.isConfirmed) callback();
                });
            } else {
                if (confirm(title + '\n' + message)) callback();
            }
        },

        open: function (options) {
            this.currentEntity = options.entity;
            this.currentId = options.id;
            this.onSuccessCallback = options.onSuccess || null;

            const csrfInput = document.getElementById('impactCsrfToken');
            if (csrfInput) {
                csrfInput.value = options.csrfToken || (window.CSRF_TOKEN || '');
            }

            this.showSkeletonLoading();
            this.bsModal.show();

            // Fetch Analysis API
            fetch(`/api/impact-analysis?type=${encodeURIComponent(this.currentEntity)}&id=${this.currentId}`)
                .then(res => {
                    if (!res.ok) throw new Error(this.text('requestError', 'Erreur lors de la récupération des données d\'impact.', 'Error retrieving impact data.'));
                    return res.json();
                })
                .then(data => {
                    if (data.error) {
                        this.showAlert('error', this.text('analysisErrorTitle', 'Erreur d\'analyse', 'Analysis error'), data.message || this.text('analysisErrorMessage', 'Impossible d\'analyser cet élément.', 'Unable to analyze this item.'));
                        this.bsModal.hide();
                        return;
                    }
                    this.currentAnalysis = data;
                    this.renderAnalysis(data);
                })
                .catch(err => {
                    console.error('Impact Radiography Error:', err);
                    this.showAlert('error', this.text('networkErrorTitle', 'Erreur réseau', 'Network error'), err.message || this.text('serverUnavailable', 'Impossible d\'accéder au serveur.', 'Unable to reach the server.'));
                    this.bsModal.hide();
                });
        },

        showSkeletonLoading: function () {
            document.getElementById('impactModalBody').innerHTML = `
                <div class="p-4 text-center position-relative">
                    <button type="button" class="btn-close position-absolute top-0 end-0 m-3" data-bs-dismiss="modal" aria-label="${this.text('cancel', 'Annuler', 'Cancel')}"></button>
                    <div class="spinner-border text-primary mb-3" role="status" style="width: 3rem; height: 3rem;"></div>
                    <h6 class="fw-bold text-secondary">${this.text('loadingTitle', 'Radiographie d\'impact en cours...', 'Impact analysis in progress...')}</h6>
                    <p class="text-muted small mb-3">${this.text('loadingDescription', 'Analyse dynamique de l\'arborescence des dépendances en base de données.', 'Dynamically analyzing the dependency tree in the database.')}</p>
                    <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-3" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i> ${this.text('cancel', 'Annuler', 'Cancel')}
                    </button>
                </div>
            `;
        },

        renderAnalysis: function (data) {
            const entity = data.entity;
            const risk = data.risk_level || 'low';
            const stats = data.stats || [];
            const summary = data.impact_summary || {};
            const transfer = data.transfer_options;
            const canDirectDelete = data.can_direct_delete;

            let riskBadgeClass = 'risk-low';
            let riskBadgeLabel = this.text('lowRisk', 'Risque faible', 'Low risk');
            let riskIcon = 'fas fa-shield-alt';

            if (risk === 'medium') {
                riskBadgeClass = 'risk-medium';
                riskBadgeLabel = this.text('mediumRisk', 'Risque moyen', 'Medium risk');
                riskIcon = 'fas fa-exclamation-circle';
            } else if (risk === 'high') {
                riskBadgeClass = 'risk-high';
                riskBadgeLabel = this.text('highRisk', 'Risque élevé', 'High risk');
                riskIcon = 'fas fa-exclamation-triangle';
            } else if (risk === 'critical') {
                riskBadgeClass = 'risk-critical';
                riskBadgeLabel = this.text('criticalRisk', 'Risque critique', 'Critical risk');
                riskIcon = 'fas fa-radiation';
            }

            // Génération HTML des stats
            let statsHtml = '';
            if (stats.length > 0) {
                statsHtml = '<div class="impact-stats-grid">';
                stats.forEach(st => {
                    statsHtml += `
                        <div class="impact-stat-card severity-${st.severity || 'neutral'}">
                            <div class="impact-stat-icon"><i class="${st.icon}"></i></div>
                            <div class="impact-stat-info">
                                <span class="impact-stat-count">${st.count}</span>
                                <span class="impact-stat-label">${st.label}</span>
                            </div>
                        </div>
                    `;
                });
                statsHtml += '</div>';
            }

            // Génération HTML du résumé d'impact
            const summaryHtml = `
                <div class="impact-summary-box">
                    <div class="impact-summary-item text-danger">
                        <i class="fas fa-trash-alt"></i>
                        <div><strong>${this.text('target', 'Élément cible :', 'Target item:')}</strong> ${summary.direct_deletion || entity.name}</div>
                    </div>
                    <div class="impact-summary-item text-warning">
                        <i class="fas fa-link"></i>
                        <div><strong>${this.text('dependencies', 'Dépendances détectées :', 'Detected dependencies:')}</strong> ${summary.dependencies || this.text('none', 'Aucun', 'None')}</div>
                    </div>
                    <div class="impact-summary-item text-info">
                        <i class="fas fa-history"></i>
                        <div><strong>${this.text('historicalData', 'Données liées / Historique :', 'Related data / History:')}</strong> ${summary.historical_data || this.text('none', 'Aucun', 'None')}</div>
                    </div>
                    <div class="impact-summary-item text-secondary">
                        <i class="fas fa-unlink"></i>
                        <div><strong>${this.text('invalidReferences', 'Impact des références :', 'Reference impact:')}</strong> ${summary.invalid_references || this.text('none', 'Aucun', 'None')}</div>
                    </div>
                </div>
            `;

            // Options de transfert HTML
            let transferHtml = '';
            if (transfer && transfer.items && transfer.items.length > 0) {
                transferHtml = `
                    <div class="mb-3 p-3 bg-light-subtle rounded-3 border">
                        <label class="form-label fw-bold text-primary small">
                            <i class="fas fa-exchange-alt me-1"></i> ${transfer.label}
                        </label>
                        <select class="form-select" id="impactTargetSelect" name="target_id">
                            <option value="">${this.text('noTransferTarget', '-- Sélectionner un élément de remplacement --', '-- Select a replacement item --')}</option>
                            ${transfer.items.map(it => `<option value="${it.id}">${it.name}</option>`).join('')}
                        </select>
                        <div class="form-text text-muted small">${this.text('transferDescription', 'Les dépendances seront immédiatement transférées à la cible choisie.', 'Dependencies will be transferred immediately to the selected target.')}</div>
                    </div>
                `;
            } else {
                transferHtml = `
                    <div class="alert alert-info py-2 small">
                        <i class="fas fa-info-circle me-1"></i> ${this.text('noTransferAvailable', 'Aucun élément équivalent disponible pour le transfert automatique.', 'No equivalent item is available for automatic transfer.')}
                    </div>
                `;
            }

            // Validation de nom pour risques élevés
            let nameConfirmHtml = '';
            if (risk === 'critical' || risk === 'high') {
                nameConfirmHtml = `
                    <div class="mb-3 p-3 bg-danger-subtle rounded-3 border border-danger">
                        <label class="form-label fw-bold text-danger small">
                            <i class="fas fa-lock me-1"></i> ${this.text('explicitConfirmation', 'Confirmation explicite requise pour le niveau :', 'Explicit confirmation required for the :risk risk level:').replace(':risk', riskBadgeLabel)}
                        </label>
                        <p class="small text-muted mb-2">${this.text('enterName', 'Veuillez saisir exactement', 'Enter exactly')} <strong>${entity.name}</strong> ${this.text('toAuthorizeDelete', 'pour autoriser la suppression directe :', 'to authorize direct deletion:')}</p>
                        <input type="text" class="form-control" id="impactConfirmName" placeholder="${entity.name}">
                    </div>
                `;
            }

            const html = `
                <div class="modal-impact-header">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <span class="risk-badge ${riskBadgeClass}"><i class="${riskIcon}"></i> ${riskBadgeLabel}</span>
                        <div class="d-flex align-items-center gap-2">
                            <span class="badge bg-secondary">${entity.type_label} #${entity.id}</span>
                            <button type="button" class="btn-close ms-2" data-bs-dismiss="modal" aria-label="${this.text('cancel', 'Annuler', 'Cancel')}"></button>
                        </div>
                    </div>
                    <div class="modal-impact-title">${entity.name}</div>
                    ${entity.subtext ? `<div class="modal-impact-subtitle">${entity.subtext}</div>` : ''}
                </div>

                <div class="modal-body p-4">
                    ${statsHtml}
                    
                    <h6 class="fw-bold mb-2 text-dark small text-uppercase tracking-wider">${this.text('summaryTitle', 'Bilan d’impact dynamique :', 'Dynamic impact summary:')}</h6>
                    ${summaryHtml}

                    <!-- Tabs Scénarios -->
                    <ul class="nav nav-tabs impact-nav-tabs mb-3" role="tablist">
                        <li class="nav-item">
                            <button class="nav-link active" id="tab-smart" data-bs-toggle="tab" data-bs-target="#content-smart" type="button">
                                <i class="fas fa-magic me-1"></i> 1. ${this.text('smartDelete', 'Suppression intelligente (recommandée)', 'Smart delete (recommended)')}
                            </button>
                        </li>
                        <li class="nav-item">
                            <button class="nav-link nav-direct" id="tab-direct" data-bs-toggle="tab" data-bs-target="#content-direct" type="button">
                                <i class="fas fa-trash-alt me-1"></i> 2. ${this.text('directDelete', 'Suppression directe', 'Direct deletion')}
                            </button>
                        </li>
                    </ul>

                    <div class="tab-content">
                        <!-- Scenario 1: Smart Delete -->
                        <div class="tab-pane fade show active" id="content-smart">
                            ${transferHtml}
                            <div class="d-flex gap-2 flex-wrap">
                                ${transfer && transfer.items && transfer.items.length > 0 ? `
                                    <button type="button" class="btn btn-success flex-grow-1" id="btnExecuteTransfer">
                                        <i class="fas fa-exchange-alt me-1"></i> ${this.text('transferButton', 'Transférer les données et réaffecter', 'Transfer data and reassign')}
                                    </button>
                                ` : ''}
                                ${['student', 'teacher', 'user', 'class', 'subject', 'academic_year'].includes(entity.type) ? `
                                    <button type="button" class="btn btn-warning flex-grow-1" id="btnExecuteArchive">
                                        <i class="fas fa-archive me-1"></i> ${this.text('archiveButton', 'Archiver / Désactiver cet élément', 'Archive / Deactivate this item')}
                                    </button>
                                ` : `
                                    <button type="button" class="btn btn-primary flex-grow-1" id="btnExecuteArchive">
                                        <i class="fas fa-check-circle me-1"></i> ${this.text('safeDeleteButton', 'Supprimer en toute sécurité', 'Delete safely')}
                                    </button>
                                `}
                            </div>
                        </div>

                        <!-- Scenario 2: Direct Delete -->
                        <div class="tab-pane fade" id="content-direct">
                            ${!canDirectDelete ? `
                                <div class="alert alert-danger py-2 small mb-3">
                                    <i class="fas fa-ban me-1"></i> <strong>${this.text('directDeleteBlocked', 'Suppression directe bloquée :', 'Direct deletion blocked:')}</strong> ${this.text('directDeleteBlockedMessage', 'Cet élément possède des notes ou des données comptables irremplaçables. Utilisez l’archivage ou le transfert.', 'This item contains irreplaceable grades or accounting data. Use archiving or transfer instead.')}
                                </div>
                            ` : ''}
                            ${nameConfirmHtml}
                            <button type="button" class="btn btn-danger w-100" id="btnExecuteDirect" ${!canDirectDelete ? 'disabled' : ''}>
                                <i class="fas fa-trash-alt me-1"></i> ${this.text('confirmPermanentDelete', 'Confirmer la suppression définitive', 'Confirm permanent deletion')}
                            </button>
                        </div>
                    </div>
                </div>

                <div class="modal-footer bg-light-subtle py-2 px-4 border-top d-flex justify-content-between align-items-center">
                    <span class="text-muted small"><i class="fas fa-shield-alt text-success me-1"></i> ${this.text('noChangeWithoutValidation', 'Aucune modification ne sera appliquée sans validation.', 'No changes will be made without confirmation.')}</span>
                    <button type="button" class="btn btn-secondary px-4 rounded-pill" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i> ${this.text('cancelOperation', 'Annuler l’opération', 'Cancel operation')}
                    </button>
                </div>
            `;

            document.getElementById('impactModalBody').innerHTML = html;
            this.bindModalActions(data);
        },

        bindModalActions: function (data) {
            const self = this;

            const btnTransfer = document.getElementById('btnExecuteTransfer');
            if (btnTransfer) {
                btnTransfer.addEventListener('click', function () {
                    const targetSelect = document.getElementById('impactTargetSelect');
                    const targetId = targetSelect ? targetSelect.value : '';

                    if (!targetId || parseInt(targetId, 10) <= 0) {
                        if (targetSelect) {
                            targetSelect.classList.add('is-invalid');
                            targetSelect.focus();
                        }
                        self.showAlert('warning', self.text('destinationRequired', 'Destination requise', 'Destination required'), self.text('selectDestination', 'Veuillez sélectionner un élément de destination dans la liste avant d\'effectuer le transfert.', 'Select a destination item from the list before transferring.'));
                        return;
                    }

                    if (targetSelect) targetSelect.classList.remove('is-invalid');
                    self.submitAction('transfer', parseInt(targetId, 10));
                });
            }

            const btnArchive = document.getElementById('btnExecuteArchive');
            if (btnArchive) {
                btnArchive.addEventListener('click', function () {
                    self.submitAction('archive');
                });
            }

            const btnDirect = document.getElementById('btnExecuteDirect');
            if (btnDirect) {
                btnDirect.addEventListener('click', function () {
                    const nameInput = document.getElementById('impactConfirmName');
                    if (nameInput) {
                        if (nameInput.value.trim() !== data.entity.name.trim()) {
                            self.showAlert('warning', self.text('wrongConfirmationName', 'Nom de confirmation incorrect', 'Incorrect confirmation name'), self.text('nameMismatch', 'Le nom saisi ne correspond pas exactement à l\'élément visé.', 'The entered name does not exactly match the target item.'));
                            return;
                        }
                    }

                    self.showConfirm(self.text('deleteConfirmation', 'Confirmation de suppression', 'Confirm deletion'), self.text('sureDelete', 'Êtes-vous absolument sûr de vouloir supprimer cet élément ? Cette action est irréversible.', 'Are you absolutely sure you want to delete this item? This action cannot be undone.'), function () {
                        self.submitAction('direct');
                    });
                });
            }
        },

        submitAction: function (scenario, targetId = 0) {
            const csrfTokenInput = document.getElementById('impactCsrfToken');
            const csrfToken = csrfTokenInput ? csrfTokenInput.value : '';
            const btnSubmit = document.activeElement;
            if (btnSubmit && btnSubmit.tagName === 'BUTTON') btnSubmit.disabled = true;

            const formData = new FormData();
            formData.append('type', this.currentEntity);
            formData.append('id', this.currentId);
            formData.append('scenario', scenario);
            formData.append('target_id', targetId);
            formData.append('csrf_token', csrfToken);

            fetch('/api/smart-delete', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken
                },
                body: formData
            })
                .then(async res => {
                    const contentType = res.headers.get('content-type') || '';
                    let data = {};
                    if (contentType.includes('application/json')) {
                        data = await res.json();
                    } else {
                        const text = await res.text();
                        data = { success: false, message: text.substring(0, 300) };
                    }
                    if (!res.ok) {
                        throw new Error(data.message || this.text('serverError', 'Erreur serveur (HTTP :status)', 'Server error (HTTP :status)').replace(':status', res.status));
                    }
                    return data;
                })
                .then(res => {
                    if (res.success) {
                        this.bsModal.hide();
                        if (this.onSuccessCallback) {
                            this.onSuccessCallback(res);
                        } else {
                            if (window.Toast) {
                                Toast.success(res.message);
                            } else {
                                this.showAlert('success', this.text('operationSuccess', 'Opération réussie', 'Operation successful'), res.message);
                            }
                            setTimeout(() => window.location.reload(), 800);
                        }
                    } else {
                        this.showAlert('error', this.text('operationFailure', 'Échec de l\'opération', 'Operation failed'), res.message || this.text('operationError', 'Erreur lors du traitement.', 'Error while processing.'));
                        if (btnSubmit) btnSubmit.disabled = false;
                    }
                })
                .catch(err => {
                    console.error('Impact Delete Error:', err);
                    this.showAlert('error', this.text('processingError', 'Erreur lors du traitement', 'Error while processing'), err.message || this.text('processingFailed', 'La suppression n\'a pas pu être effectuée.', 'The deletion could not be completed.'));
                    if (btnSubmit) btnSubmit.disabled = false;
                });
        }
    };

    window.ImpactRadiography = ImpactRadiography;

    document.addEventListener('DOMContentLoaded', function () {
        ImpactRadiography.init();
    });
})();
