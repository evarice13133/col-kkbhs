<?php
$title = "Fiche Financière - " . h($student['nom']);
ob_start();
?>

<div class="animate-fade-in container-fluid py-3 px-md-4">
    <!-- Breadcrumb & Back -->
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1" style="font-size: 0.75rem; font-weight: 500;">
                    <li class="breadcrumb-item"><a href="/payments" class="text-decoration-none text-muted">Registre Financier</a></li>
                    <li class="breadcrumb-item active text-primary" aria-current="page">Fiche Élève</li>
                </ol>
            </nav>
            <h2 class="fw-black text-main-theme mb-0 fs-4">Fiche Financière Individuelle</h2>
        </div>
        <a href="/payments" class="btn btn-sm btn-light-theme rounded-pill px-3">
            <i class="bi bi-arrow-left me-1"></i> Retour au registre
        </a>
    </div>

    <!-- Student Info Profile Header Card -->
    <div class="modern-card border-0 shadow-sm p-4 mb-4" style="background: linear-gradient(135deg, var(--bg-card) 0%, color-mix(in srgb, var(--bg-card) 95%, var(--primary-color)) 100%);">
        <div class="d-flex align-items-center gap-3 flex-wrap">
            <div class="avatar-init bg-primary text-white fw-bold rounded-circle d-flex align-items-center justify-content-center shadow-lg"
                 style="width: 70px; height: 70px; font-size: 2rem; border: 3px solid #fff; background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));">
                <?= strtoupper(substr((string) $student['nom'], 0, 1)) ?>
            </div>
            <div>
                <h3 class="fw-black text-main-theme mb-1 fs-5"><?= h($student['nom']) ?> <?= h($student['prenom']) ?></h3>
                <div class="d-flex gap-2 align-items-center flex-wrap">
                    <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 rounded-pill px-3 py-1.5 fw-bold fs-8">
                        <i class="bi bi-door-open-fill me-1"></i><?= h($student['classe_nom'] ?: 'Aucune classe') ?>
                    </span>
                    <span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary border-opacity-25 rounded-pill px-3 py-1.5 fw-bold fs-8">
                        Matricule: <?= h($student['matricule']) ?>
                    </span>
                </div>
            </div>
            <div class="ms-md-auto">
                <button type="button" class="btn btn-primary rounded-pill px-4 py-2 fw-bold shadow-sm" data-bs-toggle="modal" data-bs-target="#addPaymentModal">
                    <i class="bi bi-plus-circle-fill me-2"></i> Enregistrer un versement
                </button>
            </div>
        </div>
    </div>

    <!-- Core Balance Statistics (KPI Cards) -->
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="modern-card kpi-card border-0 shadow-sm">
                <div class="kpi-icon-wrapper bg-secondary bg-opacity-10 text-secondary">
                    <i class="bi bi-wallet2"></i>
                </div>
                <div class="kpi-value text-secondary"><?= number_format($student['frais_scolarite_brut'], 0, '.', ' ') ?> <span class="fs-7 text-muted fw-normal">FCFA</span></div>
                <div class="kpi-label">Scolarité Brut</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="modern-card kpi-card border-0 shadow-sm">
                <div class="kpi-icon-wrapper bg-danger bg-opacity-10 text-danger">
                    <i class="bi bi-percent"></i>
                </div>
                <?php $totDeductions = (float)$student['total_reductions'] + (float)$student['total_bourses']; ?>
                <div class="kpi-value text-danger">-<?= number_format($totDeductions, 0, '.', ' ') ?> <span class="fs-7 text-muted fw-normal">FCFA</span></div>
                <div class="kpi-label">Réductions & Bourses</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="modern-card kpi-card border-0 shadow-sm border-start border-4 border-success">
                <div class="kpi-icon-wrapper bg-success bg-opacity-10 text-success">
                    <i class="bi bi-check-circle"></i>
                </div>
                <div class="kpi-value text-success"><?= number_format($student['total_paye'], 0, '.', ' ') ?> <span class="fs-7 text-muted fw-normal">FCFA</span></div>
                <div class="kpi-label">Total Déjà Réglé</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="modern-card kpi-card border-0 shadow-sm border-start border-4 border-danger">
                <div class="kpi-icon-wrapper bg-danger bg-opacity-10 text-danger">
                    <i class="bi bi-exclamation-triangle"></i>
                </div>
                <div class="kpi-value text-danger"><?= number_format($student['reste_a_payer'], 0, '.', ' ') ?> <span class="fs-7 text-muted fw-normal">FCFA</span></div>
                <div class="kpi-label">Reste à payer</div>
            </div>
        </div>
    </div>

    <!-- Installments and History Grid -->
    <div class="row g-4 mb-4">
        <!-- Installments Plan -->
        <div class="col-lg-5">
            <div class="modern-card border-0 shadow-sm p-4 h-100">
                <div class="d-flex align-items-center justify-content-between mb-4 border-bottom border-theme-dynamic pb-2">
                    <h5 class="fw-black text-secondary m-0 text-uppercase letter-spacing-1 fs-6">Plan de Règlement</h5>
                </div>
                
                <?php if (empty($installments)): ?>
                    <div class="text-center py-5 text-muted small">
                        <i class="bi bi-info-circle fs-4 d-block mb-2 text-secondary"></i>
                        Aucune tranche planifiée. La scolarité nette est due en une seule fois.
                    </div>
                <?php else: ?>
                    <div class="list-group list-group-flush">
                        <?php foreach ($installments as $inst): ?>
                            <?php 
                            $amtPlanned = (float)$inst['amount_planned'];
                            $amtPaid = (float)$inst['amount_paid'];
                            $amtRem = max(0.0, $amtPlanned - $amtPaid);
                            
                            if ($amtPaid == 0) {
                                $badgeClass = 'bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25';
                                $statusTxt = 'Non payé';
                            } elseif ($amtRem > 0) {
                                $badgeClass = 'bg-warning bg-opacity-10 text-warning border border-warning border-opacity-25';
                                $statusTxt = 'Partiel';
                            } else {
                                $badgeClass = 'bg-success bg-opacity-10 text-success border border-success border-opacity-25';
                                $statusTxt = 'Réglé';
                            }
                            ?>
                            <div class="list-group-item d-flex justify-content-between align-items-center px-0 py-3 border-bottom border-theme-dynamic">
                                <div>
                                    <div class="fw-bold text-main-theme">Tranche <?= $inst['installment_number'] ?></div>
                                    <div class="text-muted extra-small">
                                        Montant planifié : <?= number_format($amtPlanned, 0, '.', ' ') ?> FCFA
                                    </div>
                                </div>
                                <div class="text-end me-3">
                                    <div class="fw-bold text-success" style="font-size: 0.85rem;"><?= number_format($amtPaid, 0, '.', ' ') ?> FCFA réglés</div>
                                    <?php if ($amtRem > 0): ?>
                                        <div class="text-danger extra-small">Solde dû : <?= number_format($amtRem, 0, '.', ' ') ?> FCFA</div>
                                    <?php endif; ?>
                                </div>
                                <span class="badge rounded-pill px-3 py-1.5 fw-bold fs-8 <?= $badgeClass ?>"><?= $statusTxt ?></span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Payments Ledger -->
        <div class="col-lg-7">
            <div class="modern-card border-0 shadow-sm overflow-hidden h-100">
                <div class="pt-4 px-4 pb-2 border-bottom border-theme-dynamic">
                    <h5 class="fw-black text-secondary m-0 text-uppercase letter-spacing-1 fs-6">Historique des Règlements</h5>
                </div>
                <div class="table-responsive">
                    <table class="table-modern">
                        <thead>
                            <tr>
                                <th class="ps-4">Date</th>
                                <th>Type</th>
                                <th class="text-end">Montant</th>
                                <th>Mode</th>
                                <th>Référence / Notes</th>
                                <th class="pe-4 text-center">Reçu</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($payments)): ?>
                                <tr>
                                    <td colspan="6" class="text-center py-5 text-muted small">
                                        Aucun versement enregistré pour cette année.
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($payments as $p): ?>
                                    <tr class="student-row">
                                        <td class="ps-4">
                                            <div class="fw-bold text-main-theme"><?= date('d/m/Y', strtotime($p['payment_date'])) ?></div>
                                        </td>
                                        <td>
                                            <?php if ($p['type'] === 'inscription'): ?>
                                                <span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary border-opacity-25 px-2 py-1 rounded-pill">Inscription</span>
                                            <?php else: ?>
                                                <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 px-2 py-1 rounded-pill">Scolarité</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-end fw-black text-main-theme"><?= number_format($p['amount'], 0, '.', ' ') ?> FCFA</td>
                                        <td>
                                            <?php 
                                            $method = strtoupper($p['payment_method']);
                                            if ($method === 'CASH') {
                                                echo '<span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 px-2.5 py-1 rounded-pill"><i class="bi bi-cash me-1"></i> Espèces</span>';
                                            } elseif ($method === 'OM') {
                                                echo '<span class="badge bg-warning bg-opacity-10 text-warning border border-warning border-opacity-25 px-2.5 py-1 rounded-pill"><i class="bi bi-phone me-1"></i> Orange Money</span>';
                                            } elseif ($method === 'MOMO') {
                                                echo '<span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 px-2.5 py-1 rounded-pill"><i class="bi bi-phone me-1"></i> MTN MoMo</span>';
                                            } elseif ($method === 'TRANSFER') {
                                                echo '<span class="badge bg-info bg-opacity-10 text-info border border-info border-opacity-25 px-2.5 py-1 rounded-pill"><i class="bi bi-bank me-1"></i> Virement</span>';
                                            } elseif ($method === 'CHEQUE') {
                                                echo '<span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary border-opacity-25 px-2.5 py-1 rounded-pill"><i class="bi bi-card-text me-1"></i> Chèque</span>';
                                            } else {
                                                echo '<span class="badge bg-light text-dark border px-2.5 py-1 rounded-pill">' . h($method) . '</span>';
                                            }
                                            ?>
                                        </td>
                                        <td>
                                            <div class="text-truncate" style="max-width: 150px;" title="<?= h($p['reference'] ?: '') ?>">
                                                <?= h($p['reference'] ?: ($p['commentaire'] ?: '-')) ?>
                                            </div>
                                        </td>
                                        <td class="pe-4 text-center">
                                            <div class="d-flex gap-1 justify-content-center">
                                                <a href="/payments/receipt?id=<?= $p['id'] ?>" target="_blank" class="btn btn-sm btn-action-modern text-primary" title="Imprimer le reçu">
                                                    <i class="bi bi-printer-fill fs-5"></i>
                                                </a>
                                                <?php if (\App\Core\Session::get('user_role') === 'superadmin'): ?>
                                                    <a href="/payments/delete?id=<?= $p['id'] ?>" class="btn btn-sm btn-action-modern text-danger" 
                                                       onclick="return confirm('Êtes-vous sûr de vouloir supprimer et annuler ce versement ?');" title="Annuler le versement">
                                                        <i class="bi bi-trash-fill fs-5"></i>
                                                    </a>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Add Payment -->
<div class="modal fade" id="addPaymentModal" tabindex="-1" aria-labelledby="addPaymentModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow-lg">
            <div class="modal-header border-bottom-0 pb-0 px-4 pt-4">
                <h5 class="modal-title fw-black text-main-theme" id="addPaymentModalLabel">Saisir un Versement</h5>
                <button type="button" class="btn-close" data-bs-shadow="none" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="/payments/store" method="POST" id="paymentForm">
                <input type="hidden" name="csrf_token" value="<?= \App\Core\Session::generateCsrfToken() ?>">
                <input type="hidden" name="student_id" value="<?= $student['id'] ?>">

                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label text-muted-theme fw-bold extra-small text-uppercase mb-1">Type de Paiement *</label>
                            <select name="type" class="form-select premium-input" required id="payment_type_select">
                                <option value="scolarite">Frais de Scolarité</option>
                                <option value="inscription">Frais d'Inscription</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label text-muted-theme fw-bold extra-small text-uppercase mb-1">Montant à Encaisser (FCFA) *</label>
                            <input type="number" name="amount" min="1" step="50" class="form-control premium-input fw-bold text-primary fs-5" required id="amount_input">
                            <div class="form-text extra-small opacity-75 animate-pulse" id="suggested_amount_help">
                                Scolarité restante à payer : <strong><?= number_format($student['reste_a_payer'], 0, '.', ' ') ?></strong> FCFA
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted-theme fw-bold extra-small text-uppercase mb-1">Mode de règlement *</label>
                            <select name="payment_method" id="payment_method_select" class="form-select premium-input" required>
                                <option value="CASH">Espèces</option>
                                <option value="OM">Orange Money</option>
                                <option value="MOMO">MTN Mobile Money</option>
                                <option value="TRANSFER">Virement Bancaire</option>
                                <option value="CHEQUE">Chèque</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted-theme fw-bold extra-small text-uppercase mb-1">Date de paiement *</label>
                            <input type="date" name="payment_date" class="form-control premium-input" required value="<?= date('Y-m-d') ?>">
                        </div>
                        <div class="col-12">
                            <label class="form-label text-muted-theme fw-bold extra-small text-uppercase mb-1" id="reference_label">Référence du paiement</label>
                            <input type="text" name="reference" id="reference_input" class="form-control premium-input" placeholder="Ex: N° transaction, n° chèque...">
                        </div>
                        <div class="col-12">
                            <label class="form-label text-muted-theme fw-bold extra-small text-uppercase mb-1">Commentaire / Observation</label>
                            <textarea name="commentaire" class="form-control premium-input" rows="2" placeholder="Informations complémentaires..."></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-top-0 pt-0 px-4 pb-4">
                    <button type="button" class="btn btn-light-theme rounded-pill px-4" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-5 fw-bold shadow-sm">
                        <i class="bi bi-check-circle-fill me-2"></i>Valider l'Encaissement
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const typeSelect = document.getElementById('payment_type_select');
    const amountHelp = document.getElementById('suggested_amount_help');
    const amountInput = document.getElementById('amount_input');

    const restTuition = <?= (float)$student['reste_a_payer'] ?>;
    const classRegistration = <?= (float)$student['class_frais_inscription'] ?>;
    const paidRegistration = <?= (float)$totalPaidRegistration ?>;
    const restRegistration = Math.max(0, classRegistration - paidRegistration);

    typeSelect.addEventListener('change', function() {
        if (this.value === 'inscription') {
            amountHelp.innerHTML = `Frais d'inscription restants : <strong class="text-danger">${formatNumber(restRegistration)}</strong> FCFA`;
            amountInput.value = restRegistration;
        } else {
            amountHelp.innerHTML = `Scolarité restante à payer : <strong class="text-danger">${formatNumber(restTuition)}</strong> FCFA`;
            amountInput.value = restTuition > 0 ? restTuition : '';
        }
    });

    // Default pre-fill
    amountInput.value = restTuition > 0 ? restTuition : '';

    // Dynamic reference fields based on payment method
    const paymentMethodSelect = document.getElementById('payment_method_select');
    const referenceInput = document.getElementById('reference_input');
    const referenceLabel = document.getElementById('reference_label');

    function updateReferenceField() {
        if (!paymentMethodSelect || !referenceInput) return;
        const val = paymentMethodSelect.value.toLowerCase();
        
        referenceInput.required = false;
        referenceInput.disabled = false;
        referenceInput.placeholder = "Ex: N° transaction, n° chèque...";
        referenceLabel.innerHTML = 'Référence du paiement';

        if (val.includes('cash') || val.includes('espece') || val.includes('espèces')) {
            referenceLabel.innerHTML = 'Référence du paiement <span class="text-success">(Générée automatiquement)</span>';
            referenceInput.placeholder = "Générée automatiquement par le système";
            referenceInput.value = "";
            referenceInput.disabled = true;
        } else if (val.includes('om') || val.includes('momo') || val.includes('orange') || val.includes('mtn') || val.includes('mobile')) {
            referenceLabel.innerHTML = 'Numéro de transaction <span class="text-muted">(optionnel)</span>';
            referenceInput.placeholder = "Ex: TX-98471...";
        } else if (val.includes('cheque') || val.includes('chèque')) {
            referenceLabel.innerHTML = 'Numéro de chèque <span class="text-muted">(optionnel)</span>';
            referenceInput.placeholder = "Ex: CHQ-001234";
        } else if (val.includes('transfer') || val.includes('virement') || val.includes('bancaire')) {
            referenceLabel.innerHTML = 'Référence bancaire <span class="text-muted">(optionnelle)</span>';
            referenceInput.placeholder = "Ex: VIR-847294...";
        }
    }

    if (paymentMethodSelect) {
        paymentMethodSelect.addEventListener('change', updateReferenceField);
        updateReferenceField(); // Run once on load to set initial state
    }

    function formatNumber(val) {
        return new Intl.NumberFormat('fr-FR').format(val);
    }
});
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../templates/layout.php';
?>
