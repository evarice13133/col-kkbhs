<?php
$title = __('dashboard');
// 
if (!function_exists('nm_level_class')) {
    function nm_level_class($label)
    {
        $map = [
            'Excellent' => 'level-excellent',
            'Bon' => 'level-bon',
            'Moyen' => 'level-moyen',
            'Faible' => 'level-faible',
            'A demarrer' => 'level-ademarrer',
        ];

        $lang = \App\Core\Session::get('app_lang', 'fr');
        if ($lang === 'en') {
            $transMap = [
                'Excellent' => 'Excellent',
                'Bon' => 'Good',
                'Moyen' => 'Average',
                'Faible' => 'Weak',
                'A demarrer' => 'To start',
            ];
            $label = $transMap[$label] ?? $label;
        }

        return $map[$label] ?? 'level-ademarrer';
    }
}

if (!function_exists('nm_evaluation_short_label')) {
    function nm_evaluation_short_label($label)
    {
        if (preg_match('/Trimestre\s+(\d+)\s*-\s*Sequence\s+(\d+)/i', (string) $label, $matches)) {
            return 'T' . $matches[1] . ' S' . $matches[2];
        }
        return (string) $label;
    }
}

ob_start();
?>

<div class="animate-fade-in teacher-analytics container-fluid py-4">

    <!-- Header & Quick Actions Banner -->
    <div class="modern-card border-0 shadow-sm p-4 mb-4" style="background: linear-gradient(135deg, rgba(var(--primary-rgb), 0.06) 0%, rgba(var(--primary-rgb), 0.02) 100%); border-radius: 16px;">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
            <div class="d-flex align-items-center gap-3">
                <div class="p-3 bg-primary bg-opacity-10 text-primary rounded-circle shadow-sm">
                    <i class="bi bi-person-workspace fs-3"></i>
                </div>
                <div>
                    <h4 class="fw-black text-main-theme m-0" style="font-family: 'Outfit', sans-serif; letter-spacing: -0.02em;">Espace Pédagogique Enseignant</h4>
                    <p class="text-muted-theme small mb-0">Bienvenue sur votre portail de suivi et de saisie des évaluations.</p>
                </div>
            </div>
            <div class="d-flex align-items-center gap-2 flex-wrap">
                <?php if (!empty($has_lmd_classes)): ?>
                    <a href="/timetables"
                        class="btn btn-outline-info rounded-pill px-3 py-2 fw-bold shadow-sm scale-on-hover d-flex align-items-center gap-2"
                        title="Consulter l'emploi du temps des classes du Supérieur LMD">
                        <i class="bi bi-calendar3-week"></i> Mon Emploi du Temps
                    </a>
                <?php endif; ?>
                <a href="/students"
                    class="btn btn-outline-primary rounded-pill px-3 py-2 fw-bold shadow-sm scale-on-hover d-flex align-items-center gap-2">
                    <i class="bi bi-people"></i> <?= __('my_students') ?? 'Mes Élèves' ?>
                </a>
                <a href="/notes"
                    class="btn btn-primary rounded-pill px-4 py-2 fw-bold shadow-sm scale-on-hover d-flex align-items-center gap-2">
                    <i class="bi bi-pencil-square"></i> <?= __('enter_marks') ?>
                </a>
            </div>
        </div>
    </div>

    <!-- Section KPI Enseignant : SaaS/ERP Modern style -->
    <div class="row g-3 g-md-4 mb-4">
        <!-- Classes Affectées -->
        <div class="col-6 col-md-3">
            <div class="erp-stat-card card-primary shadow-sm rounded-4 p-3">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <div class="kpi-value fs-2 fw-black" data-count-up="<?= (int) $stats_classes ?>"><?= $stats_classes ?></div>
                        <div class="kpi-label text-muted extra-small text-uppercase fw-bold mt-1"><?= __('assigned_classes_count') ?></div>
                    </div>
                    <div class="erp-icon-box p-3 rounded-3 bg-primary bg-opacity-10 text-primary">
                        <i class="bi bi-door-open-fill fs-4"></i>
                    </div>
                </div>
            </div>
        </div>
        <!-- Disciplines Enseignées -->
        <div class="col-6 col-md-3">
            <div class="erp-stat-card card-info shadow-sm rounded-4 p-3">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <div class="kpi-value fs-2 fw-black text-info" data-count-up="<?= (int) $stats_subjects ?>"><?= $stats_subjects ?></div>
                        <div class="kpi-label text-muted extra-small text-uppercase fw-bold mt-1"><?= __('disciplines_taught') ?></div>
                    </div>
                    <div class="erp-icon-box p-3 rounded-3 bg-info bg-opacity-10 text-info">
                        <i class="bi bi-journal-check fs-4"></i>
                    </div>
                </div>
            </div>
        </div>
        <!-- Saisies Confirmées -->
        <div class="col-6 col-md-3">
            <div class="erp-stat-card card-success shadow-sm rounded-4 p-3">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <div class="kpi-value fs-2 fw-black text-success" data-count-up="<?= (int) $stats_filled ?>"><?= $stats_filled ?></div>
                        <div class="kpi-label text-muted extra-small text-uppercase fw-bold mt-1"><?= __('confirmed_entries') ?></div>
                    </div>
                    <div class="erp-icon-box p-3 rounded-3 bg-success bg-opacity-10 text-success">
                        <i class="bi bi-check-all fs-4"></i>
                    </div>
                </div>
            </div>
        </div>
        <!-- Progression Globale -->
        <div class="col-6 col-md-3">
            <div class="erp-stat-card card-warning shadow-sm rounded-4 p-3">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <div class="kpi-value fs-2 fw-black text-warning" data-count-up="<?= (int) $stats_progress ?>" data-suffix="%"><?= $stats_progress ?>%</div>
                        <div class="kpi-label text-muted extra-small text-uppercase fw-bold mt-1"><?= __('global_progress') ?></div>
                    </div>
                    <div class="erp-icon-box p-3 rounded-3 bg-warning bg-opacity-10 text-warning">
                        <i class="bi bi-lightning-charge-fill fs-4"></i>
                    </div>
                </div>
                <div class="progress mt-2" style="height: 5px; border-radius: 10px; background: rgba(var(--primary-rgb), 0.08);">
                    <div class="progress-bar bg-warning shadow-sm" style="width: <?= $stats_progress ?>%"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Section Principale : Mes Affectations et Accès Rapides -->
    <?php if (!empty($teacherAssignments)): ?>
        <div class="modern-card border-0 shadow-sm rounded-4 mb-4 p-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="fw-bold m-0 text-main-theme d-flex align-items-center gap-2 fs-6">
                    <i class="bi bi-briefcase text-primary"></i> Mes Affectations & Accès Rapide Saisie
                </h5>
                <a href="/notes" class="btn btn-sm btn-outline-primary rounded-pill px-3 fw-bold">
                    Toutes mes saisies →
                </a>
            </div>
            <div class="row g-3">
                <?php foreach ($teacherAssignments as $assign): ?>
                    <div class="col-md-6 col-lg-4">
                        <div class="p-3 border rounded-3 bg-white bg-opacity-5 d-flex align-items-center justify-content-between gap-2 shadow-xs hover-shadow transition-all">
                            <div>
                                <div class="fw-bold text-main-theme" style="font-size: 0.9rem;"><?= htmlspecialchars($assign['subject_nom'] ?? 'Matière') ?></div>
                                <div class="extra-small text-muted d-flex align-items-center gap-1 mt-1">
                                    <span class="badge bg-primary bg-opacity-10 text-primary rounded-pill px-2 py-0-5 extra-small">
                                        <i class="bi bi-door-open me-1"></i><?= htmlspecialchars($assign['class_nom']) ?>
                                    </span>
                                </div>
                            </div>
                            <a href="/notes/saisie?class_id=<?= $assign['class_id'] ?>&subject_id=<?= $assign['subject_id'] ?>" 
                               class="btn btn-sm btn-primary rounded-pill px-3 shadow-xs scale-on-hover flex-shrink-0 d-flex align-items-center gap-1">
                                <i class="bi bi-pencil-square"></i> Saisir
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>

    <!-- Pédagogie : Séquences (7/12) + Progression par Classe (5/12) -->
    <div class="row g-4 mb-5">
        <div class="col-xl-7">
            <div class="registry-card h-100 border-0 shadow-sm rounded-4">
                <div class="p-4 border-bottom border-light border-opacity-10">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="fw-bold m-0 text-main fs-6"><?= __('active_sequences_state') ?></h5>
                        <span class="badge bg-primary bg-opacity-10 text-primary rounded-pill px-3 py-1-5 fw-bold extra-small">
                            <?= count($evaluationStats) ?> <?= __('sequences') ?>
                        </span>
                    </div>
                </div>
                <div class="p-4">
                    <div class="row g-4">
                        <?php if (empty($evaluationStats)): ?>
                            <div class="col-12 text-center py-5 text-muted border border-dashed rounded-4 opacity-50">
                                <i class="bi bi-calendar-x fs-1 d-block mb-2 opacity-25"></i>
                                <?= __('no_active_sequence') ?>
                            </div>
                        <?php else: ?>
                            <?php foreach ($evaluationStats as $ev): ?>
                                <div class="col-12 col-sm-6">
                                    <div class="p-3 border border-light border-opacity-10 rounded-4 bg-transparent transition-base h-100 scale-on-hover shadow-xs">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <span class="fw-bold text-main small text-truncate"><?= h($ev['label']) ?></span>
                                            <span class="level-badge <?= nm_level_class($ev['level_label'] ?? '') ?> d-none d-md-inline-block"><?= __($ev['level_label'] ?? 'A demarrer') ?></span>
                                        </div>
                                        <div class="d-flex align-items-end justify-content-between mb-2">
                                            <div class="fs-3 fw-black text-primary lh-1" data-count-up="<?= (int) $ev['progress_percent'] ?>" data-suffix="%">
                                                <?= $ev['progress_percent'] ?>%</div>
                                            <div class="extra-small registry-text-muted fw-semibold text-muted d-none d-md-block">
                                                <?= $ev['filled_count'] ?> / <?= $ev['expected_count'] ?></div>
                                        </div>
                                        <div class="progress" style="height: 6px; border-radius: 10px; background: var(--border-color);">
                                            <div class="progress-bar bg-primary shadow-sm" style="width: <?= $ev['progress_percent'] ?>%"></div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Progression par Classe : Utilisation de registry-table -->
        <div class="col-xl-5">
            <div class="registry-card h-100 border-0 shadow-sm overflow-hidden rounded-4">
                <div class="p-4 border-bottom border-light border-opacity-10">
                    <h5 class="fw-bold m-0 text-main fs-6"><?= __('progress_by_class') ?></h5>
                </div>
                <div class="table-responsive">
                    <table class="registry-table mb-0">
                        <thead>
                            <tr class="bg-transparent">
                                <th class="ps-4 py-3 text-muted extra-small text-uppercase"><?= __('class') ?></th>
                                <th class="text-end pe-4 py-3 text-muted extra-small text-uppercase"><?= __('progress') ?></th>
                            </tr>
                        </thead>
                        <tbody class="bg-transparent">
                            <?php foreach ($classProgress as $cp): ?>
                                <tr class="bg-transparent border-bottom border-light border-opacity-10">
                                    <td class="ps-4 py-3 bg-transparent">
                                        <div class="fw-bold text-main-theme" style="font-size: 0.88rem;"><?= h($cp['class_nom']) ?></div>
                                        <div class="extra-small text-muted"><?= $cp['student_count'] ?> <?= __('students_short') ?></div>
                                    </td>
                                    <td class="text-end pe-4 bg-transparent">
                                        <div class="d-flex align-items-center justify-content-end gap-3">
                                            <div class="text-end" style="min-width: 90px;">
                                                <div class="fw-bold text-primary mb-1 extra-small"><?= $cp['progress_percent'] ?>%</div>
                                                <div class="progress ms-auto" style="height: 4px; width: 70px; border-radius: 10px; background: var(--border-color);">
                                                    <div class="progress-bar bg-primary shadow-sm" style="width: <?= $cp['progress_percent'] ?>%"></div>
                                                </div>
                                            </div>
                                            <span class="level-badge <?= nm_level_class($cp['level_label']) ?>"><?= __($cp['level_label']) ?></span>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
        </div>
    </div>
</div>

<!-- Support WhatsApp Floating Widget -->

<div id="whatsapp-support" class="wa-float-container">
    <button class="wa-close-toggle" onclick="dismissSupport()" title="Masquer l'assistance">
        <i class="bi bi-x"></i>
    </button>
    <a href="https://wa.me/237679164801?text=Bonjour%20support%20NotesMaster%2C%20je%20souhaite%20une%20assistance%20sur%20mon%20espace%20enseignant."
        target="_blank" class="wa-fab shadow-lg">
        <div class="wa-icon-box">
            <i class="bi bi-whatsapp"></i>
        </div>
        <span class="wa-label-text"><?= __('technical_support') ?></span>
    </a>
</div>

<script>
    function dismissSupport() {
        const support = document.getElementById('whatsapp-support');
        support.style.opacity = '0';
        support.style.transform = 'translateY(20px) scale(0.9)';
        setTimeout(() => support.remove(), 300);
        // Optionnel: Persister avec localStorage
        localStorage.setItem('nm_wa_support_dismissed', 'true');
    }

    document.addEventListener('DOMContentLoaded', () => {
        // Restaurer l'état
        if (localStorage.getItem('nm_wa_support_dismissed') === 'true') {
            document.getElementById('whatsapp-support')?.remove();
        }



        document.querySelectorAll('[data-count-up]').forEach((element, index) => {
            const target = Number(element.dataset.countUp || '0');
            const suffix = element.dataset.suffix || '';
            if (!Number.isFinite(target)) return;

            const duration = 800;
            const start = performance.now();

            const tick = (now) => {
                const progress = Math.min((now - start) / duration, 1);
                const easeProgress = 1 - Math.pow(1 - progress, 5);
                const value = Math.round(target * easeProgress);
                element.childNodes[0].nodeValue = `${value}${suffix}`;
                if (progress < 1) requestAnimationFrame(tick);
            };

            setTimeout(() => requestAnimationFrame(tick), 200 + (index * 50));
        });
    });
</script>

<style>


    .wa-float-container {
        position: fixed;
        bottom: 30px;
        right: 30px;
        z-index: 2000;
        display: flex;
        align-items: center;
        transition: all 0.4s cubic-bezier(0.16, 1, 0.3, 1);
    }

    .wa-close-toggle {
        position: absolute;
        top: -8px;
        left: -8px;
        width: 24px;
        height: 24px;
        background: #fff;
        border: 1px solid #eee;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 14px;
        cursor: pointer;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        z-index: 2001;
        opacity: 0;
        transform: scale(0.5);
        transition: all 0.3s ease;
    }

    .wa-float-container:hover .wa-close-toggle {
        opacity: 1;
        transform: scale(1);
    }

    .wa-fab {
        background: #25D366;
        color: white;
        text-decoration: none;
        display: flex;
        align-items: center;
        height: 56px;
        border-radius: 28px;
        width: 56px;
        /* Circle by default */
        overflow: hidden;
        transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        box-shadow: 0 10px 30px rgba(37, 211, 102, 0.3);
    }

    .wa-fab:hover {
        width: 220px;
        /* Expand on hover */
        background: #20BA5A;
        color: white;
    }

    .wa-icon-box {
        min-width: 56px;
        height: 56px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 28px;
    }

    .wa-label-text {
        white-space: nowrap;
        font-weight: 700;
        padding-right: 25px;
        opacity: 0;
        transform: translateX(-10px);
        transition: all 0.3s ease;
    }

    .wa-fab:hover .wa-label-text {
        opacity: 1;
        transform: translateX(0);
    }

    .fw-black {
        font-weight: 800;
    }

    .scale-on-hover {
        transition: transform 0.2s ease;
    }

    .scale-on-hover:hover {
        transform: scale(1.05);
    }

    /* Animations */
    .animate-slide-down {
        animation: slideDown 0.6s cubic-bezier(0.23, 1, 0.32, 1);
    }

    @keyframes slideDown {
        from {
            transform: translateY(-20px);
            opacity: 0;
        }

        to {
            transform: translateY(0);
            opacity: 1;
        }
    }

    @keyframes fade-in-up {
        from {
            opacity: 0;
            transform: translateY(30px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .animate-fade-in {
        animation: fade-in-up 0.6s cubic-bezier(0.16, 1, 0.3, 1) forwards;
    }
</style>

<?php
$content = ob_get_clean();
include __DIR__ . '/../templates/layout.php';
?>