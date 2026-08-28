<?php
$isTechnicalBulletin = ($evaluation_form ?? 'general') === 'technical';
$bulletinPeriod = $bulletinPeriod ?? 'sequence';
$showEvaluationTests = $bulletinPeriod === 'trimestre';
$showAnnualTerms = $bulletinPeriod === 'annuel';
$grandPoints = 0.0;
$grandCoefficients = 0.0;
$passedSubjects = 0;
$totalSubjects = 0;
?>
<div class="report-card-grid <?= $isTechnicalBulletin ? 'report-card-grid-technical' : '' ?>">
    <table class="report-card-header-table">
        <colgroup>
            <col class="col-subject">
            <col class="col-competence">
            <?php if ($showEvaluationTests): ?><col class="col-test"><col class="col-test"><?php endif; ?>
            <?php if ($showAnnualTerms): ?><col class="col-term"><col class="col-term"><col class="col-term"><?php endif; ?>
            <col class="col-average"><col class="col-coefficient"><col class="col-score"><col class="col-rank"><col class="col-appreciation">
        </colgroup>
        <thead>
            <tr>
                <th rowspan="2"><?= __('subject') ?></th>
                <th rowspan="2"><?= __('competence') ?></th>
                <?php if ($showEvaluationTests): ?>
                    <th colspan="2"><?= htmlspecialchars((string) (($evaluationLabels[2] ?? null) ?: __('evaluation'))) ?></th>
                <?php elseif ($showAnnualTerms): ?>
                    <th colspan="3"><?= __('evaluation') ?></th>
                <?php else: ?>
                    <th rowspan="2"><?= htmlspecialchars((string) (($evaluationLabels[0] ?? null) ?: __('evaluation'))) ?></th>
                <?php endif; ?>
                <th rowspan="2"><?= __('average_short') ?> (/20)</th>
                <th rowspan="2"><?= __('coef') ?></th>
                <th rowspan="2"><?= __('score') ?></th>
                <th rowspan="2"><?= __('rank') ?></th>
                <th rowspan="2" class="appreciation-header"><?= __('appreciation') ?></th>
            </tr>
            <?php if ($showEvaluationTests): ?>
                <tr>
                    <th><?= htmlspecialchars((string) (($evaluationLabels[0] ?? null) ?: __('test_1'))) ?></th>
                    <th><?= htmlspecialchars((string) (($evaluationLabels[1] ?? null) ?: __('test_2'))) ?></th>
                </tr>
            <?php elseif ($showAnnualTerms): ?>
                <tr>
                    <th><?= __('trimester_short') ?> 1</th>
                    <th><?= __('trimester_short') ?> 2</th>
                    <th><?= __('trimester_short') ?> 3</th>
                </tr>
            <?php endif; ?>
        </thead>
    </table>
    <table class="report-card-main-table">
        <colgroup>
            <col class="col-subject">
            <col class="col-competence">
            <?php if ($showEvaluationTests): ?><col class="col-test"><col class="col-test"><?php endif; ?>
            <?php if ($showAnnualTerms): ?><col class="col-term"><col class="col-term"><col class="col-term"><?php endif; ?>
            <col class="col-average"><col class="col-coefficient"><col class="col-score"><col class="col-rank"><col class="col-appreciation">
        </colgroup>
        <tbody>
            <?php foreach ($groupedRows as $groupIndex => $group): ?>
                <tr class="report-card-group-header">
                    <th colspan="<?= 7 + ($showEvaluationTests ? 2 : ($showAnnualTerms ? 3 : 0)) ?>">
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
                        <?php elseif ($showAnnualTerms): ?>
                            <?php foreach (array_pad((array) ($row['term_values'] ?? []), 3, null) as $termValue): ?>
                                <td><?= $termValue !== null ? formatNote($termValue) : '-' ?></td>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <td><?= formatNote($averageValue) ?></td>
                        <?php endif; ?>
                        <td><?= formatNote($averageValue) ?></td>
                        <td><?= (int) ($row['coefficient'] ?? 0) ?></td>
                        <td><?= formatSimple($scoreValue) ?></td>
                        <td><?= htmlspecialchars((string) ($row['rank_subject'] ?? '-')) ?></td>
                        <td class="appreciation-cell"><?= htmlspecialchars((string) ($row['appreciation'] ?? '-')) ?></td>
                    </tr>
                <?php endforeach; ?>
                <tr class="report-card-subtotal">
                    <td colspan="<?= 2 + ($showEvaluationTests ? 2 : ($showAnnualTerms ? 3 : 0)) ?>"><?= __('subtotal') ?> <?= $groupIndex + 1 ?></td>
                    <td></td>
                    <td><?= formatSimple($groupCoefficients) ?></td>
                    <td><?= formatSimple($groupPoints) ?></td>
                    <td colspan="2"></td>
                </tr>
            <?php endforeach; ?>
            <tr class="report-card-grand-total">
                <th colspan="<?= 2 + ($showEvaluationTests ? 2 : ($showAnnualTerms ? 3 : 0)) ?>"><?= __('grand_total') ?></th>
                    <th></th>
                    <th><?= formatSimple($grandCoefficients) ?></th>
                    <th><?= formatSimple($grandPoints) ?></th>
                <th colspan="2"><?= $passedSubjects ?> / <?= $totalSubjects ?></th>
            </tr>
        </tbody>
    </table>
    <div class="report-card-legend">
        <strong><?= __('competency_level_key') ?></strong>
        <span><b>CTBA</b> : <?= __('ctba_desc') ?></span>
        <span><b>CBA</b> : <?= __('cba_desc') ?></span>
        <span><b>CA</b> : <?= __('ca_desc') ?></span>
        <span><b>CMA</b> : <?= __('cma_desc') ?></span>
        <span><b>CNA</b> : <?= __('cna_desc') ?></span>
    </div>
    <?php
    $profile = $classStats['profile'] ?? [];
    $profileTotal = (int) ($profile['total'] ?? $effectif);
    $profileValues = [
        (int) ($profile['unclassified'] ?? 0),
        (int) ($profile['below'] ?? 0),
        (int) ($profile['middle'] ?? 0),
        (int) ($profile['passed'] ?? 0),
    ];
    $profilePercentages = array_map(static function ($value) use ($profileTotal) {
        return $profileTotal > 0 ? formatSimple(($value / $profileTotal) * 100) . '%' : '0%';
    }, $profileValues);
    $workKey = ($discipline['encouragements'] ?? '') === 'X' ? 'work_good' : (($average ?? 0) >= 14 ? 'work_excellent' : (($average ?? 0) >= 12 ? 'work_good' : (($average ?? 0) >= 10 ? 'work_passable' : 'work_bad')));
    $workTrendKey = 'trend_stable';
    $workTrendClass = '';
    if (count($seqAverages ?? []) >= 2 && $seqAverages[0] !== null && $seqAverages[count($seqAverages) - 1] !== null) {
        $firstEvaluationAverage = (float) $seqAverages[0];
        $lastEvaluationAverage = (float) $seqAverages[count($seqAverages) - 1];
        if ($lastEvaluationAverage > $firstEvaluationAverage) {
            $workTrendKey = 'trend_up';
            $workTrendClass = 'vert';
        } elseif ($lastEvaluationAverage < $firstEvaluationAverage) {
            $workTrendKey = 'trend_down';
            $workTrendClass = 'rouge';
        }
    }
    $workBlameText = strtoupper(__('no'));
    $workBlameClass = '';
    if ($workTrendKey === 'trend_down') {
        $workBlameText = strtoupper(__('yes'));
        $workBlameClass = 'rouge';
    } elseif ($workTrendKey === 'trend_up') {
        $workBlameClass = 'vert';
    }
    ?>
    <table class="report-card-statistics-table">
        <colgroup>
            <col span="4" class="stats-student-col">
            <col span="4" class="stats-work-col">
            <col span="6" class="stats-profile-col">
        </colgroup>
        <thead>
            <tr>
                <th colspan="4" rowspan="2"><?= __('discipline') ?> <?= __('student') ?></th>
                <th colspan="4" rowspan="2"><?= __('student_work') ?></th>
                <th colspan="6"><?= __('class_profile') ?></th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td><?= __('absence_total_short') ?></td><td><?= (int) ($discipline['absences']['total'] ?? 0) ?></td>
                <td><?= __('conduct_note') ?></td><td><?= htmlspecialchars((string) (($discipline['conduct'] ?? '') !== '' ? $discipline['conduct'] : '-')) ?></td>
                <td><?= __('subjects_count') ?></td><td><?= $passedSubjects ?> / <?= $totalSubjects ?></td>
                <td><?= __('honour_roll') ?></td><td><?= (($discipline['tableau_honneur'] ?? '') === 'X' || (($discipline['tableau_honneur'] ?? '') === '' && ($average ?? 0) >= 12)) ? strtoupper(__('yes')) : strtoupper(__('no')) ?></td>
                <td></td><td><?= __('unclassified') ?></td><td>0 - 4,9</td><td>5,00 - 9,99</td><td>10,00 - 20,00</td><td><?= __('total') ?></td>
            </tr>
            <tr>
                <td><?= __('unjustified') ?></td><td><?= (int) ($discipline['absences']['unjustified'] ?? 0) ?></td>
                <td><?= __('warning_conduct_short') ?></td><td><?= htmlspecialchars((string) ($discipline['warning_conduct'] ?? '-')) ?></td>
                <td><?= __('total') ?> / <?= __('coef') ?></td><td><?= formatSimple($grandPoints) ?> / <?= formatSimple($grandCoefficients) ?></td>
                <td><?= __('encouragements') ?></td><td><?= __($workKey) ?></td>
                <td><?= __('effectif') ?></td><?php foreach ($profileValues as $value): ?><td><?= $value ?></td><?php endforeach; ?><td><?= $profileTotal ?></td>
            </tr>
            <tr>
                <td><?= __('absence_justified_short') ?></td><td><?= (int) ($discipline['absences']['justified'] ?? 0) ?></td>
                <td><?= __('blame_conduct') ?></td><td><?= htmlspecialchars((string) ($discipline['blame_conduct'] ?? '-')) ?></td>
                <td class="student-average-label <?= ($average ?? 0) >= 10 ? 'average-positive' : 'average-negative' ?>"><?= __('student_avg') ?></td><td class="student-average-value <?= ($average ?? 0) >= 10 ? 'average-positive' : 'average-negative' ?>"><?= formatNote($average ?? null) ?></td>
                <td><?= __('felicitations') ?></td><td><?= (($discipline['felicitations'] ?? '') === 'X' || (($discipline['felicitations'] ?? '') === '' && ($average ?? 0) >= 14)) ? strtoupper(__('yes')) : strtoupper(__('no')) ?></td>
                <td><?= __('percentage') ?></td><?php foreach ($profilePercentages as $percentage): ?><td><?= $percentage ?></td><?php endforeach; ?><td>100%</td>
            </tr>
            <tr>
                <td><?= __('consignes') ?> (hrs)</td><td><?= (int) ($discipline['consignes'] ?? 0) ?></td>
                <td><?= __('exclusions') ?> (hrs)</td><td>-</td>
                <td><?= __('student_rank') ?></td><td><?= $rank !== null ? $rank . '/' . $effectif : '-' ?></td>
                <td><?= __('warn_work') ?></td><td class="<?= $workTrendClass ?>"><?= __($workTrendKey) ?></td>
                <td><?= __('first_average') ?></td><td><?= formatSimple($classStats['first_average'] ?? null) ?></td><td><?= __('last_average') ?></td><td><?= formatSimple($classStats['last_average'] ?? null) ?></td><td><?= __('success_rate') ?></td><td><?= isset($classStats['success_rate']) ? formatSimple($classStats['success_rate']) . '%' : '-' ?></td>
            </tr>
            <tr>
                <td><?= __('consignes') ?> (jnr)</td><td>-</td>
                <td><?= __('exclusions') ?> (jnr)</td><td><?= sprintf('%02d', (int) ($discipline['exclusion_days'] ?? 0)) ?></td>
                <td><?= __('mention') ?></td><td><?= htmlspecialchars((string) ($mention ?? '-')) ?></td>
                <td><?= __('work_blame') ?></td><td class="<?= $workBlameClass ?>"><?= $workBlameText ?></td>
                <td><?= __('class_size') ?></td><td><?= $profileTotal ?></td><td><?= __('subjects_passed') ?></td><td><?= $passedSubjects ?></td><td><?= __('total') ?></td><td><?= $totalSubjects ?></td>
            </tr>
            <tr>
                <td><?= __('delays') ?></td><td>-</td><td></td><td></td>
                <td><?= __('terms_results') ?></td><td><?= htmlspecialchars(implode(' / ', $evaluationLabels ?? [])) ?></td>
                <td><?= __('general_observation') ?></td><td><?= htmlspecialchars((string) ($globalAppreciation ?? '-')) ?></td>
                <td><?= __('class_avg_gen') ?></td><td><?= formatSimple($classStats['average'] ?? null) ?></td><td colspan="4"></td>
            </tr>
        </tbody>
    </table>
    <?php if (!$showAnnualTerms): ?>
    <table class="report-card-footer-table">
        <thead>
            <tr>
                <th><?= __('class_council_observations') ?></th>
                <th><?= __('parent_visa') ?></th>
                <th><?= __('class_teacher') ?></th>
                <th><?= __('school_head') ?></th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td class="footer-observation <?= ($average ?? 0) > 10 ? 'vert' : 'rouge' ?>">
                    <?= htmlspecialchars((string) ($globalAppreciation ?? '-')) ?>
                    (<?= htmlspecialchars((string) ($overallAcquisitionLevel ?? '-')) ?>)
                </td>
                <td class="footer-signature"></td>
                <td class="footer-signature"><?= htmlspecialchars((string) ($professor_name ?? '')) ?></td>
                <td class="footer-signature"></td>
            </tr>
        </tbody>
    </table>
    <?php endif; ?>
</div>
