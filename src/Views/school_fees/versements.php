<?php
$title = __('versements_title');
ob_start();
?>

<div class="animate-fade-in container-fluid py-3 px-md-4">
    <!-- Header -->
    <div class="d-flex flex-column flex-md-row align-items-start align-items-md-center justify-content-between mb-4 gap-3">
        <div>
            <h2 class="fw-black text-main-theme mb-0 fs-4"><?= __('versements_header') ?></h2>
            <p class="text-muted-theme small mb-0"><?= __('versements_subtitle') ?></p>
        </div>
        <div class="d-flex flex-wrap gap-2 w-100 w-md-auto ms-md-auto justify-content-start justify-content-md-end">
            <button type="button" class="btn btn-primary rounded-pill px-4 fw-bold flex-fill flex-md-grow-0 text-center text-nowrap shadow-sm" data-bs-toggle="modal" data-bs-target="#newVersementModal">
                <i class="bi bi-plus-circle me-1"></i> <?= __('new_versement') ?>
            </button>
        </div>
    </div>

    <?php if ($msg = App\Core\Session::getFlash('success')): ?>
        <div class="alert alert-success border-0 shadow-sm alert-dismissible fade show rounded-4 mb-4" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i><?= htmlspecialchars((string) $msg) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    <?php if ($err = App\Core\Session::getFlash('error')): ?>
        <div class="alert alert-danger border-0 shadow-sm alert-dismissible fade show rounded-4 mb-4" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2"></i><?= htmlspecialchars((string) $err) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- Main Content Row -->
    <div class="row g-4">
        <!-- History Column -->
        <div class="col-lg-12">
            <div class="modern-card border-0 shadow-sm overflow-hidden h-100">
                <div class="p-4 border-bottom d-flex align-items-center justify-content-between">
                    <h5 class="fw-bold text-main-theme mb-0">
                        <i class="bi bi-journal-text text-primary me-2"></i><?= __('recent_versements') ?>
                    </h5>
                    <!-- Quick search for history -->
                    <div class="input-group search-pill bg-white bg-opacity-10 rounded-pill px-2 border-theme-light" style="max-width: 200px; border: 1px solid var(--border-color);">
                        <span class="input-group-text border-0 bg-transparent text-primary py-1">
                            <i class="bi bi-search"></i>
                        </span>
                        <input type="text" id="history-search-input" class="form-control border-0 bg-transparent shadow-none py-1 small text-main" placeholder="<?= __('quick_search_placeholder') ?>" style="font-size: 0.8rem;">
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table-modern" id="history-table">
                        <thead>
                            <tr>
                                <th class="ps-4"><?= __('grade_export_student') ?></th>
                                <th><?= __('class') ?></th>
                                <th><?= __('col_date') ?></th>
                                <th class="text-end"><?= __('col_amount') ?></th>
                                <th><?= __('col_method') ?></th>
                                <th><?= __('col_reference') ?></th>
                                <th class="pe-4 text-center"><?= __('actions') ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($payments)): ?>
                                <tr>
                                    <td colspan="7" class="text-center py-5 text-muted">
                                        <i class="bi bi-info-circle fs-4 d-block mb-2 text-secondary"></i>
                                        <?= __('no_versement_recorded') ?>
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($payments as $pay): ?>
                                    <tr class="payment-row <?= ($pay['status'] ?? 'valide') === 'annule' ? 'opacity-50 bg-light' : '' ?>">
                                        <td class="ps-4">
                                            <div class="d-flex align-items-center gap-2">
                                                <div class="avatar-init bg-primary bg-opacity-10 text-primary fw-bold rounded-circle d-flex align-items-center justify-content-center shadow-sm"
                                                     style="width: 32px; height: 32px; font-size: 0.85rem; border: 1px solid rgba(var(--primary-rgb), 0.2);">
                                                    <?= strtoupper(substr((string) $pay['student_nom'], 0, 1)) ?>
                                                </div>
                                                <div>
                                                    <div class="fw-bold <?= ($pay['status'] ?? 'valide') === 'annule' ? 'text-decoration-line-through text-muted' : 'text-main-theme' ?>" style="font-size: 0.85rem;">
                                                        <?= h($pay['student_nom']) ?>
                                                    </div>
                                                    <div class="text-muted opacity-75" style="font-size: 0.72rem;">
                                                        <?= h($pay['student_prenom']) ?>
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-15 px-2 py-0.5 rounded-pill small">
                                                <?= h($pay['class_name'] ?: '-') ?>
                                            </span>
                                        </td>
                                        <td>
                                            <div class="<?= ($pay['status'] ?? 'valide') === 'annule' ? 'text-decoration-line-through text-muted' : '' ?>">
                                                <?= date('d/m/Y', strtotime($pay['payment_date'])) ?>
                                            </div>
                                        </td>
                                        <td class="text-end fw-black <?= ($pay['status'] ?? 'valide') === 'annule' ? 'text-decoration-line-through text-muted' : '' ?>"><?= number_format($pay['amount'], 0, '.', ' ') ?> <span class="extra-small text-muted">FCFA</span></td>
                                        <td>
                                            <span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary border-opacity-15 px-2 py-0.5 rounded-pill small">
                                                <?= h($pay['payment_method']) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <code class="small"><?= h($pay['reference'] ?: '-') ?></code>
                                            <?php if (($pay['status'] ?? 'valide') === 'annule'): ?>
                                                <div class="text-danger small mt-1" title="<?= h($pay['cancellation_motive'] ?? '') ?>"><i class="bi bi-info-circle"></i> Annulé</div>
                                            <?php endif; ?>
                                        </td>
                                        <td class="pe-4 text-center">
                                            <div class="d-flex gap-1 justify-content-center">
                                                <a href="/school_fees/receipt?id=<?= $pay['id'] ?>" target="_blank" class="btn btn-sm btn-action-modern text-primary" title="<?= __('print_receipt') ?>">
                                                    <i class="bi bi-printer-fill fs-5"></i>
                                                </a>
                                                <?php if (($pay['status'] ?? 'valide') !== 'annule'): ?>
                                                    <button type="button" class="btn btn-sm btn-action-modern text-danger" onclick="openCancelModal(<?= $pay['id'] ?>)" title="<?= __('cancel_delete') ?>">
                                                        <i class="bi bi-trash-fill fs-5"></i>
                                                    </button>
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

<!-- Modal: Cancel Versement -->
<div class="modal fade" id="cancelVersementModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow-lg">
            <div class="modal-header border-bottom-0 pb-0 px-4 pt-4">
                <h5 class="modal-title fw-black text-danger">Annuler le versement</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="/school_fees/versements/delete" method="POST" id="cancelVersementForm">
                <input type="hidden" name="csrf_token" value="<?= \App\Core\Session::generateCsrfToken() ?>">
                <input type="hidden" name="id" id="cancelVersementId" value="">
                <div class="modal-body p-4">
                    <p class="text-muted">Veuillez indiquer le motif d'annulation (obligatoire). Le montant sera déduit du solde payé de l'élève.</p>
                    <div class="mb-3">
                        <label class="form-label text-muted-theme fw-bold extra-small text-uppercase mb-1">Motif d'annulation</label>
                        <textarea name="motive" class="form-control premium-input" rows="3" required placeholder="Saisir le motif détaillé..."></textarea>
                    </div>
                </div>
                <div class="modal-footer border-top-0 pt-0 px-4 pb-4">
                    <button type="button" class="btn btn-light-theme rounded-pill px-4" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-danger rounded-pill px-5 fw-bold shadow-sm">
                        <i class="bi bi-trash-fill me-2"></i>Confirmer l'annulation
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<script>
function openCancelModal(paymentId) {
    document.getElementById('cancelVersementId').value = paymentId;
    const modal = new bootstrap.Modal(document.getElementById('cancelVersementModal'));
    modal.show();
}
</script>

<!-- Modal Nouveau Versement -->
<div class="modal fade" id="newVersementModal" tabindex="-1" aria-labelledby="newVersementModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold text-main-theme" id="newVersementModalLabel">
                    <i class="bi bi-plus-circle-fill text-primary me-2"></i><?= __('new_versement') ?>
                </h5>
                <button type="button" class="btn-close shadow-none" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <form action="/school_fees/versements/store" method="POST" id="versement-form">
                    <input type="hidden" name="csrf_token" value="<?= \App\Core\Session::generateCsrfToken() ?>">

                    <!-- Search Input for Student -->
                    <div class="mb-2">
                        <label class="form-label text-muted-theme fw-bold extra-small text-uppercase mb-1"><?= __('search_student') ?></label>
                        <div class="input-group search-pill bg-white bg-opacity-10 rounded-pill px-2 border-theme-light" style="border: 1px solid var(--border-color);">
                            <span class="input-group-text border-0 bg-transparent text-primary py-1">
                                <i class="bi bi-search"></i>
                            </span>
                            <input type="text" id="student-search-select" class="form-control border-0 bg-transparent shadow-none py-1 small text-main" placeholder="<?= __('search_by_name_class') ?>" style="font-size: 0.82rem;">
                        </div>
                    </div>

                    <!-- Student Select Dropdown -->
                    <div class="mb-3">
                        <label class="form-label text-muted-theme fw-bold extra-small text-uppercase mb-1"><?= __('select_student_label') ?></label>
                        <select name="student_id" id="student_id" class="form-select premium-input" required>
                            <option value="" disabled selected><?= __('choose_student_placeholder') ?></option>
                            <?php foreach ($groupedStudents as $cName => $studentsList): ?>
                                <optgroup label="<?= h($cName) ?>">
                                    <?php foreach ($studentsList as $stud): ?>
                                        <option value="<?= $stud['id'] ?>" data-balance="<?= (float)$stud['reste_a_payer'] ?>">
                                            <?= h($stud['nom'] . ' ' . $stud['prenom']) ?> (<?= __('solde') ?> <?= number_format($stud['reste_a_payer'], 0, '.', ' ') ?> FCFA)
                                        </option>
                                    <?php endforeach; ?>
                                </optgroup>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Montant -->
                    <div class="mb-3">
                        <label class="form-label text-muted-theme fw-bold extra-small text-uppercase mb-1"><?= __('amount_paid_fcfa') ?></label>
                        <input type="number" name="amount" id="amount" min="1" class="form-control premium-input fw-bold fs-5 text-primary text-end" required placeholder="0">
                        <div class="extra-small text-muted text-end mt-1 d-none" id="balance-warning-msg"></div>
                    </div>

                    <!-- Date -->
                    <div class="mb-3">
                        <label class="form-label text-muted-theme fw-bold extra-small text-uppercase mb-1"><?= __('payment_date_label') ?></label>
                        <input type="date" name="payment_date" class="form-control premium-input" required value="<?= date('Y-m-d') ?>">
                    </div>

                    <!-- Mode de paiement -->
                    <div class="mb-3">
                        <label class="form-label text-muted-theme fw-bold extra-small text-uppercase mb-1"><?= __('payment_method_label') ?></label>
                        <select name="payment_method" id="payment_method" class="form-select premium-input" required>
                            <option value="ESPECES" selected><?= __('cash_payment') ?></option>
                            <option value="ORANGE_MONEY"><?= __('orange_money') ?></option>
                            <option value="MTN_MOMO"><?= __('mtn_momo') ?></option>
                            <option value="CARTE_BANCAIRE"><?= __('card_banking') ?></option>
                            <option value="VIREMENT_BANCAIRE"><?= __('transfer_banking') ?></option>
                            <option value="CHEQUE"><?= __('check_payment') ?></option>
                            <option value="AUTRE"><?= __('other_payment') ?></option>
                        </select>
                    </div>

                    <!-- Reference -->
                    <div class="mb-3">
                        <label class="form-label text-muted-theme fw-bold extra-small text-uppercase mb-1" id="reference_label"><?= __('payment_reference_label') ?></label>
                        <input type="text" name="reference" id="reference" class="form-control premium-input" placeholder="<?= __('payment_reference_hint') ?>">
                    </div>

                    <!-- Observation -->
                    <div class="mb-3">
                        <label class="form-label text-muted-theme fw-bold extra-small text-uppercase mb-1"><?= __('observation') ?></label>
                        <textarea name="observation" class="form-control premium-input" rows="2" placeholder="<?= __('observation_placeholder') ?>"></textarea>
                    </div>

                    <!-- Validation button -->
                    <div class="mt-4 pt-3 border-top text-end">
                        <button type="submit" class="btn btn-primary rounded-pill px-5 fw-bold shadow-sm">
                            <i class="bi bi-check-circle-fill me-2"></i><?= __('btn_save') ?>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchSelect = document.getElementById('student-search-select');
    const selectStudent = document.getElementById('student_id');
    const amountInput = document.getElementById('amount');
    const balanceWarning = document.getElementById('balance-warning-msg');

    // Filter student select dropdown options based on search input
    if (searchSelect && selectStudent) {
        // Keep original options list
        const groups = Array.from(selectStudent.querySelectorAll('optgroup'));
        const originalOptions = groups.map(g => {
            return {
                label: g.label,
                options: Array.from(g.querySelectorAll('option'))
            };
        });

        searchSelect.addEventListener('input', function(e) {
            const query = e.target.value.toLowerCase().normalize("NFD").replace(/[\u0300-\u036f]/g, "").trim();
            selectStudent.innerHTML = '<option value="" disabled selected><?= __('choose_student_placeholder') ?></option>';
            
            originalOptions.forEach(group => {
                const filteredOpts = group.options.filter(opt => {
                    return opt.textContent.toLowerCase().normalize("NFD").replace(/[\u0300-\u036f]/g, "").includes(query) ||
                           group.label.toLowerCase().normalize("NFD").replace(/[\u0300-\u036f]/g, "").includes(query);
                });

                if (filteredOpts.length > 0) {
                    const optGroup = document.createElement('optgroup');
                    optGroup.label = group.label;
                    filteredOpts.forEach(opt => {
                        const newOpt = document.createElement('option');
                        newOpt.value = opt.value;
                        newOpt.textContent = opt.textContent;
                        newOpt.setAttribute('data-balance', opt.getAttribute('data-balance'));
                        optGroup.appendChild(newOpt);
                    });
                    selectStudent.appendChild(optGroup);
                }
            });
        });
    }

    // Display student balance warning when student is selected
    selectStudent?.addEventListener('change', function() {
        const selectedOpt = this.options[this.selectedIndex];
        const balance = parseFloat(selectedOpt.getAttribute('data-balance')) || 0;
        
        if (balance > 0) {
            balanceWarning.textContent = `<?= __('remaining_to_pay') ?> ${balance.toLocaleString('fr-FR')} FCFA`;
            balanceWarning.classList.remove('d-none');
            amountInput.max = balance;
            amountInput.value = balance;
        } else {
            balanceWarning.textContent = '';
            balanceWarning.classList.add('d-none');
            amountInput.removeAttribute('max');
            amountInput.value = '';
        }
    });

    // History list search filter
    const historySearch = document.getElementById('history-search-input');
    if (historySearch) {
        historySearch.addEventListener('input', function(e) {
            const query = e.target.value.toLowerCase().normalize("NFD").replace(/[\u0300-\u036f]/g, "").trim();
            const rows = document.querySelectorAll('#history-table tbody tr');
            
            rows.forEach(row => {
                if (row.querySelector('td[colspan]')) return; // ignore empty state
                const text = row.textContent.toLowerCase().normalize("NFD").replace(/[\u0300-\u036f]/g, "");
                if (text.includes(query)) {
                    row.style.setProperty('display', '', 'important');
                } else {
                    row.style.setProperty('display', 'none', 'important');
                }
            });
        });
    }

    // Dynamic reference fields based on payment method
    const paymentMethodSelect = document.getElementById('payment_method');
    const referenceInput = document.getElementById('reference');
    const referenceLabel = document.getElementById('reference_label');

    function updateReferenceField() {
        if (!paymentMethodSelect || !referenceInput) return;
        const val = paymentMethodSelect.value.toLowerCase();
        
        referenceInput.required = false;
        referenceInput.disabled = false;
        referenceInput.placeholder = "<?= __('optional_ref_placeholder') ?>";
        referenceLabel.innerHTML = "<?= __('payment_reference_label') ?>";

        if (val.includes('espece') || val.includes('espèces') || val.includes('cash')) {
            referenceLabel.innerHTML = '<?= __('payment_reference_label') ?> <span class="text-success">(<?= __('auto_generated') ?>)</span>';
            referenceInput.placeholder = "<?= __('auto_generated_placeholder') ?>";
            referenceInput.value = "";
            referenceInput.disabled = true;
        } else if (val.includes('orange') || val.includes('mtn') || val.includes('mobile') || val.includes('momo') || val.includes('money')) {
            referenceLabel.innerHTML = '<?= __('transaction_number') ?> <span class="text-muted">(<?= __('optional_ref') ?>)</span>';
            referenceInput.placeholder = "Ex: TX-98471...";
        } else if (val.includes('chèque') || val.includes('cheque')) {
            referenceLabel.innerHTML = '<?= __('check_number') ?> <span class="text-muted">(<?= __('optional_ref') ?>)</span>';
            referenceInput.placeholder = "Ex: CHQ-001234";
        } else if (val.includes('virement') || val.includes('carte') || val.includes('bancaire') || val.includes('transfert')) {
            referenceLabel.innerHTML = '<?= __('bank_reference') ?> <span class="text-muted">(<?= __('optional_ref') ?>)</span>';
            referenceInput.placeholder = "Ex: VIR-847294...";
        }
    }

    if (paymentMethodSelect) {
        paymentMethodSelect.addEventListener('change', updateReferenceField);
        updateReferenceField(); // Run once on load to set initial state
    }
});
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../templates/layout.php';
?>
