<?php
$title = __('enroll_student');
$formData = $formData ?? [];
$selectedCycle = (string) ($formData['cycle_id'] ?? '');
$selectedSection = (string) ($formData['section_id'] ?? '');
$selectedDepartment = (string) ($formData['department_id'] ?? '');
$selectedClass = (string) ($formData['class_id'] ?? '');
$selectedTeachingType = (string) ($formData['teaching_type_id'] ?? '');
$selectedSexe = (string) ($formData['sexe'] ?? '');
$isRedoublant = (string) ($formData['is_redoublant'] ?? '0');

$settingsStore = new \App\Services\SettingsStore($this->db);
$registration_fee_policy = $settingsStore->get('registration_fee_policy', 'all');
$payment_methods_str = $settingsStore->get('payment_methods', 'Espèces,Mobile Money,Orange Money,MTN Mobile Money,Carte bancaire,Virement bancaire,Chèque,Autre');
$payment_methods = array_map('trim', explode(',', $payment_methods_str));

ob_start();
?>

<style>
/* Stepper CSS (Respecting Global Theme) */
.stepper-wrapper {
  display: flex;
  justify-content: space-between;
  margin-bottom: 30px;
  position: relative;
  padding: 0 10px;
}
.stepper-wrapper::before {
  content: '';
  position: absolute;
  top: 20px;
  left: 30px;
  right: 30px;
  height: 2px;
  background: var(--bs-border-color);
  z-index: 0;
}
.stepper-progress {
  position: absolute;
  top: 20px;
  left: 30px;
  height: 2px;
  background: var(--bs-primary);
  z-index: 0;
  transition: width 0.4s ease;
}
.stepper-item {
  position: relative;
  z-index: 1;
  display: flex;
  flex-direction: column;
  align-items: center;
  flex: 1;
  cursor: pointer;
}
.stepper-circle {
  width: 40px;
  height: 40px;
  border-radius: 50%;
  background: var(--bs-body-bg);
  border: 2px solid var(--bs-border-color);
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: bold;
  color: var(--bs-body-color);
  transition: all 0.3s ease;
}
.stepper-item.active .stepper-circle {
  background: var(--bs-primary);
  border-color: var(--bs-primary);
  color: #fff;
  transform: scale(1.1);
}
.stepper-item.completed .stepper-circle {
  background: var(--bs-success);
  border-color: var(--bs-success);
  color: #fff;
}
.stepper-title {
  margin-top: 8px;
  font-size: 0.8rem;
  font-weight: 600;
  color: var(--bs-secondary-color);
  text-transform: uppercase;
  transition: color 0.3s ease;
}
.stepper-item.active .stepper-title {
  color: var(--bs-primary);
}
.stepper-item.completed .stepper-title {
  color: var(--bs-success);
}
.form-step {
  display: none;
  animation: fadeIn 0.4s ease forwards;
}
.form-step.active {
  display: block;
}
@keyframes fadeIn {
  from { opacity: 0; transform: translateY(10px); }
  to { opacity: 1; transform: translateY(0); }
}
</style>

<div class="animate-fade-in container-fluid py-2">
    <!-- Compact Header -->
    <div class="d-flex align-items-center justify-content-between mb-3">
        <h2 class="fw-black text-main-theme mb-0 fs-4"><?= __('enroll_student') ?></h2>
        <div class="d-flex gap-2">
            <a href="/students/import" class="btn btn-sm btn-outline-success rounded-pill px-3 fw-bold text-nowrap">
                <i class="bi bi-file-earmark-spreadsheet me-1"></i> <?= __('import_excel') ?>
            </a>
            <a href="/students" class="btn btn-sm btn-light-theme rounded-pill px-3 border-theme-light">
                <i class="bi bi-arrow-left me-1"></i> <?= __('back_to_list') ?>
            </a>
        </div>
    </div>

    <form action="/students/store" method="POST" id="studentEnrollForm" enctype="multipart/form-data" class="no-loader">
        <input type="hidden" name="csrf_token" value="<?= \App\Core\Session::generateCsrfToken() ?>">

        <div class="subject-card-compact border-0 shadow-sm overflow-hidden mb-4">
            <div class="card-body p-4">
                
                <!-- Stepper -->
                <div class="stepper-wrapper" id="stepper">
                    <div class="stepper-progress" id="stepperProgress" style="width: 0%;"></div>
                    <div class="stepper-item active" data-step="1">
                        <div class="stepper-circle">1</div>
                        <div class="stepper-title"><?= __('learner_identity') ?></div>
                    </div>
                    <div class="stepper-item" data-step="2">
                        <div class="stepper-circle">2</div>
                        <div class="stepper-title"><?= __('student_photo') ?></div>
                    </div>
                    <div class="stepper-item" data-step="3">
                        <div class="stepper-circle">3</div>
                        <div class="stepper-title"><?= __('academic_assignment') ?></div>
                    </div>
                    <div class="stepper-item" data-step="4">
                        <div class="stepper-circle">4</div>
                        <div class="stepper-title">Finance</div>
                    </div>
                </div>

                <!-- Step 1: Identity Section -->
                <div class="form-step active" id="step1">
                    <div class="row g-4 mb-3">
                        <div class="col-12 border-bottom border-theme-light pb-2 mb-2">
                            <h6 class="fw-black text-primary m-0 text-uppercase letter-spacing-1"><?= __('learner_identity') ?></h6>
                        </div>
                        
                        <div class="col-md-3">
                            <label class="form-label text-muted-theme fw-bold extra-small text-uppercase mb-1"><?= __('family_name') ?></label>
                            <input type="text" name="nom" class="form-control premium-input" 
                                placeholder="<?= __('name_placeholder') ?>" value="<?= h($formData['nom'] ?? '') ?>" required autofocus>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label text-muted-theme fw-bold extra-small text-uppercase mb-1"><?= __('first_names') ?></label>
                            <input type="text" name="prenom" class="form-control premium-input" 
                                placeholder="<?= __('first_name_placeholder') ?>" value="<?= h($formData['prenom'] ?? '') ?>" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label text-muted-theme fw-bold extra-small text-uppercase mb-1"><?= __('matricule') ?></label>
                            <input type="text" name="email" class="form-control premium-input border-primary border-opacity-25" 
                                placeholder="Optionnel (Généré si vide)" value="<?= h($formData['email'] ?? '') ?>">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label text-muted-theme fw-bold extra-small text-uppercase mb-1"><?= __('sex') ?></label>
                            <select name="sexe" class="form-select premium-input" required>
                                <option value="" disabled selected>Sélectionner...</option>
                                <option value="M" <?= $selectedSexe === 'M' ? 'selected' : '' ?>><?= __('male') ?></option>
                                <option value="F" <?= $selectedSexe === 'F' ? 'selected' : '' ?>><?= __('female') ?></option>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label text-muted-theme fw-bold extra-small text-uppercase mb-1"><?= __('birth_date_full') ?></label>
                            <input type="date" name="date_naissance" class="form-control premium-input" value="<?= h($formData['date_naissance'] ?? '') ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted-theme fw-bold extra-small text-uppercase mb-1"><?= __('birth_place_full') ?></label>
                            <input type="text" name="lieu_naissance" class="form-control premium-input" 
                                placeholder="Lieu de naissance" value="<?= h($formData['lieu_naissance'] ?? '') ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted-theme fw-bold extra-small text-uppercase mb-1"><?= __('parent_contact') ?? 'Contact Père/Mère' ?></label>
                            <input type="tel" name="parent_contact" class="form-control premium-input" 
                                placeholder="+237 600000000" value="<?= h($formData['parent_contact'] ?? '') ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted-theme fw-bold extra-small text-uppercase mb-1"><?= __('guardian_contact') ?? 'Contact Tuteur' ?></label>
                            <input type="tel" name="guardian_contact" class="form-control premium-input" 
                                placeholder="+237 600000000" value="<?= h($formData['guardian_contact'] ?? '') ?>">
                        </div>
                        <div class="col-md-12">
                            <label class="form-label text-muted-theme fw-bold extra-small text-uppercase mb-1">Adresse</label>
                            <input type="text" name="adresse" class="form-control premium-input" 
                                placeholder="Adresse complète (Quartier, Rue, etc.)" value="<?= h($formData['adresse'] ?? '') ?>">
                        </div>
                    </div>
                </div>

                <!-- Step 2: Photo Section -->
                <div class="form-step" id="step2">
                    <div class="row g-4 mb-3">
                        <div class="col-12 border-bottom border-theme-light pb-2 mb-2">
                            <h6 class="fw-black text-info m-0 text-uppercase letter-spacing-1"><?= __('student_photo') ?></h6>
                        </div>
                        
                        <div class="col-md-6">
                            <label class="form-label text-muted-theme fw-bold extra-small text-uppercase mb-1"><?= __('photo_upload') ?> <span class="text-muted">(<?= __('optional') ?>)</span></label>
                            <input type="file" name="photo_eleve" class="form-control premium-input" accept="image/jpeg,image/jpg,image/png,image/webp" id="photoInput">
                            <div class="form-text small text-muted">
                                <?= __('photo_formats') ?>: JPG, JPEG, PNG, WEBP<br>
                                <?= __('photo_max_size') ?>: 5MB
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <label class="form-label text-muted-theme fw-bold extra-small text-uppercase mb-1"><?= __('photo_preview') ?></label>
                            <div id="photoPreview" class="border rounded d-flex align-items-center justify-content-center bg-light" style="width: 150px; height: 150px; overflow: hidden;">
                                <span class="text-muted small text-center px-2"><?= __('no_photo_selected') ?></span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Step 3: Academic Section -->
                <div class="form-step" id="step3">
                    <div class="row g-4">
                        <div class="col-12 border-bottom border-theme-light pb-2 mb-2">
                            <h6 class="fw-black text-success m-0 text-uppercase letter-spacing-1"><?= __('academic_assignment') ?></h6>
                        </div>

                        <!-- Left Column: Academic Structure Filters -->
                        <div class="col-lg-8">
                            <div class="card bg-light bg-opacity-50 border-theme-light rounded-4 p-4 shadow-sm">
                                <span class="fw-bold text-muted-theme extra-small text-uppercase mb-3 d-block">
                                    <i class="bi bi-funnel text-success me-1"></i> Filtrer par structure académique
                                </span>
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label text-muted-theme fw-bold extra-small text-uppercase mb-1 opacity-50">Type Enseignement</label>
                                        <select id="teaching_type_select" name="teaching_type_id" class="form-select premium-input">
                                            <option value="">Tous les types</option>
                                            <?php foreach ($teachingTypes as $tt): ?>
                                                <option value="<?= $tt['id'] ?>" <?= $selectedTeachingType === (string) $tt['id'] ? 'selected' : '' ?>><?= h($tt['nom']) ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label text-muted-theme fw-bold extra-small text-uppercase mb-1 opacity-50"><?= __('cycle_membership_label') ?></label>
                                        <select id="cycle_select" name="cycle_id" class="form-select premium-input">
                                            <option value=""><?= __('all_cycles') ?></option>
                                            <?php foreach ($cycles as $cy): ?>
                                                <option value="<?= $cy['id'] ?>" <?= $selectedCycle === (string) $cy['id'] ? 'selected' : '' ?>><?= h($cy['nom']) ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label text-muted-theme fw-bold extra-small text-uppercase mb-1 opacity-50"><?= __('section_stream') ?></label>
                                        <select id="section_select" name="section_id" class="form-select premium-input">
                                            <option value=""><?= __('all_sections') ?></option>
                                            <?php foreach ($sections as $sec): ?>
                                                <option value="<?= $sec['id'] ?>" <?= $selectedSection === (string) $sec['id'] ? 'selected' : '' ?>><?= h($sec['nom']) ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label text-muted-theme fw-bold extra-small text-uppercase mb-1 opacity-50"><?= __('department') ?></label>
                                        <select id="department_select" name="department_id" class="form-select premium-input">
                                            <option value=""><?= __('all_departments') ?? 'Tous les départements' ?></option>
                                            <?php foreach ($departments as $dept): ?>
                                                <option value="<?= $dept['id'] ?>" data-teaching-type="<?= $dept['teaching_type_id'] ?? '' ?>" <?= $selectedDepartment === (string) $dept['id'] ? 'selected' : '' ?>><?= h($dept['nom']) ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Right Column: Final Class Selection & Statuses -->
                        <div class="col-lg-4">
                            <div class="card border-success border-opacity-25 shadow-sm rounded-4 p-4 h-100 bg-success bg-opacity-5">
                                <div class="mb-4">
                                    <label class="form-label text-success fw-black extra-small text-uppercase mb-1 d-flex align-items-center">
                                        <i class="bi bi-door-open-fill me-1"></i> <?= __('student_class_label') ?> *
                                    </label>
                                    <select name="class_id" id="class_select" class="form-select premium-input border-success fw-bold" required data-current="<?= h($selectedClass) ?>">
                                        <option value=""><?= __('select_class') ?></option>
                                        <?php foreach ($classes as $cla): ?>
                                            <option value="<?= $cla['id'] ?>" data-teaching-type="<?= $cla['teaching_type_id'] ?>" data-cycle="<?= $cla['cycle_id'] ?>" data-section="<?= $cla['section_id'] ?>" data-department="<?= $cla['department_id'] ?>" data-frais-inscription="<?= $cla['frais_inscription'] ?>" data-frais-reinscription="<?= $cla['frais_inscription_reinscription'] ?>" data-frais-scolarite="<?= $cla['frais_scolarite_brut'] ?>"><?= h($cla['nom']) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                    <div class="form-text extra-small text-muted mt-1">La classe assignée à l'élève.</div>
                                </div>

                                <div class="row g-2">
                                    <div class="col-md-12 mb-3">
                                        <label class="form-label text-muted-theme fw-bold extra-small text-uppercase mb-2">
                                            <i class="bi bi-arrow-repeat me-1"></i> <?= __('repeat_status') ?>
                                        </label>
                                        <div class="d-flex gap-2">
                                            <div class="flex-grow-1">
                                                <input type="radio" class="btn-check" name="is_redoublant" id="red_no" value="0" <?= $isRedoublant !== '1' ? 'checked' : '' ?>>
                                                <label class="btn btn-outline-secondary w-100 rounded-pill btn-sm py-2 fw-semibold" for="red_no"><?= __('no') ?></label>
                                            </div>
                                            <div class="flex-grow-1">
                                                <input type="radio" class="btn-check" name="is_redoublant" id="red_yes" value="1" <?= $isRedoublant === '1' ? 'checked' : '' ?>>
                                                <label class="btn btn-outline-warning w-100 rounded-pill btn-sm py-2 fw-semibold" for="red_yes"><?= __('yes') ?></label>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-md-12">
                                        <label class="form-label text-success fw-black extra-small text-uppercase mb-2 d-flex align-items-center">
                                            <i class="bi bi-person-badge-fill me-1"></i> Statut d'inscription *
                                        </label>
                                        <div class="d-flex gap-2">
                                            <div class="flex-grow-1">
                                                <input type="radio" class="btn-check" name="student_status" id="status_new" value="nouveau" <?= ($formData['student_status'] ?? 'nouveau') !== 'ancien' ? 'checked' : '' ?> required>
                                                <label class="btn btn-outline-secondary w-100 rounded-pill btn-sm py-2 fw-semibold" for="status_new">Nouveau</label>
                                            </div>
                                            <div class="flex-grow-1">
                                                <input type="radio" class="btn-check" name="student_status" id="status_returning" value="ancien" <?= ($formData['student_status'] ?? '') === 'ancien' ? 'checked' : '' ?> required>
                                                <label class="btn btn-outline-info w-100 rounded-pill btn-sm py-2 fw-semibold" for="status_returning">Ancien</label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Step 4: Finance Section -->
                <div class="form-step" id="step4">
                    <div class="row g-4 mb-3">
                        <div class="col-12 border-bottom border-theme-light pb-2 mb-2">
                            <h6 class="fw-black text-warning m-0 text-uppercase letter-spacing-1">Configuration Financière Initiale</h6>
                        </div>

                        <div class="col-12">
                            <div class="alert alert-info py-2 px-3 fs-7 mb-0" id="class_finance_summary">
                                Classe sélectionnée : <span id="summary_class_name" class="fw-bold">-</span><br>
                                Frais de scolarité brut : <span id="summary_class_tuition" class="fw-bold">0</span> FCFA | Frais d'inscription attendus : <span id="summary_class_registration" class="fw-bold">0</span> FCFA
                            </div>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label text-muted-theme fw-bold extra-small text-uppercase mb-1">Frais d'inscription payés (FCFA) *</label>
                            <input type="number" name="frais_inscription_paid" min="0" value="0" step="50" class="form-control premium-input border-primary border-opacity-25" id="frais_inscription_paid" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label text-muted-theme fw-bold extra-small text-uppercase mb-1">Mode de Paiement *</label>
                            <select name="payment_method" id="payment_method" class="form-select premium-input border-primary border-opacity-25" required>
                                <option value="" disabled selected>Sélectionner...</option>
                                <?php foreach ($payment_methods as $method): ?>
                                    <option value="<?= h($method) ?>"><?= h($method) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label text-muted-theme fw-bold extra-small text-uppercase mb-1" id="reference_label">Référence de paiement</label>
                            <input type="text" name="reference" id="reference" class="form-control premium-input" placeholder="Numéro de transaction, chèque ou référence bancaire" required>
                        </div>

                        <div class="col-md-12 mt-4 border-top border-theme-light pt-3">
                            <span class="fw-bold text-secondary text-uppercase fs-7 mb-2 d-block">Réduction Éventuelle</span>
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label class="form-label text-muted-theme fw-bold extra-small text-uppercase mb-1">Montant / Valeur de réduction</label>
                                    <input type="number" name="reduction_amount" min="0" value="0" class="form-control premium-input" id="reduction_amount">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label text-muted-theme fw-bold extra-small text-uppercase mb-1">Type de réduction</label>
                                    <select name="reduction_amount_type" class="form-select premium-input">
                                        <option value="fixed">Montant fixe (FCFA)</option>
                                        <option value="percentage">Pourcentage (%)</option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label text-muted-theme fw-bold extra-small text-uppercase mb-1">Motif de réduction</label>
                                    <select name="reduction_motive" id="reduction_motive" class="form-select premium-input">
                                        <option value="">-- Sélectionner un motif --</option>
                                        <?php if (!empty($discountTypes)): ?>
                                            <?php foreach ($discountTypes as $dt): ?>
                                                <option value="<?= (int)$dt['id'] ?>"><?= h($dt['name'] ?? $dt['nom'] ?? '') ?></option>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <option value="">Aucun motif disponible</option>
                                        <?php endif; ?>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-12 mt-4 border-top border-theme-light pt-3">
                            <span class="fw-bold text-secondary text-uppercase fs-7 mb-2 d-block">Bourse Éventuelle</span>
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label class="form-label text-muted-theme fw-bold extra-small text-uppercase mb-1">Montant / Valeur de bourse</label>
                                    <input type="number" name="scholarship_amount" min="0" value="0" class="form-control premium-input" id="scholarship_amount">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label text-muted-theme fw-bold extra-small text-uppercase mb-1">Type de bourse</label>
                                    <select name="scholarship_amount_type" class="form-select premium-input">
                                        <option value="fixed">Montant fixe (FCFA)</option>
                                        <option value="percentage">Pourcentage (%)</option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label text-muted-theme fw-bold extra-small text-uppercase mb-1">Motif de bourse</label>
                                    <input type="text" name="scholarship_motive" class="form-control premium-input" placeholder="Ex: Excellence académique, besoin...">
                                </div>
                            </div>
                        </div>

                        <div class="col-12 mt-4 border-top border-theme-light pt-3">
                            <div class="alert alert-success py-2 px-3 fs-7 mb-0">
                                Solde Scolarité Initial Estimé : <span id="summary_estimated_balance" class="fw-bold">0</span> FCFA
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Navigation Footer -->
                <div class="d-flex justify-content-between border-top border-theme-light pt-4 mt-2">
                    <button type="button" class="btn btn-light-theme rounded-pill px-4" id="prevBtn" style="display: none;">
                        <i class="bi bi-arrow-left me-1"></i> Précédent
                    </button>
                    <div class="ms-auto">
                        <button type="button" class="btn btn-primary rounded-pill px-4" id="nextBtn">
                            Suivant <i class="bi bi-arrow-right ms-1"></i>
                        </button>
                        <button type="submit" class="btn btn-success rounded-pill px-5 fw-bold shadow-sm transition-base scale-on-hover" id="submitBtn" style="display: none;">
                            <i class="bi bi-check-circle-fill me-2"></i> Valider
                        </button>
                    </div>
                </div>

            </div>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    // Stepper Logic
    let currentStep = 1;
    const totalSteps = 4;
    const form = document.getElementById('studentEnrollForm');
    
    const nextBtn = document.getElementById('nextBtn');
    const prevBtn = document.getElementById('prevBtn');
    const submitBtn = document.getElementById('submitBtn');
    const stepperProgress = document.getElementById('stepperProgress');
    
    const classSelect = document.getElementById('class_select');

    // Financial policy configuration
    const registrationFeePolicy = <?= json_encode($registration_fee_policy) ?>;

    function getStudentStatus() {
        const checkedRadio = document.querySelector('input[name="student_status"]:checked');
        return checkedRadio ? checkedRadio.value : 'nouveau';
    }

    function getExpectedRegistrationFee() {
        const selectedOpt = classSelect.options[classSelect.selectedIndex];
        if (!selectedOpt || selectedOpt.value === '') {
            return 0;
        }

        const classRegistration = parseFloat(selectedOpt.getAttribute('data-frais-inscription')) || 0;
        const classReenrollment = parseFloat(selectedOpt.getAttribute('data-frais-reinscription')) || 0;
        const status = getStudentStatus();

        if (registrationFeePolicy === 'new_only') {
            return status === 'nouveau' ? classRegistration : 0;
        } else if (registrationFeePolicy === 'by_status') {
            return status === 'nouveau' ? classRegistration : classReenrollment;
        } else { // 'all'
            return classRegistration;
        }
    }

    function updateStepper() {
        // Update Steps visibility
        document.querySelectorAll('.form-step').forEach(step => {
            step.classList.remove('active');
        });
        document.getElementById('step' + currentStep).classList.add('active');
        
        // Update Navigation Buttons
        prevBtn.style.display = currentStep > 1 ? 'inline-block' : 'none';
        
        if (currentStep === totalSteps) {
            nextBtn.style.display = 'none';
            submitBtn.style.display = 'inline-block';
        } else {
            nextBtn.style.display = 'inline-block';
            submitBtn.style.display = 'none';
        }

        // Specific actions when entering step 4 (Finances)
        if (currentStep === 4) {
            updateFinanceStepSummary();
        }
        
        // Update Stepper UI
        const items = document.querySelectorAll('.stepper-item');
        items.forEach((item, index) => {
            const stepNum = index + 1;
            item.classList.remove('active', 'completed');
            
            if (stepNum < currentStep) {
                item.classList.add('completed');
                item.querySelector('.stepper-circle').innerHTML = '<i class="bi bi-check-lg"></i>';
            } else if (stepNum === currentStep) {
                item.classList.add('active');
                item.querySelector('.stepper-circle').innerHTML = stepNum;
            } else {
                item.querySelector('.stepper-circle').innerHTML = stepNum;
            }
        });
        
        // Update Progress Bar
        const progressPercentage = ((currentStep - 1) / (totalSteps - 1)) * 100;
        stepperProgress.style.width = progressPercentage + '%';
    }

    function updateFinanceStepSummary() {
        const selectedOpt = classSelect.options[classSelect.selectedIndex];
        const className = selectedOpt && selectedOpt.value !== '' ? selectedOpt.textContent : 'Non sélectionnée';
        const classTuition = parseFloat(selectedOpt && selectedOpt.value !== '' ? selectedOpt.getAttribute('data-frais-scolarite') : 0) || 0;
        
        const expectedFee = getExpectedRegistrationFee();

        document.getElementById('summary_class_name').textContent = className;
        document.getElementById('summary_class_tuition').textContent = formatNumber(classTuition);
        
        let policyLabel = "Tous les élèves";
        if (registrationFeePolicy === 'new_only') policyLabel = "Nouveaux uniquement";
        else if (registrationFeePolicy === 'by_status') policyLabel = "Différent selon statut";

        const statusLabel = getStudentStatus() === 'nouveau' ? "Nouveau" : "Ancien (Réinscription)";
        document.getElementById('summary_class_registration').textContent = `${formatNumber(expectedFee)} FCFA (Règle : ${policyLabel}, Statut : ${statusLabel})`;

        // Frais d'inscription payés par défaut
        const registrationInput = document.getElementById('frais_inscription_paid');
        if (!registrationInput.dataset.modified) {
            registrationInput.value = expectedFee;
        }

        updateEstimatedBalance();
        updateReferenceField();
    }

    function updateEstimatedBalance() {
        const selectedOpt = classSelect.options[classSelect.selectedIndex];
        const classTuition = parseFloat(selectedOpt && selectedOpt.value !== '' ? selectedOpt.getAttribute('data-frais-scolarite') : 0) || 0;

        const redAmt = parseFloat(document.getElementById('reduction_amount').value) || 0;
        const redType = document.getElementsByName('reduction_amount_type')[0].value;
        const scholAmt = parseFloat(document.getElementById('scholarship_amount').value) || 0;
        const scholType = document.getElementsByName('scholarship_amount_type')[0].value;

        let totalRed = 0;
        if (redType === 'percentage') {
            totalRed = classTuition * (redAmt / 100);
        } else {
            totalRed = redAmt;
        }

        let totalSchol = 0;
        if (scholType === 'percentage') {
            totalSchol = classTuition * (scholAmt / 100);
        } else {
            totalSchol = scholAmt;
        }

        const estBalance = Math.max(0, classTuition - totalRed - totalSchol);
        document.getElementById('summary_estimated_balance').textContent = formatNumber(estBalance);
    }

    // Dynamic reference fields based on payment method
    const paymentMethodSelect = document.getElementById('payment_method');
    const referenceInput = document.getElementById('reference');
    const referenceLabel = document.getElementById('reference_label');

    function updateReferenceField() {
        if (!paymentMethodSelect || !referenceInput) return;
        const val = paymentMethodSelect.value.toLowerCase();
        
        // Par défaut, le champ référence est requis et activé
        referenceInput.required = true;
        referenceInput.disabled = false;
        referenceLabel.innerHTML = 'Référence de paiement';
        referenceInput.placeholder = "Numéro de transaction, chèque ou référence bancaire";

        // Si paiement en espèces, la référence est générée automatiquement côté serveur
        if (val.includes('cash') || val.includes('espèces') || val.includes('espece')) {
            referenceLabel.innerHTML = 'Référence de paiement <span class="text-success">(générée automatiquement)</span>';
            referenceInput.placeholder = "Générée automatiquement par le système";
            referenceInput.value = "";
            referenceInput.required = false;
            referenceInput.disabled = true;
        } else if (val.includes('orange') || val.includes('mtn') || val.includes('mobile') || val.includes('momo') || val.includes('money')) {
            referenceLabel.innerHTML = 'Numéro de transaction <span class="text-muted">(obligatoire)</span>';
            referenceInput.placeholder = "Ex: TX-98471...";
            referenceInput.required = true;
            referenceInput.disabled = false;
        } else if (val.includes('chèque') || val.includes('cheque')) {
            referenceLabel.innerHTML = 'Numéro de chèque <span class="text-muted">(obligatoire)</span>';
            referenceInput.placeholder = "Ex: CHQ-001234";
            referenceInput.required = true;
            referenceInput.disabled = false;
        } else if (val.includes('virement') || val.includes('carte') || val.includes('bancaire') || val.includes('transfert')) {
            referenceLabel.innerHTML = 'Référence bancaire <span class="text-muted">(obligatoire)</span>';
            referenceInput.placeholder = "Ex: VIR-847294...";
            referenceInput.required = true;
            referenceInput.disabled = false;
        }
    }

    if (paymentMethodSelect) {
        paymentMethodSelect.addEventListener('change', updateReferenceField);
    }

    // Recalculate if status changes
    document.querySelectorAll('input[name="student_status"]').forEach(radio => {
        radio.addEventListener('change', function() {
            const registrationInput = document.getElementById('frais_inscription_paid');
            delete registrationInput.dataset.modified; // Reset manual edit flag
            if (currentStep === 4) {
                updateFinanceStepSummary();
            }
        });
    });

    // Event listeners for finance step
    document.getElementById('frais_inscription_paid').addEventListener('change', function() {
        this.dataset.modified = "true";
    });
    document.getElementById('reduction_amount').addEventListener('input', updateEstimatedBalance);
    document.getElementsByName('reduction_amount_type')[0].addEventListener('change', updateEstimatedBalance);
    document.getElementById('scholarship_amount').addEventListener('input', updateEstimatedBalance);
    document.getElementsByName('scholarship_amount_type')[0].addEventListener('change', updateEstimatedBalance);
    
    function validateCurrentStep() {
        const currentStepEl = document.getElementById('step' + currentStep);
        const inputs = currentStepEl.querySelectorAll('input[required], select[required]');
        let isValid = true;
        
        inputs.forEach(input => {
            if (!input.value.trim()) {
                isValid = false;
                input.classList.add('is-invalid');
                input.addEventListener('input', function() {
                    this.classList.remove('is-invalid');
                }, { once: true });
            }
        });
        
        return isValid;
    }
    
    nextBtn.addEventListener('click', () => {
        if (validateCurrentStep()) {
            if (currentStep < totalSteps) {
                currentStep++;
                updateStepper();
            }
        }
    });
    
    prevBtn.addEventListener('click', () => {
        if (currentStep > 1) {
            currentStep--;
            updateStepper();
        }
    });

    // Make stepper items freely clickable if valid
    document.querySelectorAll('.stepper-item').forEach((item, index) => {
        item.addEventListener('click', () => {
            const targetStep = index + 1;
            if (targetStep < currentStep) {
                currentStep = targetStep;
                updateStepper();
            } else {
                if (validateCurrentStep()) {
                    currentStep = targetStep;
                    updateStepper();
                }
            }
        });
    });

    // Final Validation on Submit
    form.addEventListener('submit', function(e) {
        const requiredInputs = form.querySelectorAll('input[required], select[required]');
        let isValid = true;
        let firstInvalidStep = null;

        requiredInputs.forEach(input => {
            if (!input.value.trim()) {
                isValid = false;
                input.classList.add('is-invalid');
                
                const stepEl = input.closest('.form-step');
                if (stepEl && !firstInvalidStep) {
                    firstInvalidStep = parseInt(stepEl.id.replace('step', ''));
                }
                
                input.addEventListener('input', function() {
                    this.classList.remove('is-invalid');
                }, { once: true });
            }
        });

        if (!isValid) {
            e.preventDefault();
            if (firstInvalidStep && firstInvalidStep !== currentStep) {
                currentStep = firstInvalidStep;
                updateStepper();
            }
            return;
        }

        // Strict validation of registration fees paid
        const expectedFee = getExpectedRegistrationFee();
        const paidFee = parseFloat(document.getElementById('frais_inscription_paid').value) || 0;
        if (paidFee !== expectedFee) {
            e.preventDefault();
            Swal.fire({
                icon: 'error',
                title: 'Validation des frais d\'inscription',
                text: `Le montant versé (${formatNumber(paidFee)} FCFA) doit être exactement égal au montant attendu (${formatNumber(expectedFee)} FCFA) pour cette classe et ce statut.`,
                confirmButtonColor: '#2563EB'
            });
            currentStep = 4;
            updateStepper();
        }
    });

    // Dropdown Filtering Logic
    const teachingTypeSelect = document.getElementById('teaching_type_select');
    const cycleSelect = document.getElementById('cycle_select');
    const sectionSelect = document.getElementById('section_select');
    const departmentSelect = document.getElementById('department_select');
    const currentClassId = classSelect.getAttribute('data-current') || '';
    
    const labels = <?= json_encode([
        'selectClass' => __('select_class'),
        'noClassForCriteria' => __('no_class_for_criteria'),
        'allDepartments' => __('all_departments') ?? 'Tous les départements',
    ], JSON_UNESCAPED_UNICODE) ?>;

    const originalOptions = Array.from(classSelect.options).filter(opt => opt.value !== '');
    const originalDeptOptions = Array.from(departmentSelect.options).filter(opt => opt.value !== '');

    function filterDepartments() {
        if (!teachingTypeSelect) return;
        const selectedTeachingType = teachingTypeSelect.value;
        const currentDeptId = departmentSelect.value;
        
        departmentSelect.innerHTML = '<option value="">' + labels.allDepartments + '</option>';
        
        let deptFoundSelected = false;
        
        originalDeptOptions.forEach(opt => {
            const optTeachingType = opt.getAttribute('data-teaching-type');
            const matchTeachingType = !selectedTeachingType || !optTeachingType || optTeachingType === selectedTeachingType;
            
            if (matchTeachingType) {
                const clonedOption = opt.cloneNode(true);
                if (clonedOption.value === currentDeptId) {
                    clonedOption.selected = true;
                    deptFoundSelected = true;
                }
                departmentSelect.appendChild(clonedOption);
            }
        });
        
        if (currentDeptId && !deptFoundSelected) {
            departmentSelect.value = '';
        }
        
        filterClasses();
    }

    function filterClasses() {
        const selectedTeachingType = teachingTypeSelect.value;
        const selectedCycle = cycleSelect.value;
        const selectedSection = sectionSelect.value;
        const selectedDept = departmentSelect.value;
        classSelect.innerHTML = '<option value="">' + labels.selectClass + '</option>';

        let addedCount = 0;
        originalOptions.forEach(opt => {
            const matchTeachingType = !selectedTeachingType || opt.getAttribute('data-teaching-type') === selectedTeachingType;
            const matchCycle = !selectedCycle || opt.getAttribute('data-cycle') === selectedCycle;
            const matchSection = !selectedSection || opt.getAttribute('data-section') === selectedSection;
            const matchDept = !selectedDept || opt.getAttribute('data-department') === selectedDept;

            if (matchTeachingType && matchCycle && matchSection && matchDept) {
                const clonedOption = opt.cloneNode(true);
                if (clonedOption.value === currentClassId) clonedOption.selected = true;
                classSelect.appendChild(clonedOption);
                addedCount++;
            }
        });

        if (addedCount === 0 && (selectedTeachingType || selectedCycle || selectedSection || selectedDept)) {
            classSelect.innerHTML = '<option value="">' + labels.noClassForCriteria + '</option>';
        }
    }

    if (teachingTypeSelect) teachingTypeSelect.addEventListener('change', filterDepartments);
    if (cycleSelect) cycleSelect.addEventListener('change', filterClasses);
    sectionSelect.addEventListener('change', filterClasses);
    departmentSelect.addEventListener('change', filterClasses);
    if (teachingTypeSelect) filterDepartments(); else filterClasses();

    // Photo preview
    const photoInput = document.getElementById('photoInput');
    const photoPreview = document.getElementById('photoPreview');
    
    photoInput.addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                photoPreview.innerHTML = '<img src="' + e.target.result + '" alt="Aperçu" style="width: 100%; height: 100%; object-fit: cover;">';
            };
            reader.readAsDataURL(file);
        } else {
            photoPreview.innerHTML = '<span class="text-muted small text-center px-2"><?= __('no_photo_selected') ?></span>';
        }
    });

    function formatNumber(val) {
        return new Intl.NumberFormat('fr-FR').format(val);
    }

    // Initial setup
    updateStepper();
});
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../templates/layout.php';
?>
