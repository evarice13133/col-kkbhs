<?php
$title = "Types de Réductions";

// Calculate KPIs
$totalTypes = count($discountTypes);
$activeTypes = 0;
$inactiveTypes = 0;
foreach ($discountTypes as $dt) {
    if ($dt['status'] === 'active') {
        $activeTypes++;
    } else {
        $inactiveTypes++;
    }
}

ob_start();
?>

<div class="animate-fade-in container-fluid py-3 px-md-4">
    <!-- Header -->
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h2 class="fw-black text-main-theme mb-0 fs-4">Types de Réductions</h2>
            <p class="text-muted-theme small mb-0">Configuration et administration des catégories de réductions et bourses</p>
        </div>
    </div>

    <!-- KPI Summary Row -->
    <div class="row g-3 mb-4 animate-fade-in">
        <div class="col-6 col-md-4">
            <div class="modern-card kpi-card border-0 shadow-sm">
                <div class="kpi-icon-wrapper bg-primary bg-opacity-10 text-primary">
                    <i class="bi bi-tags-fill"></i>
                </div>
                <div class="kpi-value text-primary"><?= $totalTypes ?></div>
                <div class="kpi-label">Total des Catégories</div>
            </div>
        </div>
        <div class="col-6 col-md-4">
            <div class="modern-card kpi-card border-0 shadow-sm">
                <div class="kpi-icon-wrapper bg-success bg-opacity-10 text-success">
                    <i class="bi bi-check-circle-fill"></i>
                </div>
                <div class="kpi-value text-success"><?= $activeTypes ?></div>
                <div class="kpi-label">Catégories Actives</div>
            </div>
        </div>
        <div class="col-12 col-md-4">
            <div class="modern-card kpi-card border-0 shadow-sm">
                <div class="kpi-icon-wrapper bg-secondary bg-opacity-10 text-secondary">
                    <i class="bi bi-x-circle-fill"></i>
                </div>
                <div class="kpi-value text-secondary"><?= $inactiveTypes ?></div>
                <div class="kpi-label">Catégories Inactives</div>
            </div>
        </div>
    </div>

    <!-- Toolbar Row -->
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
        <div class="d-flex align-items-center flex-wrap gap-3">
            <div class="input-group search-pill bg-white bg-opacity-10 rounded-pill px-2 border-theme-light" style="max-width: 280px; border: 1px solid var(--border-color);">
                <span class="input-group-text border-0 bg-transparent text-primary py-1.5">
                    <i class="bi bi-search"></i>
                </span>
                <input type="text" id="type-search-input" class="form-control border-0 bg-transparent shadow-none py-1.5 small text-main" placeholder="Recherche rapide..." style="font-size: 0.85rem;">
            </div>
        </div>

        <div>
            <button type="button" class="btn btn-primary rounded-pill px-4 fw-bold shadow-sm" id="btn-add-type">
                <i class="bi bi-plus-circle-fill me-2"></i> Nouveau type
            </button>
        </div>
    </div>

    <!-- Table Card -->
    <div class="modern-card border-0 shadow-sm overflow-hidden animate-fade-in">
        <div class="table-responsive">
            <table class="table-modern" id="types-table">
                <thead>
                    <tr>
                        <th class="ps-4">Nom du Type</th>
                        <th>Description</th>
                        <th>Commentaire Admin</th>
                        <th>Date de Création</th>
                        <th>Créateur</th>
                        <th class="text-center">Statut</th>
                        <th class="pe-4 text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($discountTypes)): ?>
                        <tr>
                            <td colspan="7" class="text-center py-5 text-muted">
                                <i class="bi bi-info-circle fs-4 d-block mb-2 text-secondary"></i>
                                Aucun type de réduction enregistré.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($discountTypes as $type): ?>
                            <tr class="type-row" data-id="<?= $type['id'] ?>">
                                <td class="ps-4 fw-bold text-main-theme">
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="avatar-init bg-primary bg-opacity-10 text-primary fw-bold rounded-circle d-flex align-items-center justify-content-center shadow-sm"
                                             style="width: 32px; height: 32px; font-size: 0.85rem; border: 1px solid rgba(var(--primary-rgb), 0.2);">
                                            <?= strtoupper(substr((string) $type['name'], 0, 1)) ?>
                                        </div>
                                        <span><?= h($type['name']) ?></span>
                                    </div>
                                </td>
                                <td>
                                    <div class="text-muted-theme text-truncate" style="max-width: 220px;" title="<?= h($type['description']) ?>">
                                        <?= !empty($type['description']) ? h($type['description']) : '<span class="opacity-50">-</span>' ?>
                                    </div>
                                </td>
                                <td>
                                    <div class="text-muted-theme text-truncate" style="max-width: 180px;" title="<?= h($type['comment']) ?>">
                                        <?= !empty($type['comment']) ? h($type['comment']) : '<span class="opacity-50">-</span>' ?>
                                    </div>
                                </td>
                                <td>
                                    <?= date('d/m/Y', strtotime($type['created_at'])) ?>
                                </td>
                                <td>
                                    <span class="text-muted small">
                                        <?= !empty($type['creator_nom']) ? h($type['creator_prenom'] . ' ' . $type['creator_nom']) : '<span class="opacity-50">-</span>' ?>
                                    </span>
                                </td>
                                <td class="text-center">
                                    <span style="cursor: pointer;" class="badge-premium <?= $type['status'] === 'active' ? 'badge-premium-success' : 'badge-premium-secondary' ?> toggle-status-btn" data-id="<?= $type['id'] ?>" title="Cliquer pour changer le statut">
                                        <?php if ($type['status'] === 'active'): ?>
                                            <i class="bi bi-check-circle-fill"></i> Active
                                        <?php else: ?>
                                            <i class="bi bi-x-circle-fill"></i> Inactive
                                        <?php endif; ?>
                                    </span>
                                </td>
                                <td class="pe-4 text-center">
                                    <div class="d-flex gap-1 justify-content-center">
                                        <button type="button" class="btn btn-sm btn-action-modern text-primary edit-type-btn" 
                                                data-id="<?= $type['id'] ?>"
                                                data-name="<?= h($type['name']) ?>"
                                                data-description="<?= h($type['description'] ?? '') ?>"
                                                data-comment="<?= h($type['comment'] ?? '') ?>"
                                                data-status="<?= h($type['status']) ?>"
                                                title="Modifier">
                                            <i class="bi bi-pencil-square fs-5"></i>
                                        </button>
                                        <button type="button" class="btn btn-sm btn-action-modern text-danger delete-type-btn" data-id="<?= $type['id'] ?>" title="Supprimer">
                                            <i class="bi bi-trash fs-5"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal unique pour Ajouter/Modifier un Type de Réduction -->
<div class="modal fade" id="discountTypeModal" tabindex="-1" aria-labelledby="discountTypeModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 rounded-4 shadow">
            <form id="discountTypeForm">
                <input type="hidden" name="csrf_token" value="<?= \App\Core\Session::generateCsrfToken() ?>">
                <input type="hidden" name="id" id="type_id" value="0">
                
                <div class="modal-header border-bottom-0 pb-0 px-4 pt-4">
                    <h5 class="modal-title fw-black text-main-theme" id="discountTypeModalLabel">Nouveau Type de Réduction</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label text-muted-theme fw-bold extra-small text-uppercase mb-1">Nom du Type *</label>
                            <input type="text" name="name" id="type_name" class="form-control premium-input fw-bold" placeholder="Ex: Excellence, Cas social, Fratrie..." required>
                        </div>
                        <div class="col-12">
                            <label class="form-label text-muted-theme fw-bold extra-small text-uppercase mb-1">Description</label>
                            <textarea name="description" id="type_description" class="form-control premium-input" rows="3" placeholder="Description de l'éligibilité ou de l'objectif..."></textarea>
                        </div>
                        <div class="col-12">
                            <label class="form-label text-muted-theme fw-bold extra-small text-uppercase mb-1">Commentaire interne</label>
                            <textarea name="comment" id="type_comment" class="form-control premium-input" rows="2" placeholder="Notes complémentaires pour l'administration..."></textarea>
                        </div>
                        <div class="col-12">
                            <label class="form-label text-muted-theme fw-bold extra-small text-uppercase mb-1">Statut initial *</label>
                            <select name="status" id="type_status" class="form-select premium-input" required>
                                <option value="active" selected>Actif (Disponible pour attribution)</option>
                                <option value="inactive">Inactif (Indisponible pour de nouvelles attributions)</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="modal-footer border-top-0 pt-0 px-4 pb-4">
                    <button type="button" class="btn btn-light-theme rounded-pill px-4" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-5 fw-bold shadow-sm">
                        <i class="bi bi-check-circle-fill me-2"></i>Valider
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const modalElement = document.getElementById('discountTypeModal');
    const modal = new bootstrap.Modal(modalElement);
    const form = document.getElementById('discountTypeForm');
    
    const labelTitle = document.getElementById('discountTypeModalLabel');
    const fieldId = document.getElementById('type_id');
    const fieldName = document.getElementById('type_name');
    const fieldDescription = document.getElementById('type_description');
    const fieldComment = document.getElementById('type_comment');
    const fieldStatus = document.getElementById('type_status');
    
    // Open modal for Create
    document.getElementById('btn-add-type').addEventListener('click', function() {
        labelTitle.textContent = "Nouveau Type de Réduction";
        fieldId.value = "0";
        form.reset();
        modal.show();
    });

    // Open modal for Edit
    document.addEventListener('click', function(e) {
        const btnEdit = e.target.closest('.edit-type-btn');
        if (btnEdit) {
            e.preventDefault();
            labelTitle.textContent = "Modifier le Type de Réduction";
            
            fieldId.value = btnEdit.dataset.id;
            fieldName.value = btnEdit.dataset.name;
            fieldDescription.value = btnEdit.dataset.description;
            fieldComment.value = btnEdit.dataset.comment;
            fieldStatus.value = btnEdit.dataset.status;
            
            modal.show();
        }
    });

    // Form Submission (Create or Update)
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(form);
        
        fetch('/discount_types/store', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                modal.hide();
                AlertService.toast('success', data.message);
                setTimeout(() => {
                    window.location.reload();
                }, 1000);
            } else {
                AlertService.error('Erreur', data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            AlertService.toast('error', 'Une erreur est survenue lors de la communication avec le serveur.');
        });
    });

    // Status Toggle
    document.addEventListener('click', function(e) {
        const btnToggle = e.target.closest('.toggle-status-btn');
        if (btnToggle) {
            e.preventDefault();
            const id = btnToggle.dataset.id;
            
            fetch(`/discount_types/toggle?id=${id}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    AlertService.toast('success', data.message);
                    
                    // Update badge dynamically
                    if (data.status === 'active') {
                        btnToggle.className = 'badge-premium badge-premium-success toggle-status-btn';
                        btnToggle.innerHTML = '<i class="bi bi-check-circle-fill"></i> Active';
                        
                        const editBtn = btnToggle.closest('tr').querySelector('.edit-type-btn');
                        if (editBtn) editBtn.dataset.status = 'active';
                    } else {
                        btnToggle.className = 'badge-premium badge-premium-secondary toggle-status-btn';
                        btnToggle.innerHTML = '<i class="bi bi-x-circle-fill"></i> Inactive';
                        
                        const editBtn = btnToggle.closest('tr').querySelector('.edit-type-btn');
                        if (editBtn) editBtn.dataset.status = 'inactive';
                    }
                    
                    // Refresh after a delay to update top KPIs
                    setTimeout(() => {
                        window.location.reload();
                    }, 1000);
                } else {
                    AlertService.toast('error', data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                AlertService.toast('error', 'Erreur lors du changement de statut.');
            });
        }
    });

    // Deletion
    document.addEventListener('click', function(e) {
        const btnDelete = e.target.closest('.delete-type-btn');
        if (btnDelete) {
            e.preventDefault();
            const id = btnDelete.dataset.id;
            const row = btnDelete.closest('tr');
            
            AlertService.confirmDelete(
                'Supprimer ce type ?',
                'Voulez-vous vraiment supprimer ce type de réduction ? Cette action est définitive et impossible si le type est déjà attribué.'
            ).then((result) => {
                if (result.isConfirmed) {
                    fetch(`/discount_types/delete?id=${id}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            AlertService.toast('success', data.message);
                            row.style.transition = 'all 0.5s ease';
                            row.style.opacity = '0';
                            row.style.transform = 'translateX(50px)';
                            setTimeout(() => {
                                row.remove();
                                window.location.reload();
                            }, 500);
                        } else {
                            AlertService.error('Erreur', data.message);
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        AlertService.toast('error', 'Erreur lors de la suppression.');
                    });
                }
            });
        }
    });

    // Quick Search filter
    const searchInput = document.getElementById('type-search-input');
    if (searchInput) {
        searchInput.addEventListener('input', function(e) {
            const query = e.target.value.toLowerCase().normalize("NFD").replace(/[\u0300-\u036f]/g, "").trim();
            const rows = document.querySelectorAll('#types-table tbody tr');
            
            rows.forEach(row => {
                if (row.querySelector('td[colspan]')) return; // ignore empty state
                const text = row.textContent.toLowerCase().normalize("NFD").replace(/[\u0300-\u036f]/g, "");
                if (text.includes(query)) {
                    row.style.setProperty('display', '', 'important');
                } else {
                    row.style.setProperty('display', 'none', 'important');
                }
            });
        });
    }
});
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../templates/layout.php';
?>
