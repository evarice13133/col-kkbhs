<?php
$showCompetences = ($_GET['show_competences'] ?? '1') !== '0';
?>
<button
    type="button"
    class="pv-btn pv-btn-competences"
    data-competence-toggle
    data-show-label="<?= htmlspecialchars(__('bulletin_show_competencies'), ENT_QUOTES, 'UTF-8') ?>"
    data-hide-label="<?= htmlspecialchars(__('bulletin_hide_competencies'), ENT_QUOTES, 'UTF-8') ?>"
    aria-pressed="<?= $showCompetences ? 'true' : 'false' ?>">
    <?= $showCompetences ? __('bulletin_hide_competencies') : __('bulletin_show_competencies') ?>
</button>
<script>
document.querySelectorAll('[data-competence-toggle]').forEach((button) => {
    button.addEventListener('click', () => {
        const showCompetences = button.getAttribute('aria-pressed') !== 'true';
        button.setAttribute('aria-pressed', String(showCompetences));
        button.textContent = showCompetences ? button.dataset.hideLabel : button.dataset.showLabel;
        document.body.classList.toggle('competences-hidden', !showCompetences);
        document.querySelectorAll('.report-card-grid').forEach((grid) => {
            grid.classList.toggle('competences-hidden', !showCompetences);
        });
        document.querySelectorAll('[data-colspan-visible][data-colspan-hidden]').forEach((cell) => {
            cell.colSpan = Number(showCompetences ? cell.dataset.colspanVisible : cell.dataset.colspanHidden);
        });

        const url = new URL(window.location.href);
        url.searchParams.set('show_competences', showCompetences ? '1' : '0');
        window.history.replaceState(null, '', url);

        document.querySelectorAll('.pv-btn-download').forEach((link) => {
            const downloadUrl = new URL(link.href, window.location.href);
            downloadUrl.searchParams.set('show_competences', showCompetences ? '1' : '0');
            link.href = downloadUrl.toString();
        });
    });
});
</script>
