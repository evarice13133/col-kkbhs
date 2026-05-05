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
    <!-- BARRE D'ACTIONS RAPIDES : Style Floating Island -->
    <div class="d-flex justify-content-center mb-5">
        <div class="filter-island px-3 py-2 shadow-lg animate-slide-down" style="min-width: 40%;">
            <div class="d-flex align-items-center justify-content-center gap-2 w-100">
                <a href="/notes"
                    class="btn btn-primary rounded-pill px-4 py-2 fw-bold shadow-sm scale-on-hover d-flex align-items-center gap-2">
                    <i class="bi bi-pencil-square"></i> <?= __('enter_marks') ?>
                </a>
            </div>
        </div>
    </div>

    <!-- Section KPI Enseignant : Standardisé Registry -->
    <div class="row g-3 g-md-4 mb-5">
        <div class="col-6 col-xl-3">
            <div class="registry-card p-3 p-md-4 border-0 shadow-sm h-100">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <div class="registry-icon text-primary"
                        style="background: rgba(var(--primary-rgb), 0.1); width: 40px; height: 40px; font-size: 1.2rem;">
                        <i class="bi bi-door-open-fill"></i>
                    </div>
                    <span
                        class="badge bg-primary bg-opacity-10 text-primary rounded-pill extra-small fw-bold d-none d-sm-inline-block">LIVE</span>
                </div>
                <div class="h2 fw-black registry-text-main mb-1" data-count-up="<?= (int) $stats_classes ?>">
                    <?= $stats_classes ?></div>
                <div class="registry-text-muted small fw-medium text-truncate"><?= __('assigned_classes_count') ?></div>
            </div>
        </div>
        <div class="col-6 col-xl-3">
            <div class="registry-card p-3 p-md-4 border-0 shadow-sm h-100">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <div class="registry-icon text-info"
                        style="background: rgba(13, 202, 240, 0.1); width: 40px; height: 40px; font-size: 1.2rem;">
                        <i class="bi bi-journal-check"></i>
                    </div>
                    <span
                        class="badge bg-info bg-opacity-10 text-info rounded-pill extra-small fw-bold d-none d-sm-inline-block">ACTIVE</span>
                </div>
                <div class="h2 fw-black registry-text-main mb-1" data-count-up="<?= (int) $stats_subjects ?>">
                    <?= $stats_subjects ?></div>
                <div class="registry-text-muted small fw-medium text-truncate"><?= __('disciplines_taught') ?></div>
            </div>
        </div>
        <div class="col-6 col-xl-3">
            <div class="registry-card p-3 p-md-4 border-0 shadow-sm h-100">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <div class="registry-icon text-success"
                        style="background: rgba(25, 135, 84, 0.1); width: 40px; height: 40px; font-size: 1.2rem;">
                        <i class="bi bi-check-all"></i>
                    </div>
                    <span
                        class="badge bg-success bg-opacity-10 text-success rounded-pill extra-small fw-bold d-none d-sm-inline-block">COMPLETED</span>
                </div>
                <div class="h2 fw-black registry-text-main mb-1" data-count-up="<?= (int) $stats_filled ?>">
                    <?= $stats_filled ?></div>
                <div class="registry-text-muted small fw-medium text-truncate"><?= __('confirmed_entries') ?></div>
            </div>
        </div>
        <div class="col-6 col-xl-3">
            <div class="registry-card p-3 p-md-4 border-0 shadow-sm h-100 overflow-hidden position-relative">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <div class="registry-icon text-warning"
                        style="background: rgba(255, 193, 7, 0.1); width: 40px; height: 40px; font-size: 1.2rem;">
                        <i class="bi bi-lightning-charge-fill"></i>
                    </div>
                    <div class="fw-bold text-warning small"><?= $stats_progress ?>%</div>
                </div>
                <div class="h2 fw-black registry-text-main mb-1" data-count-up="<?= (int) $stats_progress ?>"
                    data-suffix="%"><?= $stats_progress ?>%</div>
                <div class="registry-text-muted small fw-medium mb-3 text-truncate"><?= __('global_progress') ?></div>
                <div class="progress" style="height: 4px; border-radius: 10px; background: var(--border-color);">
                    <div class="progress-bar bg-warning shadow-sm" style="width: <?= $stats_progress ?>%"></div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-5">
        <!-- État des Séquences : Utilisation de registry-card -->
        <div class="col-xl-7">
            <div class="registry-card h-100 border-0 shadow-sm">
                <div class="p-4 border-bottom border-light border-opacity-10">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="fw-bold m-0 text-main"><?= __('active_sequences_state') ?></h5>
                        <span class="badge bg-primary bg-opacity-10 text-primary rounded-pill px-3 py-2 fw-bold small">
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
                                    <div
                                        class="p-2 p-md-4 border border-light border-opacity-10 rounded-4 bg-transparent transition-base h-100 scale-on-hover shadow-sm">
                                        <div class="d-flex justify-content-between align-items-center mb-2 mb-md-3">
                                            <span class="fw-bold text-main small text-truncate"><?= h($ev['label']) ?></span>
                                            <span
                                                class="level-badge <?= nm_level_class($ev['level_label'] ?? '') ?> d-none d-md-inline-block"><?= __($ev['level_label'] ?? 'A demarrer') ?></span>
                                        </div>
                                        <div class="d-flex align-items-end justify-content-between mb-2">
                                            <div class="fs-4 fs-md-2 fw-black text-primary lh-1"
                                                data-count-up="<?= (int) $ev['progress_percent'] ?>" data-suffix="%">
                                                <?= $ev['progress_percent'] ?>%</div>
                                            <div
                                                class="extra-small registry-text-muted fw-semibold text-muted d-none d-md-block">
                                                <?= $ev['filled_count'] ?> / <?= $ev['expected_count'] ?></div>
                                        </div>
                                        <div class="progress"
                                            style="height: 6px; border-radius: 10px; background: var(--border-color);">
                                            <div class="progress-bar bg-primary shadow-sm"
                                                style="width: <?= $ev['progress_percent'] ?>%"></div>
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
            <div class="registry-card h-100 border-0 shadow-sm overflow-hidden">
                <div class="p-4 border-bottom border-light border-opacity-10">
                    <h5 class="fw-bold m-0 text-main"><?= __('progress_by_class') ?></h5>
                </div>
                <div class="table-responsive">
                    <table class="registry-table mb-0">
                        <thead>
                            <tr class="bg-transparent">
                                <th class="ps-4 py-3 text-muted"><?= __('class') ?></th>
                                <th class="text-end pe-4 py-3 text-muted"><?= __('progress') ?></th>
                            </tr>
                        </thead>
                        <tbody class="bg-transparent">
                            <?php foreach ($classProgress as $cp): ?>
                                <tr class="bg-transparent border-bottom border-light border-opacity-10">
                                    <td class="ps-4 py-3 bg-transparent">
                                        <div class="fw-bold text-main text-muted"><?= h($cp['class_nom']) ?></div>
                                        <div class="small registry-text-muted text-muted"><?= $cp['student_count'] ?>
                                            <?= __('students_short') ?></div>
                                    </td>
                                    <td class="text-end pe-4 bg-transparent">
                                        <div class="d-flex align-items-center justify-content-end gap-3">
                                            <div class="text-end" style="min-width: 100px;">
                                                <div class="fw-bold text-primary mb-1"><?= $cp['progress_percent'] ?>%</div>
                                                <div class="progress ms-auto"
                                                    style="height: 4px; width: 80px; border-radius: 10px; background: var(--border-color);">
                                                    <div class="progress-bar bg-primary shadow-sm"
                                                        style="width: <?= $cp['progress_percent'] ?>%"></div>
                                                </div>
                                            </div>
                                            <span
                                                class="level-badge <?= nm_level_class($cp['level_label']) ?>"><?= __($cp['level_label']) ?></span>
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
    /* Floating Island Filters */
    .filter-island {
        background: rgba(var(--bg-card-rgb), 0.7);
        backdrop-filter: blur(20px) saturate(180%);
        border: 1px solid rgba(var(--primary-rgb), 0.15);
        border-radius: 100px;
        transition: all 0.3s ease;
    }

    [data-theme="dark"] .filter-island {
        background: rgba(30, 30, 45, 0.6);
        border-color: rgba(255, 255, 255, 0.08);
    }

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