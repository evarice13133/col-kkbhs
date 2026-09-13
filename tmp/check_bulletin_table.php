<?php
function __($k) {
    $map = [
        'subjects_column' => 'Matières',
        'competences' => 'Compétences',
        'average_short' => 'Moy',
        'coef' => 'Coef',
        'score' => 'Score',
        'rank' => 'Rang',
        'appreciation' => 'Appréciation',
        'teacher_signature' => 'Enseignant',
        'test_1' => 'SEQ 1',
        'test_2' => 'SEQ 2',
        'evaluations' => 'Évaluations',
        'first_term' => 'Premier trimestre',
        'second_term' => 'Deuxième trimestre',
        'third_term' => 'Troisième trimestre',
        'trimester' => 'Trimestre',
        'subjects_passed' => 'Matières validées / sur',
        'student_avg' => 'Moyenne de l\'élève',
        'terms_results' => 'Résultats du trimestre',
        'group_default' => 'Général',
        'subtotal' => 'Sous-total',
        'grand_total' => 'TOTAL GÉNÉRAL',
        'evaluation' => 'Évaluation'
    ];
    return $map[$k] ?? $k;
}
function formatNote($val){ if ($val === null || $val === '-') return '-'; $n = (float)$val; return '<span class="vert">'.number_format($n,2,',',' ').'</span>'; }
function formatSimple($val){ if ($val === null || $val === '-') return '-'; return number_format((float)$val,2,',',' '); }
$groupedRows = [['label'=>'Matières','rows'=>[]]];
$classStats = ['profile'=>['total'=>1,'unclassified'=>0,'below'=>0,'middle'=>0,'passed'=>1]];
$term = 1;
$bulletinPeriod = 'trimestre';
$termSequences = [['label'=>'S1','code'=>'SEQ 1'],['label'=>'S2','code'=>'SEQ 2']];
$termAverages = [12.5,11.3,13.2];
$termRanks = [2,4,1];
$evaluationLabels = ['SEQ 1', 'SEQ 2', 'Trimestre'];
$seqAverages = [10.5,11.5];
$seqRanks = [3,2];
include 'src/Views/bulletins/bulletin_grades_table.php';
