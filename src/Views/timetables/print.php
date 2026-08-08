<?php
$title = __('timetables_menu') . ' - ' . __('print');
ob_start();
?>

<div class="animate-fade-in admin-analytics module-bureau-flow">


    <!-- BARRE D'ACTIONS COMPLÈTE : Style Floating Island Responsive -->
    <div class="d-flex justify-content-center mb-4 mb-md-5">
        <div class="filter-island px-3 py-2 shadow-lg animate-slide-down">
            <form method="GET" action="/timetables/print" class="d-flex align-items-center gap-2 gap-md-3 flex-wrap flex-md-nowrap filter-form w-100">

                <div class="row g-2 flex-grow-1 w-100 m-0">
                    <!-- 1. Année Académique -->
                    <div class="col-12 col-sm-4 p-0 px-sm-1">
                        <div class="input-group search-pill bg-white bg-opacity-10 rounded-pill px-3 py-1 w-100">
                            <span class="input-group-text border-0 bg-transparent text-primary small fw-bold text-uppercase me-1">
                                <?= __('year') ?>
                            </span>
                            <select name="academic_year_id" class="form-select border-0 bg-transparent shadow-none fw-bold text-main" onchange="this.form.submit()">
                                <?php foreach ($academicYears as $year): ?>
                                    <option value="<?= $year['id'] ?>" <?= $selectedYearId === (int)$year['id'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars((string)$year['nom']) ?>
                                        <?= (int)$year['is_active'] === 1 ? '(' . __('active') . ')' : '' ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <!-- 2. Cycle Académique -->
                    <div class="col-12 col-sm-4 p-0 px-sm-1">
                        <div class="input-group search-pill bg-white bg-opacity-10 rounded-pill px-3 py-1 w-100">
                            <span class="input-group-text border-0 bg-transparent text-primary small fw-bold text-uppercase me-1">
                                <?= __('academic_cycles') ?>
                            </span>
                            <select name="cycle_id" class="form-select border-0 bg-transparent shadow-none fw-bold text-main" onchange="this.form.submit()">
                                <?php foreach ($cycles as $cycle): ?>
                                    <option value="<?= $cycle['id'] ?>" <?= $selectedCycleId === (int)$cycle['id'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars((string)($cycle['nom'] ?? $cycle['code'] ?? 'Cycle')) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <!-- 3. Niveau Scolaire (Optionnel) -->
                    <div class="col-12 col-sm-4 p-0 px-sm-1">
                        <div class="input-group search-pill bg-white bg-opacity-10 rounded-pill px-3 py-1 w-100">
                            <span class="input-group-text border-0 bg-transparent text-primary small fw-bold text-uppercase me-1">
                                <?= __('levels') ?>
                            </span>
                            <select name="level_id" class="form-select border-0 bg-transparent shadow-none fw-bold text-main" onchange="this.form.submit()">
                                <option value="0">Tous les niveaux du cycle</option>
                                <?php foreach ($levels as $level): ?>
                                    <option value="<?= $level['id'] ?>" <?= $selectedLevelId === (int)$level['id'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars((string)($level['nom'] ?? $level['code'] ?? 'Niveau')) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="d-flex gap-2 align-items-center justify-content-center ps-md-2 pt-2 pt-md-0 border-top border-top-md-0 border-opacity-10 border-secondary flex-shrink-0">
                    <a href="/timetables/print" class="btn btn-light rounded-circle p-2 d-flex align-items-center justify-content-center reset-btn shadow-sm" style="width: 44px; height: 44px;" title="<?= __('reset') ?>">
                        <i class="bi bi-arrow-counterclockwise fs-5 text-primary"></i>
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- CARTE DE SÉLECTION & PRÉVISUALISATION -->
    <?php if ($selectedCycleId > 0): ?>
        <div class="row g-4 mb-5">
            
            <!-- ÉTAPE 1 & 2 : Formulaire de configuration d'impression (Compensé et réduit de moitié) -->
            <div class="col-xl-3 col-lg-4">
                <div class="modern-card border-0 shadow-sm h-100 p-3 p-lg-4">
                    <h5 class="fw-bold mb-4 text-main-theme d-flex align-items-center gap-2 fs-6">
                        <i class="bi bi-sliders text-primary"></i> Options d'Édition
                    </h5>

                    <!-- Semaine de cours -->
                    <div class="flow-step mb-4">
                        <div class="flow-step-number">1</div>
                        <h6 class="fw-bold mb-2 text-main-theme extra-small text-uppercase">Semaine d'enseignement</h6>
                        <select id="selectWeekInput" class="form-select form-select-md rounded-4 shadow-sm" onchange="updatePreviewUrl()">
                            <?php foreach ($weeks as $week): ?>
                                <option value="<?= $week['id'] ?>" <?= $selectedWeekId === (int)$week['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars((string)$week['libelle']) ?>
                                    <?php if (!empty($week['date_debut'])): ?>
                                        (du <?= date('d/m/Y', strtotime($week['date_debut'])) ?> au <?= date('d/m/Y', strtotime($week['date_fin'])) ?>)
                                    <?php endif; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Boutons d'Action & Format d'impression -->
                    <div class="flow-step mb-4">
                        <div class="flow-step-number">2</div>
                        <h6 class="fw-bold mb-2 text-main-theme extra-small text-uppercase">Format & Actions</h6>
                        <div class="p-3 bg-main-theme bg-opacity-5 rounded-4 border mb-3">
                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <span class="fw-semibold extra-small text-muted">Format :</span>
                                <span class="badge bg-primary bg-opacity-10 text-primary rounded-pill px-2.5 py-1 extra-small fw-bold">A4 Paysage</span>
                            </div>
                            <div class="d-flex align-items-center justify-content-between">
                                <span class="fw-semibold extra-small text-muted">Mise en page :</span>
                                <span class="badge bg-success bg-opacity-10 text-success rounded-pill px-2.5 py-1 extra-small fw-bold">Grille horaire</span>
                            </div>
                        </div>

                        <div class="d-flex flex-column gap-2">
                            <a id="btnDirectPrint" href="#" target="_blank" class="btn btn-primary btn-md rounded-pill shadow-sm fw-bold d-flex align-items-center justify-content-center gap-2">
                                <i class="bi bi-printer-fill fs-6"></i> Imprimer
                            </a>

                            <a id="btnDownloadPdf" href="#" class="btn btn-outline-primary btn-md rounded-pill shadow-sm fw-bold d-flex align-items-center justify-content-center gap-2">
                                <i class="bi bi-file-earmark-pdf-fill fs-6"></i> Télécharger PDF
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ZONE DE PRÉVISUALISATION DIRECTE (IFRAME ÉLARGIE À 75%) -->
            <div class="col-xl-9 col-lg-8">
                <div class="modern-card border-0 shadow-sm h-100 overflow-hidden d-flex flex-column">
                    <div class="modern-card-header p-3 px-4 border-bottom bg-transparent d-flex align-items-center justify-content-between">
                        <div class="d-flex align-items-center gap-2">
                            <div class="p-2 bg-primary bg-opacity-10 rounded-3 text-primary">
                                <i class="bi bi-eye"></i>
                            </div>
                            <h6 class="fw-bold m-0 text-main-theme">Prévisualisation A4 Paysage</h6>
                        </div>
                        <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-3" onclick="reloadPreviewIframe()">
                            <i class="bi bi-arrow-clockwise me-1"></i> Actualiser
                        </button>
                    </div>
                    <div class="modern-card-body p-0 flex-grow-1 bg-secondary bg-opacity-10 position-relative" style="min-height: 520px;">
                        <iframe id="previewIframe" src="about:blank" class="w-100 h-100 border-0" style="min-height: 520px;"></iframe>
                    </div>
                </div>
            </div>

        </div>
    <?php endif; ?>

</div>

<script>
    function getSelectedParams() {
        const cycleId = "<?= (int)$selectedCycleId ?>";
        const levelId = "<?= (int)$selectedLevelId ?>";
        const weekSelect = document.getElementById('selectWeekInput');
        const weekId = weekSelect ? weekSelect.value : "<?= (int)$selectedWeekId ?>";
        return { cycleId, levelId, weekId };
    }

    function updatePreviewUrl() {
        const { cycleId, levelId, weekId } = getSelectedParams();
        const previewUrl = `/timetables/pdf?cycle_id=${cycleId}&level_id=${levelId}&week_id=${weekId}&mode=preview`;
        const printUrl = `/timetables/pdf?cycle_id=${cycleId}&level_id=${levelId}&week_id=${weekId}&mode=print`;
        const downloadUrl = `/timetables/pdf?cycle_id=${cycleId}&level_id=${levelId}&week_id=${weekId}&mode=download`;

        const iframe = document.getElementById('previewIframe');
        if (iframe) {
            iframe.src = previewUrl;
        }

        const btnPrint = document.getElementById('btnDirectPrint');
        if (btnPrint) {
            btnPrint.href = printUrl;
        }

        const btnDownload = document.getElementById('btnDownloadPdf');
        if (btnDownload) {
            btnDownload.href = downloadUrl;
        }
    }

    function reloadPreviewIframe() {
        const iframe = document.getElementById('previewIframe');
        if (iframe) {
            iframe.src = iframe.src;
        }
    }

    document.addEventListener('DOMContentLoaded', () => {
        updatePreviewUrl();
    });
</script>

<?php
$content = ob_get_clean();
require __DIR__ . '/../templates/layout.php';
