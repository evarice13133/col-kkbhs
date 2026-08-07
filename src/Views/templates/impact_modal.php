<?php
/**
 * Modale unifiée Radiographie d'Impact
 */
?>
<!-- Modale Radiographie d'Impact -->
<div class="modal fade modal-impact-radiography" id="impactRadiographyModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content shadow-lg border-0">
            <input type="hidden" id="impactCsrfToken" value="<?= h(\App\Core\Session::generateCsrfToken()) ?>">
            <div id="impactModalBody">
                <!-- Le contenu de l'analyse sera injecté ici dynamiquement via JS -->
            </div>
        </div>
    </div>
</div>
