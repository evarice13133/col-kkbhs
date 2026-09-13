<?php
$isTechnicalBulletin = ($evaluation_form ?? 'general') === 'technical';
$bulletinPeriod = $bulletinPeriod ?? 'sequence';
$showEvaluationTests = $bulletinPeriod === 'trimestre';
$showAnnualTerms = $bulletinPeriod === 'annuel';
$subjectCountForSizing = (int) ($subjectCount ?? 0);
$tableColumns = $showEvaluationTests ? 10 : ($showAnnualTerms ? 11 : 9);
$groupLabelColspan = $showEvaluationTests ? 4 : ($showAnnualTerms ? 5 : 3);
$grandPoints = 0.0;
$grandCoefficients = 0.0;
$passedSubjects = 0;
$totalSubjects = 0;
?>
<div class="report-card-grid <?= $isTechnicalBulletin ? 'report-card-grid-technical' : '' ?><?= $subjectCountForSizing < 17 ? ' report-card-grid-under-17' : '' ?>">
    <table class="report-card-header-table">
        <colgroup>
            <col class="col-subject">
            <col class="col-competence">
            <?php if ($showEvaluationTests): ?><col class="col-test"><col class="col-test"><?php endif; ?>
            <?php if ($showAnnualTerms): ?><col class="col-term"><col class="col-term"><col class="col-term"><?php endif; ?>
            <col class="col-average"><col class="col-coefficient"><col class="col-score"><col class="col-rank"><col class="col-appreciation"><col class="col-teacher-signature">
        </colgroup>
        <thead>
            <tr>
                <th rowspan="2"><?= __('subjects_column') ?></th>
                <th rowspan="2"><?= __('competences') ?></th>
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
                <th rowspan="2"><?= __('teacher_signature') ?></th>
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
            <col class="col-average"><col class="col-coefficient"><col class="col-score"><col class="col-rank"><col class="col-appreciation"><col class="col-teacher-signature">
        </colgroup>
        <tbody>
            <?php foreach ($groupedRows as $groupIndex => $group): ?>
                <tr class="report-card-group-header">
                    <th colspan="<?= $tableColumns ?>">
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
                        <td class="teacher-signature-cell"><?= htmlspecialchars((string) ($row['teacher'] ?? '-')) ?></td>
                    </tr>
                <?php endforeach; ?>
                <tr class="report-card-subtotal">
                    <td colspan="<?= $groupLabelColspan ?>"><?= __('subtotal') ?> <?= $groupIndex + 1 ?></td>
                    <td></td>
                    <td><?= formatSimple($groupCoefficients) ?></td>
                    <td><?= formatSimple($groupPoints) ?></td>
                    <td colspan="3"></td>
                </tr>
            <?php endforeach; ?>
            <tr class="report-card-grand-total">
                <th colspan="<?= $groupLabelColspan ?>"><?= __('grand_total') ?></th>
                    <th></th>
                    <th><?= formatSimple($grandCoefficients) ?></th>
                    <th><?= formatSimple($grandPoints) ?></th>
                <th colspan="3"><?= $passedSubjects ?> / <?= $totalSubjects ?></th>
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
    $classAverage = $classStats['average'] ?? null;
    $classMax = $classStats['max'] ?? null;
    $classMin = $classStats['min'] ?? null;
    $classSuccessRate = $classStats['success_rate'] ?? 0;
    $classPassedCount = (int) ($classStats['profile']['passed'] ?? 0);
    $disciplineValues = $discipline ?? [];
    $disciplineLate = (int) ($disciplineValues['late_count'] ?? $disciplineValues['retards'] ?? 0);
    $disciplineTotalAbsences = (int) (($disciplineValues['absences']['total'] ?? $disciplineValues['absences_total'] ?? 0));
    $disciplineJustified = (int) (($disciplineValues['absences']['justified'] ?? $disciplineValues['absences_justified'] ?? 0));
    $disciplineUnjustified = max(0, $disciplineTotalAbsences - $disciplineJustified);
    $disciplinePunishment = (int) ($disciplineValues['exclusion_days'] ?? 0);
    $councilDecision = __('average_level');
    if (($average ?? 0) < 5) {
        $councilDecision = __('poor');
    } elseif (($average ?? 0) < 8) {
        $councilDecision = __('weak');
    } elseif (($average ?? 0) < 10) {
        $councilDecision = __('below_average');
    } elseif (($average ?? 0) < 12) {
        $councilDecision = __('average_level');
    } elseif (($average ?? 0) < 14) {
        $councilDecision = __('fairly_good');
    } elseif (($average ?? 0) < 16) {
        $councilDecision = __('good');
    } elseif (($average ?? 0) < 18) {
        $councilDecision = __('very_good');
    } else {
        $councilDecision = __('excellent');
    }
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
</div>
