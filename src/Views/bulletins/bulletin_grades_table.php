<?php
$isTechnicalBulletin = ($evaluation_form ?? 'general') === 'technical';
$bulletinPeriod = $bulletinPeriod ?? 'sequence';
$showEvaluationTests = $bulletinPeriod === 'trimestre';
$grandPoints = 0.0;
$grandCoefficients = 0.0;
$passedSubjects = 0;
$totalSubjects = 0;
?>
<div class="report-card-grid <?= $isTechnicalBulletin ? 'report-card-grid-technical' : '' ?>">
    <table class="report-card-main-table">
        <colgroup>
            <col class="col-subject">
            <col class="col-competence">
            <?php if ($showEvaluationTests): ?><col class="col-test"><col class="col-test"><?php endif; ?>
            <col class="col-average"><col class="col-coefficient"><col class="col-score"><col class="col-appreciation">
        </colgroup>
        <thead>
            <tr>
                    <th rowspan="2"><?= __('subject') ?></th>
                    <th rowspan="2"><?= __('competence') ?></th>
                <?php if ($showEvaluationTests): ?>
                    <th colspan="2"><?= __('evaluation') ?></th>
                <?php else: ?>
                    <th rowspan="2"><?= htmlspecialchars((string) (($evaluationLabels[0] ?? null) ?: __('evaluation'))) ?></th>
                <?php endif; ?>
                <th rowspan="2"><?= __('average') ?><br>(/20)</th>
                <th rowspan="2"><?= __('coef') ?></th>
                <th rowspan="2"><?= __('score') ?></th>
                <th rowspan="2"><?= __('appreciation') ?></th>
            </tr>
            <?php if ($showEvaluationTests): ?>
                <tr>
                    <th><?= htmlspecialchars((string) (($evaluationLabels[0] ?? null) ?: __('test_1'))) ?></th>
                    <th><?= htmlspecialchars((string) (($evaluationLabels[1] ?? null) ?: __('test_2'))) ?></th>
                </tr>
            <?php endif; ?>
        </thead>
        <tbody>
            <?php foreach ($groupedRows as $groupIndex => $group): ?>
                <tr class="report-card-group-header">
                    <th colspan="<?= 5 + ($showEvaluationTests ? 2 : 0) ?>">
                        <?= htmlspecialchars((string) $group['label']) ?>
                    </th>
                </tr>
                <?php $groupPoints = 0.0; $groupCoefficients = 0.0; ?>
                <?php foreach ($group['rows'] as $row): ?>
                    <?php
                    $averageValue = $row['note'] ?? $row['term_note'] ?? $row['annual_note'] ?? null;
                    $scoreValue = $row['weighted'] ?? (($averageValue !== null) ? (float) $averageValue * (float) ($row['coefficient'] ?? 0) : null);
                    $groupPoints += (float) ($scoreValue ?? 0);
                    $groupCoefficients += (float) ($row['coefficient'] ?? 0);
                    $grandPoints += (float) ($scoreValue ?? 0);
                    $grandCoefficients += (float) ($row['coefficient'] ?? 0);
                    $totalSubjects++;
                    if ($averageValue !== null && (float) $averageValue >= 10) $passedSubjects++;
                    ?>
                    <tr>
                        <td class="report-card-subject">
                            <?= htmlspecialchars((string) $row['subject']) ?>
                            <?php if (!empty($row['teacher'])): ?><small class="report-card-teacher"><?= htmlspecialchars((string) $row['teacher']) ?></small><?php endif; ?>
                        </td>
                        <td class="report-card-competence"><?= htmlspecialchars((string) ($row['competence'] ?? '')) ?></td>
                        <?php if ($showEvaluationTests): ?>
                            <td><?= isset($row['test_1']) && $row['test_1'] !== null ? formatNote($row['test_1']) : '-' ?></td>
                            <td><?= isset($row['test_2']) && $row['test_2'] !== null ? formatNote($row['test_2']) : '-' ?></td>
                        <?php else: ?>
                            <td><?= formatNote($averageValue) ?></td>
                        <?php endif; ?>
                        <td><?= formatNote($averageValue) ?></td>
                        <td><?= (int) ($row['coefficient'] ?? 0) ?></td>
                        <td><?= formatSimple($scoreValue) ?></td>
                        <td><?= htmlspecialchars((string) ($row['appreciation'] ?? '-')) ?></td>
                    </tr>
                <?php endforeach; ?>
                <tr class="report-card-subtotal">
                    <td colspan="<?= 2 + ($showEvaluationTests ? 2 : 0) ?>"><?= __('subtotal') ?> <?= $groupIndex + 1 ?></td>
                    <td><?= formatSimple($groupCoefficients) ?></td>
                    <td><?= formatSimple($groupPoints) ?></td>
                    <td colspan="<?= 1 + ($isTechnicalBulletin ? 0 : 0) ?>"></td>
                </tr>
            <?php endforeach; ?>
            <tr class="report-card-grand-total">
                <th colspan="<?= 2 + ($showEvaluationTests ? 2 : 0) ?>"><?= __('grand_total') ?></th>
                    <th><?= formatSimple($grandCoefficients) ?></th>
                    <th><?= formatSimple($grandPoints) ?></th>
                <th colspan="<?= 1 + ($isTechnicalBulletin ? 0 : 0) ?>"><?= $passedSubjects ?> / <?= $totalSubjects ?></th>
            </tr>
        </tbody>
    </table>
    <div class="report-card-legend">
        <strong><?= __('competency_level_key') ?></strong>
        <span>4: <?= __('excellent') ?></span>
        <span>3: <?= __('good') ?></span>
        <span>2: <?= __('average_level') ?></span>
        <span>1: <?= __('below_average') ?></span>
    </div>
    <div class="report-card-lower-grid">
        <table>
            <tr><th colspan="2"><?= __('grades_summary') ?></th></tr>
            <tr><td><?= __('total') ?> (/20)</td><td><?= formatSimple($grandPoints) ?></td></tr>
            <tr><td><?= __('student_avg') ?> (/20)</td><td><?= formatNote($average ?? null) ?></td></tr>
            <tr><td><?= __('terms_results') ?></td><td><?= htmlspecialchars(implode(' / ', $evaluationLabels ?? [])) ?></td></tr>
        </table>
        <table>
            <tr><th><?= __('subjects_passed') ?></th></tr>
            <tr><td class="report-card-kpi"><?= $passedSubjects ?> / <?= $totalSubjects ?></td></tr>
        </table>
        <table>
            <tr><th><?= __('class_performance') ?></th></tr>
            <tr><td><?= __('highest_average') ?>: <?= formatSimple($classStats['max'] ?? null) ?></td></tr>
            <tr><td><?= __('lowest_average') ?>: <?= formatSimple($classStats['min'] ?? null) ?></td></tr>
            <tr><td><?= __('class_avg_gen') ?>: <?= formatSimple($classStats['average'] ?? null) ?></td></tr>
        </table>
    </div>
    <table class="report-card-decision-table">
        <tr><th><?= __('discipline') ?></th><th><?= __('council_decision') ?></th><th><?= __('class_performance') ?></th><th><?= __('principal_remarks_sign') ?></th></tr>
        <tr>
            <td><?= __('absences') ?>: <?= (int) ($discipline['absences']['total'] ?? 0) ?><br><?= __('justified') ?>: <?= (int) ($discipline['absences']['justified'] ?? 0) ?><br><?= __('unjustified') ?>: <?= (int) ($discipline['absences']['unjustified'] ?? 0) ?></td>
            <td><?= __('skills_not_acquired') ?> [ ]<br><?= __('skills_in_process') ?> [ ]<br><?= __('skills_acquired') ?> [ ]<br><?= __('promoted') ?> [ ]<br><?= __('repeat') ?> [ ]</td>
            <td><?= __('highest_average') ?>: <?= formatSimple($classStats['max'] ?? null) ?><br><?= __('lowest_average') ?>: <?= formatSimple($classStats['min'] ?? null) ?><br><?= __('subjects_passed') ?>: <?= $passedSubjects ?> / <?= $totalSubjects ?></td>
            <td class="remarks-space"></td>
        </tr>
    </table>
    <div class="report-card-signatures">
        <span><?= __('fees_owing') ?>: ................................................</span>
        <span><?= __('class_master') ?>: ................................................</span>
        <span><?= __('parent_sign') ?>: ................................................</span>
    </div>
</div>
