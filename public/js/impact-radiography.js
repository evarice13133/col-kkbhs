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
                    confirmButtonText: 'Oui, confirmer',
                    cancelButtonText: 'Annuler',
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
                    confirmButtonText: 'Oui, me continuer',
                    cancelButtonText: 'Annuler',
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
                    if (!res.ok) throw new Error('Erreur lors de la récupération des données d\'impact.');
                    return res.json();
                })
                .then(data => {
                    if (data.error) {
                        this.showAlert('error', 'Erreur d\'analyse', data.message || 'Impossible d\'analyser cet élément.');
                        this.bsModal.hide();
                        return;
                    }
                    this.currentAnalysis = data;
                    this.renderAnalysis(data);
                })
                .catch(err => {
                    console.error('Impact Radiography Error:', err);
                    this.showAlert('error', 'Erreur réseau', err.message || 'Impossible d\'accéder au serveur.');
                    this.bsModal.hide();
                });
        },

        showSkeletonLoading: function () {
            document.getElementById('impactModalBody').innerHTML = `
                <div class="p-4 text-center position-relative">
                    <button type="button" class="btn-close position-absolute top-0 end-0 m-3" data-bs-dismiss="modal" aria-label="Annuler"></button>
                    <div class="spinner-border text-primary mb-3" role="status" style="width: 3rem; height: 3rem;"></div>
                    <h6 class="fw-bold text-secondary">Radiographie d'Impact en cours...</h6>
                    <p class="text-muted small mb-3">Analyse dynamique de l'arborescence des dépendances en base de données.</p>
                    <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-3" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i> Annuler
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
            let riskBadgeLabel = 'Risque Faible';
            let riskIcon = 'fas fa-shield-alt';

            if (risk === 'medium') {
                riskBadgeClass = 'risk-medium';
                riskBadgeLabel = 'Risque Moyen';
                riskIcon = 'fas fa-exclamation-circle';
            } else if (risk === 'high') {
                riskBadgeClass = 'risk-high';
                riskBadgeLabel = 'Risque Élevé';
                riskIcon = 'fas fa-exclamation-triangle';
            } else if (risk === 'critical') {
                riskBadgeClass = 'risk-critical';
                riskBadgeLabel = 'Risque Critique';
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
                        <div><strong>Élément cible :</strong> ${summary.direct_deletion || entity.name}</div>
                    </div>
                    <div class="impact-summary-item text-warning">
                        <i class="fas fa-link"></i>
                        <div><strong>Dépendances détectées :</strong> ${summary.dependencies || 'Aucune'}</div>
                    </div>
                    <div class="impact-summary-item text-info">
                        <i class="fas fa-history"></i>
                        <div><strong>Données liées / Historique :</strong> ${summary.historical_data || 'Aucun'}</div>
                    </div>
                    <div class="impact-summary-item text-secondary">
                        <i class="fas fa-unlink"></i>
                        <div><strong>Impact des références :</strong> ${summary.invalid_references || 'Aucun'}</div>
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
                            <option value="">-- Sélectionner un élément de remplacement --</option>
                            ${transfer.items.map(it => `<option value="${it.id}">${it.name}</option>`).join('')}
                        </select>
                        <div class="form-text text-muted small">Les dépendances seront immédiatement transférées à la cible choisie.</div>
                    </div>
                `;
            } else {
                transferHtml = `
                    <div class="alert alert-info py-2 small">
                        <i class="fas fa-info-circle me-1"></i> Aucun élément équivalent disponible pour le transfert automatique.
                    </div>
                `;
            }

            // Validation de nom pour risques élevés
            let nameConfirmHtml = '';
            if (risk === 'critical' || risk === 'high') {
                nameConfirmHtml = `
                    <div class="mb-3 p-3 bg-danger-subtle rounded-3 border border-danger">
                        <label class="form-label fw-bold text-danger small">
                            <i class="fas fa-lock me-1"></i> Confirmation explicite requise pour niveau ${riskBadgeLabel} :
                        </label>
                        <p class="small text-muted mb-2">Veuillez saisir exactement <strong>${entity.name}</strong> pour autoriser la suppression directe :</p>
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
                            <button type="button" class="btn-close ms-2" data-bs-dismiss="modal" aria-label="Annuler"></button>
                        </div>
                    </div>
                    <div class="modal-impact-title">${entity.name}</div>
                    ${entity.subtext ? `<div class="modal-impact-subtitle">${entity.subtext}</div>` : ''}
                </div>

                <div class="modal-body p-4">
                    ${statsHtml}
                    
                    <h6 class="fw-bold mb-2 text-dark small text-uppercase tracking-wider">Bilan d'impact dynamique :</h6>
                    ${summaryHtml}

                    <!-- Tabs Scénarios -->
                    <ul class="nav nav-tabs impact-nav-tabs mb-3" role="tablist">
                        <li class="nav-item">
                            <button class="nav-link active" id="tab-smart" data-bs-toggle="tab" data-bs-target="#content-smart" type="button">
                                <i class="fas fa-magic me-1"></i> 1. Suppression Intelligente (Recommandé)
                            </button>
                        </li>
                        <li class="nav-item">
                            <button class="nav-link nav-direct" id="tab-direct" data-bs-toggle="tab" data-bs-target="#content-direct" type="button">
                                <i class="fas fa-trash-alt me-1"></i> 2. Suppression Directe
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
                                        <i class="fas fa-exchange-alt me-1"></i> Transférer les données & Réaffecter
                                    </button>
                                ` : ''}
                                ${['student', 'teacher', 'user', 'class', 'subject', 'academic_year'].includes(entity.type) ? `
                                    <button type="button" class="btn btn-warning flex-grow-1" id="btnExecuteArchive">
                                        <i class="fas fa-archive me-1"></i> Archiver / Désactiver cet élément
                                    </button>
                                ` : `
                                    <button type="button" class="btn btn-primary flex-grow-1" id="btnExecuteArchive">
                                        <i class="fas fa-check-circle me-1"></i> Supprimer en toute sécurité
                                    </button>
                                `}
                            </div>
                        </div>

                        <!-- Scenario 2: Direct Delete -->
                        <div class="tab-pane fade" id="content-direct">
                            ${!canDirectDelete ? `
                                <div class="alert alert-danger py-2 small mb-3">
                                    <i class="fas fa-ban me-1"></i> <strong>Suppression directe bloquée :</strong> Cet élément possède des notes ou données comptables irremplaçables. Utilisez l'archivage ou le transfert.
                                </div>
                            ` : ''}
                            ${nameConfirmHtml}
                            <button type="button" class="btn btn-danger w-100" id="btnExecuteDirect" ${!canDirectDelete ? 'disabled' : ''}>
                                <i class="fas fa-trash-alt me-1"></i> Confirmer la suppression définitive
                            </button>
                        </div>
                    </div>
                </div>

                <div class="modal-footer bg-light-subtle py-2 px-4 border-top d-flex justify-content-between align-items-center">
                    <span class="text-muted small"><i class="fas fa-shield-alt text-success me-1"></i> Aucune modification ne sera appliquée sans validation.</span>
                    <button type="button" class="btn btn-secondary px-4 rounded-pill" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i> Annuler l'opération
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
                        self.showAlert('warning', 'Destination requise', 'Veuillez sélectionner un élément de destination dans la liste déroulante avant d\'effectuer le transfert.');
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
                            self.showAlert('warning', 'Nom de confirmation incorrect', 'Le nom saisi ne correspond pas exactement à l\'élément visé.');
                            return;
                        }
                    }

                    self.showConfirm('Confirmation de suppression', 'Êtes-vous absolument sûr de vouloir supprimer cet élément ? Cette action est irréversible.', function () {
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
                        throw new Error(data.message || `Erreur serveur (HTTP ${res.status})`);
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
                                this.showAlert('success', 'Opération réussie', res.message);
                            }
                            setTimeout(() => window.location.reload(), 800);
                        }
                    } else {
                        this.showAlert('error', 'Échec de l\'opération', res.message || 'Erreur lors du traitement.');
                        if (btnSubmit) btnSubmit.disabled = false;
                    }
                })
                .catch(err => {
                    console.error('Impact Delete Error:', err);
                    this.showAlert('error', 'Erreur lors du traitement', err.message || 'La suppression n\'a pas pu être effectuée.');
                    if (btnSubmit) btnSubmit.disabled = false;
                });
        }
    };

    window.ImpactRadiography = ImpactRadiography;

    document.addEventListener('DOMContentLoaded', function () {
        ImpactRadiography.init();
    });
})();
