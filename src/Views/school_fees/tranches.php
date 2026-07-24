<?php
$title = __('tranches_title');
ob_start();
?>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
<style>
.page-wrap{font-family:'Plus Jakarta Sans',sans-serif;}
.page-header-bar{background:var(--bg-card,#fff);border-radius:18px;border:1px solid rgba(0,0,0,.05);box-shadow:0 2px 12px rgba(0,0,0,.03);}
.page-header-icon{width:48px;height:48px;border-radius:14px;background:linear-gradient(135deg,rgba(79,70,229,.14),rgba(79,70,229,.06));color:var(--bs-primary);box-shadow:inset 0 1px 0 rgba(255,255,255,.6);}
.search-pill{border-radius:50px;border:1.5px solid rgba(0,0,0,.09);background:#fff;padding:.5rem 1.15rem .5rem .65rem;display:flex;align-items:center;gap:.55rem;transition:all .22s ease;min-width:0;}
.search-pill:focus-within{border-color:var(--bs-primary);box-shadow:0 0 0 4px rgba(79,70,229,.1);}
.search-pill input{border:none;outline:none;background:transparent;font-size:.88rem;width:220px;min-width:0;}
.search-pill input::placeholder{color:rgba(100,116,139,.75);}
.btn-add-new{width:42px;height:42px;border-radius:13px;display:flex;align-items:center;justify-content:center;background:linear-gradient(135deg,var(--bs-primary),#6366f1);color:#fff;transition:all .25s cubic-bezier(.34,1.56,.64,1);cursor:pointer;border:none;box-shadow:0 4px 14px rgba(79,70,229,.28);}
.btn-add-new:hover{transform:translateY(-2px) scale(1.04);box-shadow:0 10px 28px rgba(79,70,229,.35);}
.tranches-alert{border:none;border-left:4px solid transparent;box-shadow:0 4px 18px rgba(0,0,0,.04);}
.tranches-alert.alert-success{border-left-color:var(--bs-success);}
.tranches-alert.alert-danger{border-left-color:var(--bs-danger);}
.config-card{background:var(--bg-card,#fff);border:1px solid rgba(0,0,0,0.06);border-radius:20px;transition:all .3s cubic-bezier(.16,1,.3,1);box-shadow:0 4px 16px rgba(0,0,0,.03);display:flex;flex-direction:column;}
.config-card:hover{box-shadow:0 14px 42px rgba(0,0,0,.08);transform:translateY(-3px);}
.config-card.type-class{border-left:5px solid var(--bs-primary);}
.config-card.type-cycle{border-left:5px solid var(--bs-info);}
.config-card.type-teaching_type{border-left:5px solid var(--bs-warning);}
.config-card-amount{letter-spacing:-.02em;line-height:1.1;}
.badge-type{font-size:.72rem;font-weight:700;letter-spacing:.35px;padding:.42rem .95rem;border-radius:50px;}
.btn-edit-config{width:36px;height:36px;border-radius:11px;display:flex;align-items:center;justify-content:center;background:rgba(79,70,229,.07);border:1px solid rgba(79,70,229,.12);color:var(--bs-primary);transition:all .25s cubic-bezier(.34,1.56,.64,1);cursor:pointer;}
.btn-edit-config:hover{background:var(--bs-primary);color:#fff;transform:scale(1.08);box-shadow:0 6px 18px rgba(79,70,229,.25);}
.timeline-line{border-left:2px solid rgba(79,70,229,.12);padding-left:1.55rem;position:relative;}
.timeline-dot{width:10px;height:10px;border-radius:50%;background:var(--bs-primary);border:2px solid #fff;box-shadow:0 0 0 2px rgba(79,70,229,.22);position:absolute;left:-6px;top:6px;}
.timeline-item{padding:.45rem .65rem;margin-left:-.65rem;border-radius:10px;transition:background .18s ease;}
.timeline-item:hover{background:rgba(79,70,229,.04);}
.config-card-footer{border-color:rgba(0,0,0,.06)!important;}
.empty-state-icon{width:72px;height:72px;border-radius:20px;background:rgba(79,70,229,.08);color:var(--bs-primary);display:inline-flex;align-items:center;justify-content:center;margin-bottom:1rem;}
.empty-state-card{max-width:520px;margin:0 auto;}
/* Modal scroll & layout */
#modal-edit-config .modal-dialog{max-height:calc(100vh - 1.5rem);margin:.75rem auto;}
#modal-edit-config .modal-content{border-radius:20px;max-height:calc(100vh - 1.5rem);display:flex;flex-direction:column;overflow:hidden;}
#modal-edit-config .modal-config-form{display:flex;flex-direction:column;flex:1 1 auto;min-height:0;overflow:hidden;}
#modal-edit-config .modal-header{flex-shrink:0;}
#modal-edit-config .modal-body{flex:1 1 auto;min-height:0;overflow-y:auto;overflow-x:hidden;-webkit-overflow-scrolling:touch;overscroll-behavior:contain;}
#modal-edit-config .modal-body::-webkit-scrollbar{width:6px;}
#modal-edit-config .modal-body::-webkit-scrollbar-thumb{background:rgba(79,70,229,.25);border-radius:50px;}
#modal-edit-config .modal-footer{flex-shrink:0;background:var(--bg-card,#fff);border-top:1px solid rgba(0,0,0,.05)!important;padding-top:.85rem!important;}
.modal-header-gradient{background:linear-gradient(135deg,rgba(79,70,229,.08),rgba(99,102,241,.03));}
.modal-target-panel{background:rgba(79,70,229,.04);border:1px solid rgba(79,70,229,.1);border-radius:16px;padding:1.1rem;}
.modal-budget-panel{background:rgba(248,250,252,.95);border:1px solid rgba(0,0,0,.06);border-radius:14px;padding:.85rem 1rem;}
.modal-presets-panel{padding:.75rem 1rem;background:rgba(79,70,229,.03);border:1px dashed rgba(79,70,229,.18);border-radius:14px;}
.modal-tranche-row{border:1px solid rgba(0,0,0,.07);border-radius:14px;background:#fafbff;padding:1rem 1.05rem;margin-bottom:.75rem;position:relative;border-left:4px solid var(--bs-primary);transition:box-shadow .2s,transform .2s;}
.modal-tranche-row:hover{box-shadow:0 6px 20px rgba(0,0,0,.06);transform:translateY(-1px);}
.mi{border-radius:10px;border:1.5px solid rgba(0,0,0,.1);padding:.58rem .9rem;transition:all .2s;font-size:.87rem;background:#fff;}
.mi:focus{border-color:var(--bs-primary);box-shadow:0 0 0 3px rgba(79,70,229,.12);}
.preset-chip{font-size:.7rem;font-weight:700;padding:.35rem .75rem;border-radius:50px;border:1px solid rgba(79,70,229,.2);background:#fff;color:var(--bs-primary);cursor:pointer;transition:all .2s;}
.preset-chip:hover,.preset-chip:active{background:var(--bs-primary);color:#fff;border-color:var(--bs-primary);transform:translateY(-1px);}
.budget-bar-wrap{height:9px;border-radius:50px;background:rgba(0,0,0,.06);overflow:hidden;}
.budget-bar-fill{height:100%;border-radius:50px;transition:width .35s ease,background .2s;}
.btn-modal-cancel{border:1px solid rgba(0,0,0,.08);}
.btn-modal-save{min-width:148px;}
.modal-loader-panel{padding:2rem 1rem;}
@media (max-width:575.98px){
    .page-header-bar{flex-direction:column;align-items:stretch!important;gap:1rem;}
    .page-header-actions{width:100%;justify-content:space-between;}
    .search-pill{flex:1;}
    .search-pill input{width:100%;}
    #modal-edit-config .modal-dialog{margin:.5rem;max-height:calc(100vh - 1rem);}
    #modal-edit-config .modal-content,#modal-edit-config .modal-dialog{max-height:calc(100vh - 1rem);}
    #modal-edit-config .modal-footer{flex-direction:column-reverse;gap:.5rem;}
    #modal-edit-config .modal-footer .btn{width:100%;}
    .modal-tranche-row .row.g-2>.col-6{flex:0 0 100%;max-width:100%;}
}
@media (min-width:576px){
    #modal-edit-config.modal .modal-dialog.modal-dialog-centered{align-items:center;min-height:calc(100% - 1.5rem);}
}
[data-theme="dark"] .config-card{background:rgba(30,41,59,.35);border-color:rgba(255,255,255,.06);}
[data-theme="dark"] .page-header-bar{background:rgba(30,41,59,.4);border-color:rgba(255,255,255,.06);}
[data-theme="dark"] .page-header-icon{box-shadow:inset 0 1px 0 rgba(255,255,255,.05);}
[data-theme="dark"] .search-pill{background:rgba(30,41,59,.5);border-color:rgba(255,255,255,.08);}
[data-theme="dark"] .search-pill input{color:#e2e8f0;}
[data-theme="dark"] .timeline-item:hover{background:rgba(79,70,229,.1);}
[data-theme="dark"] .config-card-footer{border-color:rgba(255,255,255,.06)!important;}
[data-theme="dark"] .modal-tranche-row{background:rgba(15,23,42,.4);border-color:rgba(255,255,255,.07);}
[data-theme="dark"] .mi{background:rgba(15,23,42,.5)!important;border-color:rgba(255,255,255,.09)!important;color:#f1f5f9!important;}
[data-theme="dark"] .modal-budget-panel{background:rgba(15,23,42,.45);border-color:rgba(255,255,255,.07);}
[data-theme="dark"] .modal-header-gradient{background:linear-gradient(135deg,rgba(79,70,229,.14),rgba(15,23,42,.2));}
[data-theme="dark"] .modal-target-panel{background:rgba(79,70,229,.08);border-color:rgba(79,70,229,.18);}
[data-theme="dark"] .modal-presets-panel{background:rgba(79,70,229,.08);border-color:rgba(79,70,229,.22);}
[data-theme="dark"] .preset-chip{background:rgba(15,23,42,.55);border-color:rgba(79,70,229,.25);}
[data-theme="dark"] #modal-edit-config .modal-footer{background:rgba(30,41,59,.55);border-color:rgba(255,255,255,.06)!important;}
@keyframes cfadeIn{from{opacity:0;transform:translateY(12px)}to{opacity:1;transform:translateY(0)}}
.cfg-animate{animation:cfadeIn .35s cubic-bezier(.16,1,.3,1) forwards;}
@keyframes rowSlide{from{opacity:0;transform:translateX(-10px)}to{opacity:1;transform:translateX(0)}}
.row-anim{animation:rowSlide .25s cubic-bezier(.16,1,.3,1) forwards;}
@keyframes rowFade{from{opacity:1}to{opacity:0;transform:scale(.9)}}
.row-remove-anim{animation:rowFade .2s forwards;}
</style>
<div class="page-wrap animate-fade-in container-fluid py-3 px-md-4">
    <div class="page-header-bar d-flex align-items-center justify-content-between p-3 p-md-4 mb-4">
        <div class="d-flex align-items-center gap-3">
            <div class="page-header-icon d-flex align-items-center justify-content-center flex-shrink-0">
                <i class="bi bi-layers-half fs-5"></i>
            </div>
            <div>
                <h1 class="fw-black text-main-theme mb-0 fs-5" style="letter-spacing:-.4px;"><?= __('tranches_title') ?></h1>
                <p class="text-muted-theme small mb-0"><?= __('tranches_subtitle') ?></p>
            </div>
        </div>
        <div class="page-header-actions d-flex align-items-center gap-2 gap-sm-3">
            <div class="search-pill d-flex">
                <i class="bi bi-search text-muted ms-1" style="font-size:.85rem;"></i>
                <input type="text" id="search-cfg" placeholder="<?= __('search_config_placeholder') ?>">
            </div>
            <button type="button" class="btn-add-new flex-shrink-0" id="btn-open-new" title="<?= __('new_config_title') ?>"
                data-bs-toggle="modal" data-bs-target="#modal-edit-config">
                <i class="bi bi-plus-lg fs-5"></i>
            </button>
        </div>
    </div>

    <?php if ($flash = \App\Core\Session::getFlash('success')): ?>
        <div class="alert alert-success tranches-alert alert-dismissible fade show rounded-3 mb-4" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i><?= h($flash) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    <?php if ($flash = \App\Core\Session::getFlash('error')): ?>
        <div class="alert alert-danger tranches-alert alert-dismissible fade show rounded-3 mb-4" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2"></i><?= h($flash) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div id="configs-grid" class="row g-3">
        <?php
        $groupedInstallments = [];
        foreach ($installments as $inst) {
            $key = ''; $label = ''; $type = ''; $tClass = ''; $rawType = '';
            if ($inst['class_id']) {
                $key = 'class_' . $inst['class_id'];
                $label = __('class') . ' : ' . $inst['class_name'];
                $type = 'primary'; $tClass = 'type-class'; $rawType = 'class';
            } elseif ($inst['cycle_id']) {
                $key = 'cycle_' . $inst['cycle_id'];
                $label = __('cycle') . ' : ' . $inst['cycle_name'];
                $type = 'info'; $tClass = 'type-cycle'; $rawType = 'cycle';
            } elseif ($inst['teaching_type_id']) {
                $key = 'teaching_type_' . $inst['teaching_type_id'];
                $label = __('teaching_type') . ' : ' . $inst['teaching_type_name'];
                $type = 'warning'; $tClass = 'type-teaching_type'; $rawType = 'teaching_type';
            }
            if ($key === '') continue;
            if (!isset($groupedInstallments[$key])) {
                $groupedInstallments[$key] = [
                    'label' => $label, 'type' => $type, 'target_type_class' => $tClass,
                    'raw_type' => $rawType,
                    'target_type' => $inst['class_id'] ? 'class' : ($inst['cycle_id'] ? 'cycle' : 'teaching_type'),
                    'target_id' => $inst['class_id'] ?: ($inst['cycle_id'] ?: $inst['teaching_type_id']),
                    'total_amount' => 0.0, 'items' => []
                ];
            }
            $groupedInstallments[$key]['total_amount'] += (float)$inst['amount'];
            $groupedInstallments[$key]['items'][] = $inst;
        }
        ?>

        <?php if (empty($groupedInstallments)): ?>
            <div class="col-12">
                <div class="text-center py-5 config-card p-5 empty-state-card">
                    <div class="empty-state-icon">
                        <i class="bi bi-layers fs-3"></i>
                    </div>
                    <h5 class="text-main-theme fw-bold mb-2"><?= __('no_config_yet') ?></h5>
                    <p class="text-muted small mb-4 px-2"><?= __('use_editor_to_start') ?></p>
                    <button type="button" class="btn btn-primary rounded-pill px-4 shadow-sm" data-bs-toggle="modal" data-bs-target="#modal-edit-config" id="btn-empty-new">
                        <i class="bi bi-plus-circle-fill me-2"></i><?= __('create_config_btn') ?>
                    </button>
                </div>
            </div>
        <?php else: ?>
            <?php foreach ($groupedInstallments as $group): ?>
                <div class="col-md-6 col-xl-4 cfg-animate config-grid-item">
                    <div class="config-card <?= $group['target_type_class'] ?> p-4 h-100">
                        <div class="d-flex align-items-start justify-content-between mb-3">
                            <div class="flex-grow-1 me-2">
                                <span class="badge badge-type bg-<?= $group['type'] ?> bg-opacity-10 text-<?= $group['type'] ?> border border-<?= $group['type'] ?> border-opacity-20 mb-2">
                                    <?= h($group['label']) ?>
                                </span>
                                <div class="fw-black fs-5 text-main-theme config-card-amount">
                                    <?= number_format($group['total_amount'], 0, '.', ' ') ?>
                                    <span class="text-muted fw-normal" style="font-size:.75rem;">FCFA</span>
                                </div>
                            </div>
                            <button type="button"
                                class="btn-edit-config flex-shrink-0"
                                data-type="<?= $group['raw_type'] ?>"
                                data-id="<?= $group['target_id'] ?>"
                                data-label="<?= h($group['label']) ?>"
                                title="<?= __('edit_this_config') ?>"
                                onclick="openEditModal(this)">
                                <i class="bi bi-pencil-square"></i>
                            </button>
                        </div>
                        <div class="timeline-line mt-3 flex-grow-1">
                            <?php foreach ($group['items'] as $item): ?>
                                <div class="mb-2 position-relative timeline-item">
                                    <div class="timeline-dot"></div>
                                    <div class="d-flex justify-content-between align-items-center gap-2 flex-wrap">
                                        <span class="fw-semibold text-main-theme small"><?= h($item['name']) ?></span>
                                        <div class="text-end">
                                            <span class="fw-bold text-primary small me-2"><?= number_format($item['amount'], 0, '.', ' ') ?> FCFA</span>
                                            <span class="text-muted" style="font-size:.7rem;"><i class="bi bi-calendar2 me-1"></i><?= date('d/m/Y', strtotime($item['deadline_date'])) ?></span>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <div class="mt-3 pt-2 border-top config-card-footer d-flex align-items-center justify-content-between">
                            <span class="text-muted extra-small fw-semibold">
                                <i class="bi bi-stack me-1"></i><?= count($group['items']) ?> <?= count($group['items']) > 1 ? __('tranches_count_plural') : __('tranches_count') ?>
                            </span>
                            <span class="extra-small text-muted fw-medium"><?= $group['raw_type'] === 'class' ? __('class') : ($group['raw_type'] === 'cycle' ? __('cycle') : __('teaching_type')) ?></span>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>
<!-- MODAL EDITION -->
<div class="modal fade" id="modal-edit-config" tabindex="-1" aria-labelledby="modal-edit-label" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header border-0 pb-0 pt-4 px-4 modal-header-gradient">
                <div class="pe-3">
                    <h5 class="modal-title fw-black text-main-theme mb-1 fs-5" id="modal-edit-label">
                        <i class="bi bi-sliders2 text-primary me-2"></i><?= __('config_editor') ?>
                    </h5>
                    <p class="text-muted small mb-0" id="modal-edit-subtitle"><?= __('tranches_subtitle') ?></p>
                </div>
                <button type="button" class="btn-close ms-auto flex-shrink-0" data-bs-dismiss="modal" aria-label="<?= __('cancel') ?>"></button>
            </div>
            <form id="modal-config-form" class="modal-config-form" action="/school_fees/tranches" method="POST" novalidate>
                <input type="hidden" name="csrf_token" value="<?= \App\Core\Session::generateCsrfToken() ?>">
                <input type="hidden" name="target_type" id="modal-target-type" value="">
                <input type="hidden" name="target_id"   id="modal-target-id"   value="">
                <div class="modal-body px-4 py-3">
                    <!-- Target selector (visible for new config) -->
                    <div id="modal-target-selector" class="mb-4 modal-target-panel">
                        <label class="form-label fw-bold text-muted-theme extra-small text-uppercase mb-2" style="letter-spacing:.5px;"><?= __('application_level') ?></label>
                        <select id="modal-level-select" class="form-select mi mb-3" onchange="handleModalLevelChange(this.value)">
                            <option value="" disabled selected><?= __('choose_level') ?></option>
                            <option value="class"><?= __('by_class') ?></option>
                            <option value="cycle"><?= __('by_cycle') ?></option>
                            <option value="teaching_type"><?= __('by_teaching_type') ?></option>
                        </select>
                        <div id="modal-sub-class" class="d-none">
                            <label class="form-label fw-bold text-muted-theme extra-small text-uppercase mb-2" style="letter-spacing:.5px;"><?= __('select_class_editor') ?></label>
                            <select id="modal-sel-class" class="form-select mi" onchange="handleModalTargetChange(this.value)">
                                <option value="" disabled selected><?= __('choose_class_placeholder') ?></option>
                                <?php foreach ($classes as $c): ?>
                                    <option value="<?= $c['id'] ?>"><?= h($c['nom']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div id="modal-sub-cycle" class="d-none">
                            <label class="form-label fw-bold text-muted-theme extra-small text-uppercase mb-2" style="letter-spacing:.5px;"><?= __('select_cycle_editor') ?></label>
                            <select id="modal-sel-cycle" class="form-select mi" onchange="handleModalTargetChange(this.value)">
                                <option value="" disabled selected><?= __('choose_cycle_placeholder') ?></option>
                                <?php foreach ($cycles as $cy): ?>
                                    <option value="<?= $cy['id'] ?>"><?= h($cy['nom']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div id="modal-sub-type" class="d-none">
                            <label class="form-label fw-bold text-muted-theme extra-small text-uppercase mb-2" style="letter-spacing:.5px;"><?= __('select_teaching_type_editor') ?></label>
                            <select id="modal-sel-type" class="form-select mi" onchange="handleModalTargetChange(this.value)">
                                <option value="" disabled selected><?= __('choose_type_placeholder') ?></option>
                                <?php foreach ($teachingTypes as $tt): ?>
                                    <option value="<?= $tt['id'] ?>"><?= h($tt['nom']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <!-- Budget bar -->
                    <div id="modal-budget-bar" class="mb-4 d-none modal-budget-panel">
                        <div class="d-flex align-items-center justify-content-between mb-2 flex-wrap gap-2">
                            <span class="small fw-semibold text-muted"><?= __('expected_tuition_lbl') ?> <strong id="modal-tuition-lbl" class="text-primary">-</strong></span>
                            <span class="small fw-semibold" id="modal-sum-lbl"><?= __('assigned_lbl') ?> <strong class="text-dark">0 FCFA</strong></span>
                        </div>
                        <div class="budget-bar-wrap">
                            <div id="modal-budget-fill" class="budget-bar-fill bg-primary" style="width:0%"></div>
                        </div>
                        <div id="modal-budget-msg" class="mt-2 text-center extra-small fw-bold"></div>
                    </div>
                    <!-- Loader -->
                    <div id="modal-loader" class="text-center modal-loader-panel d-none">
                        <div class="spinner-border text-primary" role="status"></div>
                        <p class="mt-2 text-muted small mb-0"><?= __('loading_config') ?></p>
                    </div>
                    <!-- Presets -->
                    <div id="modal-presets" class="mb-3 d-none modal-presets-panel">
                        <div class="d-flex flex-wrap gap-2 align-items-center">
                            <span class="extra-small text-muted fw-bold me-1"><?= __('quick_distribution') ?></span>
                            <button type="button" class="preset-chip" onclick="applyPreset([100])">100%</button>
                            <button type="button" class="preset-chip" onclick="applyPreset([50,50])">50 / 50</button>
                            <button type="button" class="preset-chip" onclick="applyPreset([60,40])">60 / 40</button>
                            <button type="button" class="preset-chip" onclick="applyPreset([50,25,25])">50 / 25 / 25</button>
                            <button type="button" class="preset-chip" onclick="applyPreset([40,30,30])">40 / 30 / 30</button>
                            <button type="button" class="preset-chip" onclick="splitEqual()"><i class="bi bi-distribute-horizontal me-1"></i><?= __('equi_btn') ?></button>
                        </div>
                    </div>
                    <!-- Tranche rows -->
                    <div id="modal-rows-container" class="mb-3"></div>
                    <!-- Add row -->
                    <button type="button" id="modal-btn-add" class="btn btn-sm btn-outline-primary rounded-pill px-3 d-none" onclick="modalAddRow('', 0, '', null)">
                        <i class="bi bi-plus-circle-fill me-1"></i><?= __('add_tranche') ?>
                    </button>
                </div>
                <div class="modal-footer border-0 px-4 pb-4 pt-0">
                    <button type="button" class="btn btn-light btn-modal-cancel rounded-pill px-4" data-bs-dismiss="modal"><?= __('cancel') ?></button>
                    <button type="submit" id="modal-btn-submit" class="btn btn-primary btn-modal-save rounded-pill px-5 fw-bold shadow-sm" disabled>
                        <i class="bi bi-check-circle-fill me-2"></i><?= __('save') ?>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<div id="toast-container" class="toast-container position-fixed top-0 end-0 p-4" style="z-index:9999;"></div>
<script>
(function() {
'use strict';
var modalTuition = 0;
var I18N = {
    defineLevelTarget: <?= json_encode(__('define_level_target')) ?>,
    selectTargetWarning: <?= json_encode(__('select_target_warning')) ?>,
    addAtLeastOneTranche: <?= json_encode(__('add_at_least_one_tranche')) ?>,
    savingInProgress: <?= json_encode(__('saving_in_progress')) ?>,
    configSavedSuccess: <?= json_encode(__('config_saved_success')) ?>,
    errorGeneric: <?= json_encode(__('error_generic')) ?>,
    networkError: <?= json_encode(__('network_error')) ?>,
    notDefined: <?= json_encode(__('not_defined')) ?>,
    errorTitle: <?= json_encode(__('error_title')) ?>,
    trancheRowNamePlaceholder: <?= json_encode(__('tranche_row_name_placeholder')) ?>,
    trancheName: <?= json_encode(__('tranche_name')) ?>,
    delete: <?= json_encode(__('delete')) ?>,
    amountFcfaLabel: <?= json_encode(__('amount_fcfa_label')) ?>,
    deadlineLabel: <?= json_encode(__('deadline_label')) ?>,
    assignedLbl: <?= json_encode(__('assigned_lbl')) ?>,
    balanced100Percent: <?= json_encode(__('balanced_100_percent')) ?>,
    excessOf: <?= json_encode(__('excess_of')) ?>,
    remainingBalance: <?= json_encode(__('remaining_balance')) ?>
};

function esc(s){ return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;'); }

function showToast(msg, type) {
    var tc = document.getElementById('toast-container');
    var el = document.createElement('div');
    el.className = 'toast align-items-center text-white bg-' + type + ' border-0 shadow-lg';
    el.setAttribute('role','alert'); el.setAttribute('aria-atomic','true');
    var icon = type === 'success' ? 'check-circle-fill' : (type === 'warning' ? 'exclamation-circle-fill' : 'exclamation-triangle-fill');
    el.innerHTML = '<div class="d-flex"><div class="toast-body fw-semibold"><i class="bi bi-'+icon+' me-2"></i>' + esc(msg) + '</div><button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button></div>';
    tc.appendChild(el);
    if (typeof bootstrap !== 'undefined') { var t = new bootstrap.Toast(el,{delay:4500}); t.show(); }
    else { setTimeout(function(){ el.remove(); }, 4500); }
    el.addEventListener('hidden.bs.toast', function(){ el.remove(); });
}

window.openEditModal = function(btn) {
    var type = btn.dataset.type, id = btn.dataset.id, label = btn.dataset.label;
    resetModalRows();
    document.getElementById('modal-target-selector').classList.add('d-none');
    document.getElementById('modal-edit-subtitle').textContent = label;
    document.getElementById('modal-target-type').value = type;
    document.getElementById('modal-target-id').value   = id;
    document.getElementById('modal-btn-submit').disabled = true;
    var modal = new bootstrap.Modal(document.getElementById('modal-edit-config'));
    modal.show();
    loadTargetConfig(type, id);
};

document.addEventListener('DOMContentLoaded', function() {
    var btnNew = document.getElementById('btn-open-new');
    var btnEmptyNew = document.getElementById('btn-empty-new');
    function openNewModal() {
        resetModalFull();
        document.getElementById('modal-target-selector').classList.remove('d-none');
        document.getElementById('modal-edit-subtitle').textContent = I18N.defineLevelTarget;
        document.getElementById('modal-target-type').value = '';
        document.getElementById('modal-target-id').value   = '';
        document.getElementById('modal-btn-submit').disabled = true;
        modalTuition = 0;
    }
    if (btnNew) btnNew.addEventListener('click', openNewModal);
    if (btnEmptyNew) btnEmptyNew.addEventListener('click', openNewModal);

    var si = document.getElementById('search-cfg');
    if (si) {
        si.addEventListener('input', function() {
            var q = this.value.toLowerCase().trim();
            document.querySelectorAll('.config-grid-item').forEach(function(c) {
                c.style.display = c.textContent.toLowerCase().includes(q) ? '' : 'none';
            });
        });
    }

    document.getElementById('modal-config-form').addEventListener('submit', function(e) {
        e.preventDefault();
        var tid  = document.getElementById('modal-target-id').value;
        var ttyp = document.getElementById('modal-target-type').value;
        if (!tid || !ttyp) { showToast(I18N.selectTargetWarning, 'warning'); return; }
        var rows = document.querySelectorAll('#modal-rows-container .modal-tranche-row');
        if (!rows.length) { showToast(I18N.addAtLeastOneTranche, 'warning'); return; }
        var btnSub = document.getElementById('modal-btn-submit');
        var oldHtml = btnSub.innerHTML;
        btnSub.disabled = true;
        btnSub.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>' + esc(I18N.savingInProgress);
        var fd = new FormData(document.getElementById('modal-config-form'));
        fd.append('ajax', '1');
        fetch('/school_fees/tranches', { method:'POST', body:fd, headers:{'X-Requested-With':'XMLHttpRequest'} })
        .then(function(r){ return r.json(); })
        .then(function(res) {
            if (res.success) {
                showToast(res.message || I18N.configSavedSuccess, 'success');
                var bsModal = bootstrap.Modal.getInstance(document.getElementById('modal-edit-config'));
                if (bsModal) bsModal.hide();
                reloadGrid();
            } else { showToast(res.message || I18N.errorGeneric, 'danger'); }
        })
        .catch(function(err){ showToast(I18N.networkError + ' ' + err.message, 'danger'); })
        .finally(function(){ btnSub.disabled=false; btnSub.innerHTML=oldHtml; });
    });
});

window.handleModalLevelChange = function(level) {
    ['modal-sub-class','modal-sub-cycle','modal-sub-type'].forEach(function(id){ document.getElementById(id).classList.add('d-none'); });
    document.getElementById('modal-target-type').value = level;
    document.getElementById('modal-target-id').value = '';
    resetModalRows();
    if (level==='class') document.getElementById('modal-sub-class').classList.remove('d-none');
    else if (level==='cycle') document.getElementById('modal-sub-cycle').classList.remove('d-none');
    else if (level==='teaching_type') document.getElementById('modal-sub-type').classList.remove('d-none');
};

window.handleModalTargetChange = function(id) {
    if (!id) return;
    document.getElementById('modal-target-id').value = id;
    var level = document.getElementById('modal-target-type').value;
    loadTargetConfig(level, id);
};

function loadTargetConfig(type, id) {
    showModalLoader(true);
    document.getElementById('modal-budget-bar').classList.add('d-none');
    document.getElementById('modal-presets').classList.add('d-none');
    document.getElementById('modal-btn-add').classList.add('d-none');
    document.getElementById('modal-btn-submit').disabled = true;
    resetModalRows();
    var url = window.location.pathname + '?ajax=1&target_type=' + encodeURIComponent(type) + '&target_id=' + encodeURIComponent(id);
    fetch(url, {headers:{'X-Requested-With':'XMLHttpRequest'}})
    .then(function(r){ if(!r.ok) throw new Error('HTTP '+r.status); return r.json(); })
    .then(function(d) {
        showModalLoader(false);
        modalTuition = parseFloat(d.tuition_amount) || 0;
        document.getElementById('modal-budget-bar').classList.remove('d-none');
        document.getElementById('modal-tuition-lbl').textContent = modalTuition > 0 ? modalTuition.toLocaleString('fr-FR') + ' FCFA' : I18N.notDefined;
        document.getElementById('modal-presets').classList.remove('d-none');
        document.getElementById('modal-btn-add').classList.remove('d-none');
        document.getElementById('modal-btn-submit').disabled = false;
        if (d.installments && d.installments.length > 0) {
            d.installments.forEach(function(inst, i){ modalAddRow(inst.name, parseFloat(inst.amount), inst.deadline_date, i+1); });
        } else {
            if (modalTuition > 0) applyPreset([50,25,25]);
            else modalAddRow(I18N.trancheName + ' 1', 0, '', 1);
        }
        calcModalSum();
    })
    .catch(function(err){
        showModalLoader(false);
        document.getElementById('modal-rows-container').innerHTML = '<div class="alert alert-danger small"><i class="bi bi-exclamation-triangle-fill me-2"></i>' + esc(I18N.errorTitle) + ' : ' + esc(err.message) + '</div>';
    });
}

window.modalAddRow = function(name, amount, deadline, num) {
    num = num || (document.querySelectorAll('#modal-rows-container .modal-tranche-row').length + 1);
    if (!deadline) { var dd = new Date(); dd.setDate(dd.getDate()+num*30); deadline = dd.toISOString().slice(0,10); }
    var row = document.createElement('div');
    row.className = 'modal-tranche-row row-anim';
    row.innerHTML =
        '<div class="d-flex align-items-center gap-2 mb-2">' +
        '<span class="badge bg-primary bg-opacity-10 text-primary fw-bold rounded-pill" style="font-size:.68rem;min-width:24px;">#'+num+'</span>' +
        '<input type="text" name="tranche_name[]" class="form-control form-control-sm mi flex-grow-1" placeholder="' + esc(I18N.trancheRowNamePlaceholder) + '" value="'+esc(name||(I18N.trancheName + ' '+num))+'" required>' +
        '<button type="button" class="btn btn-sm btn-link text-danger p-1 btn-rm-row" title="' + esc(I18N.delete) + '"><i class="bi bi-trash3-fill"></i></button>' +
        '</div>' +
        '<div class="row g-2">' +
        '<div class="col-6"><label class="form-label extra-small fw-bold text-muted mb-1"><i class="bi bi-cash-stack text-success me-1"></i>' + esc(I18N.amountFcfaLabel) + '</label>' +
        '<input type="number" name="tranche_amount[]" min="0" step="1" class="form-control form-control-sm mi input-amount" value="'+(amount||0)+'" required></div>' +
        '<div class="col-6"><label class="form-label extra-small fw-bold text-muted mb-1"><i class="bi bi-calendar-check text-info me-1"></i>' + esc(I18N.deadlineLabel) + '</label>' +
        '<input type="date" name="tranche_deadline[]" class="form-control form-control-sm mi input-deadline" value="'+deadline+'" required></div>' +
        '</div>';
    row.querySelector('.btn-rm-row').addEventListener('click', function(){
        row.classList.add('row-remove-anim');
        setTimeout(function(){ row.remove(); reindexRows(); calcModalSum(); }, 200);
    });
    row.querySelector('.input-amount').addEventListener('input', calcModalSum);
    document.getElementById('modal-rows-container').appendChild(row);
    calcModalSum();
};

function reindexRows() {
    document.querySelectorAll('#modal-rows-container .modal-tranche-row').forEach(function(r,i){
        var badge=r.querySelector('.badge'); if(badge) badge.textContent='#'+(i+1);
        var ni=r.querySelector('input[name="tranche_name[]"]');
        var reg = new RegExp('^' + I18N.trancheName + ' \\d+$');
        if(ni && (reg.test(ni.value.trim()) || /^Tranche \d+$/.test(ni.value.trim()))) ni.value = I18N.trancheName + ' ' + (i+1);
    });
}

function calcModalSum() {
    var sum=0;
    document.querySelectorAll('#modal-rows-container .input-amount').forEach(function(inp){ sum+=parseFloat(inp.value)||0; });
    var cls=(modalTuition>0&&sum===modalTuition)?'text-success':(sum>modalTuition&&modalTuition>0?'text-danger':'text-dark');
    document.getElementById('modal-sum-lbl').innerHTML= esc(I18N.assignedLbl) + ' <strong class="'+cls+'">'+sum.toLocaleString('fr-FR')+' FCFA</strong>';
    var fill=document.getElementById('modal-budget-fill'), msg=document.getElementById('modal-budget-msg');
    if(modalTuition>0){
        var pct=Math.min((sum/modalTuition)*100,100);
        fill.style.width=pct+'%';
        fill.className='budget-bar-fill '+(sum===modalTuition?'bg-success':sum>modalTuition?'bg-danger':'bg-primary');
        if(sum===modalTuition){msg.className='mt-1 text-center extra-small fw-bold text-success';msg.innerHTML='<i class="bi bi-check-circle-fill me-1"></i>' + esc(I18N.balanced100Percent);}
        else if(sum>modalTuition){msg.className='mt-1 text-center extra-small fw-bold text-danger';msg.innerHTML='<i class="bi bi-exclamation-triangle-fill me-1"></i>' + esc(I18N.excessOf) + ' '+(sum-modalTuition).toLocaleString('fr-FR')+' FCFA';}
        else{msg.className='mt-1 text-center extra-small fw-bold text-warning';msg.innerHTML='<i class="bi bi-info-circle-fill me-1"></i>' + esc(I18N.remainingBalance) + ' '+(modalTuition-sum).toLocaleString('fr-FR')+' FCFA';}
    } else { fill.style.width='0%'; msg.textContent=''; }
}

window.applyPreset = function(parts) {
    document.getElementById('modal-rows-container').innerHTML='';
    var sf=0;
    parts.forEach(function(pct,i){
        var amt=0;
        if(modalTuition>0){ amt=(i===parts.length-1)?Math.max(0,modalTuition-sf):Math.round((pct/100)*modalTuition); sf+=amt; }
        var d=new Date(); d.setDate(d.getDate()+(i+1)*30);
        modalAddRow(I18N.trancheName + ' '+(i+1), amt, d.toISOString().slice(0,10), i+1);
    });
    calcModalSum();
};

window.splitEqual = function() {
    var rows=document.querySelectorAll('#modal-rows-container .modal-tranche-row');
    if(!rows.length||modalTuition<=0) return;
    var n=rows.length, p=Math.floor(modalTuition/n), rem=modalTuition-p*n;
    rows.forEach(function(row,i){ var inp=row.querySelector('.input-amount'); if(inp) inp.value=(i===n-1)?p+rem:p; });
    calcModalSum();
};

function showModalLoader(show) {
    var el=document.getElementById('modal-loader');
    if(show) el.classList.remove('d-none'); else el.classList.add('d-none');
}

function resetModalRows() {
    document.getElementById('modal-rows-container').innerHTML='';
    document.getElementById('modal-loader').classList.add('d-none');
    document.getElementById('modal-budget-bar').classList.add('d-none');
    document.getElementById('modal-presets').classList.add('d-none');
    document.getElementById('modal-btn-add').classList.add('d-none');
    document.getElementById('modal-btn-submit').disabled=true;
}

function resetModalFull() {
    resetModalRows();
    document.getElementById('modal-level-select').value='';
    document.getElementById('modal-sel-class').value='';
    document.getElementById('modal-sel-cycle').value='';
    document.getElementById('modal-sel-type').value='';
    ['modal-sub-class','modal-sub-cycle','modal-sub-type'].forEach(function(id){ document.getElementById(id).classList.add('d-none'); });
    modalTuition=0;
}

function reloadGrid() {
    var grid=document.getElementById('configs-grid');
    grid.style.opacity='0.4';
    fetch(window.location.pathname)
    .then(function(r){ return r.text(); })
    .then(function(html){
        var doc=new DOMParser().parseFromString(html,'text/html');
        var ng=doc.getElementById('configs-grid');
        if(ng) grid.innerHTML=ng.innerHTML;
        grid.style.opacity='1';
    })
    .catch(function(e){ console.error(e); grid.style.opacity='1'; window.location.reload(); });
}

})();
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../templates/layout.php';
?>