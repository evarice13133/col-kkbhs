<?php
$title = 'WhatsApp - NotesMaster';
ob_start();
?>
<style>
.whatsapp-module {
    color: #263449;
    background-color: #f8fafc;
}
.whatsapp-module .card {
    color: #263449;
    background: #fff;
}
.whatsapp-module .form-control,
.whatsapp-module .form-select,
.whatsapp-module .list-group-item {
    color: #263449;
    background-color: #fff;
    border-color: #d8dee8;
}
.whatsapp-module .text-muted,
.whatsapp-module .form-text {
    color: #5b6678 !important;
}
.whatsapp-module .form-control::placeholder {
    color: #6b7280;
    opacity: 1;
}
.whatsapp-module details > summary {
    cursor: pointer;
}
[data-theme="dark"] .whatsapp-module .card {
    color: #e5e7eb;
    background: #1f2937;
    border-color: #374151 !important;
}
[data-theme="dark"] .whatsapp-module {
    color: #e5e7eb;
    background-color: #111827;
}
[data-theme="dark"] .whatsapp-module .form-control,
[data-theme="dark"] .whatsapp-module .form-select,
[data-theme="dark"] .whatsapp-module .list-group-item {
    color: #e5e7eb;
    background-color: #111827;
    border-color: #4b5563;
}
[data-theme="dark"] .whatsapp-module .form-control::placeholder {
    color: #9ca3af;
}
[data-theme="dark"] .whatsapp-module .text-muted,
[data-theme="dark"] .whatsapp-module .form-text {
    color: #cbd5e1 !important;
}
[data-theme="dark"] .whatsapp-module .bg-light {
    color: #e5e7eb !important;
    background-color: #374151 !important;
}
</style>
<div class="container-fluid py-4 whatsapp-module">
    <?php if (\App\Core\Session::get('user_role') === 'superadmin'): ?>
        <div class="card shadow-sm border-0 rounded-4 mb-4">
            <div class="card-body p-4 d-flex justify-content-between align-items-center flex-wrap gap-3">
                <div>
                    <h5 class="fw-bold mb-1">Accès au module WhatsApp</h5>
                    <p id="whatsapp-module-status" class="text-muted mb-0" role="status">
                        <?= $whatsappModuleEnabled ? 'Le module est activé pour les utilisateurs autorisés.' : 'Le module est désactivé pour les utilisateurs.' ?>
                    </p>
                </div>
                <button
                    class="btn <?= $whatsappModuleEnabled ? 'btn-outline-danger' : 'btn-success' ?>"
                    type="button"
                    id="btn-toggle-whatsapp-module"
                    data-enabled="<?= $whatsappModuleEnabled ? '1' : '0' ?>"
                >
                    <?= $whatsappModuleEnabled ? 'Désactiver le module' : 'Activer le module' ?>
                </button>
            </div>
        </div>
        <script>
        window.WHATSAPP_CSRF_TOKEN = <?= json_encode(\App\Core\Session::generateCsrfToken(), JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
        document.getElementById('btn-toggle-whatsapp-module').addEventListener('click', async event => {
            const button = event.currentTarget;
            const status = document.getElementById('whatsapp-module-status');
            const enabled = button.dataset.enabled !== '1';
            button.disabled = true;
            status.textContent = 'Mise à jour en cours…';
            try {
                const response = await fetch('/api/whatsapp/module-status', {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'X-CSRF-Token': window.WHATSAPP_CSRF_TOKEN
                    },
                    body: JSON.stringify({ enabled })
                });
                const result = await response.json();
                if (!response.ok) {
                    throw new Error(result.message || 'Impossible de modifier l’accès au module.');
                }
                window.location.reload();
            } catch (error) {
                status.textContent = error.message || 'Impossible de modifier l’accès au module.';
                button.disabled = false;
            }
        });
        </script>
    <?php endif; ?>
    <?php if (!$whatsappModuleEnabled): ?>
        <div class="alert alert-warning rounded-4" role="status">
            Le module WhatsApp est désactivé. Seul le superadministrateur peut le réactiver.
        </div>
    <?php else: ?>
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
        <div>
            <h1 class="fw-bold mb-1">WhatsApp</h1>
            <p class="text-muted mb-0">Configurez une fois, choisissez les élèves, puis envoyez votre notification.</p>
        </div>
        <button class="btn btn-outline-secondary rounded-3" type="button" id="btn-refresh-preview">
            <i class="bi bi-arrow-clockwise me-1"></i> Actualiser les journaux
        </button>
    </div>

    <div class="alert alert-info rounded-4" role="status">
        <strong>Pour commencer :</strong> laissez le mode test activé. Vérifiez la connexion Meta avant tout envoi réel.
        En mode test, aucun message WhatsApp réel n’est envoyé.
    </div>

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card shadow-sm border-0 rounded-4">
                <div class="card-body p-4">
                    <h5 class="fw-bold mb-1">1. Configuration du compte</h5>
                    <p class="text-muted small mb-3">Copiez les informations fournies par Meta. Vous pouvez demander de l’aide à la personne qui a configuré WhatsApp Business.</p>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label" for="whatsapp_test_mode">Mode de fonctionnement</label>
                            <select id="whatsapp_test_mode" class="form-select">
                                <option value="1">Test — aucun vrai message</option>
                                <option value="0">Production — envoi réel</option>
                            </select>
                            <div id="whatsapp-mode-status" class="form-text" role="status">Chargement du mode enregistré…</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="whatsapp_business_phone_number">Numéro WhatsApp de l’établissement</label>
                            <input id="whatsapp_business_phone_number" class="form-control" placeholder="+237...">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="whatsapp_phone_number_id">Identifiant du numéro (fourni par Meta)</label>
                            <input id="whatsapp_phone_number_id" class="form-control" placeholder="123456789012345">
                            <div class="form-text">Ce n’est pas le numéro de téléphone. Copiez le « Phone Number ID » du compte Meta.</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="whatsapp_access_token">Jeton Meta</label>
                            <input id="whatsapp_access_token" type="password" class="form-control" autocomplete="new-password" placeholder="Collez le jeton fourni par Meta">
                            <div id="whatsapp-token-status" class="form-text">État du jeton inconnu.</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="whatsapp_template_name">Nom du modèle approuvé par Meta</label>
                            <input id="whatsapp_template_name" class="form-control" value="notemaster_notification">
                            <div class="form-text">Utilisé pour les notifications envoyées hors d’une conversation récente.</div>
                        </div>
                    </div>
                    <details class="mt-3">
                        <summary class="text-primary">Paramètres avancés (à laisser tels quels si vous n’êtes pas accompagné)</summary>
                        <div class="row g-3 mt-1">
                            <div class="col-md-6">
                                <label class="form-label" for="whatsapp_api_base_url">Adresse API</label>
                                <input id="whatsapp_api_base_url" class="form-control" value="https://graph.facebook.com/v20.0">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label" for="whatsapp_webhook_verify_token">Jeton de validation webhook</label>
                                <input id="whatsapp_webhook_verify_token" class="form-control" placeholder="Facultatif">
                            </div>
                        </div>
                    </details>
                    <div class="mt-4 d-flex gap-2 flex-wrap">
                        <button class="btn btn-primary" type="button" id="btn-save-config"><i class="bi bi-save me-1"></i> Enregistrer la configuration</button>
                        <button class="btn btn-outline-secondary" type="button" id="btn-verify-meta"><i class="bi bi-check2-circle me-1"></i> Vérifier le compte</button>
                    </div>
                    <div id="meta-connection-result" class="small mt-3 p-3 rounded-3 bg-light" role="status">Le compte n’a pas encore été vérifié.</div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card shadow-sm border-0 rounded-4">
                <div class="card-body p-4">
                    <h5 class="fw-bold mb-2">État de la configuration</h5>
                    <ul id="setup-checklist" class="list-unstyled small mb-0">
                        <li id="check-token" class="mb-2">○ Jeton Meta</li>
                        <li id="check-phone" class="mb-2">○ Identifiant et numéro</li>
                        <li id="check-meta" class="mb-2">○ Vérification Meta</li>
                        <li id="check-mode">○ Mode test activé</li>
                    </ul>
                    <hr>
                    <h6 class="fw-bold">Test rapide</h6>
                    <label class="form-label" for="test_phone">Ton numéro WhatsApp de réception</label>
                    <input id="test_phone" class="form-control mb-2" placeholder="+237 6 XX XX XX XX">
                    <button class="btn btn-outline-success w-100" type="button" id="btn-send-test">Faire un test</button>
                    <div class="form-text mt-2">En mode test, le résultat est simulé; aucun message réel n’arrive.</div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4 mt-1">
        <div class="col-lg-8">
            <div class="card shadow-sm border-0 rounded-4">
                <div class="card-body p-4">
                    <h5 class="fw-bold mb-1">2. Cibler les élèves</h5>
                    <p class="text-muted small">L’année scolaire active est appliquée automatiquement. Seuls les élèves inscrits dans des classes et types d’enseignement actifs sont proposés.</p>
                    <div class="row g-3 align-items-end">
                        <div class="col-md-6">
                            <label class="form-label" for="target-teaching-type">Type d’enseignement</label>
                            <select id="target-teaching-type" class="form-select">
                                <option value="">Chargement…</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="target-class">Classe</label>
                            <select id="target-class" class="form-select">
                                <option value="0">Toutes les classes actives</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="target-criterion">Critère des destinataires</label>
                            <select id="target-criterion" class="form-select">
                                <option value="all">Tous les élèves inscrits</option>
                                <option value="unpaid_1">Reste impayé — tranche 1</option>
                                <option value="unpaid_2">Reste impayé — tranche 2</option>
                                <option value="unpaid_3">Reste impayé — tranche 3</option>
                                <option value="bulletin_available">Au moins une note saisie (bulletin alimenté)</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="target-search">Recherche facultative</label>
                            <input id="target-search" class="form-control" placeholder="Nom, prénom ou matricule/ID">
                        </div>
                    </div>
                    <div id="target-filter-note" class="form-text mt-2">Les résultats seront vérifiés sur le serveur avant tout envoi.</div>
                    <button class="btn btn-outline-primary mt-3" type="button" id="btn-search-targets">Afficher les élèves correspondants</button>
                    <div id="target-results" class="mt-3"></div>
                    <div class="small fw-semibold mt-3">Élèves sélectionnés : <span id="selected-count">0</span></div>
                    <div id="selected-students" class="d-flex flex-wrap gap-2 mb-3"></div>
                    <input type="hidden" id="student_ids">
                    <button class="btn btn-outline-primary" type="button" id="btn-preview" disabled>Vérifier les contacts et consentements</button>
                    <div id="preview-result" class="mt-4 small text-muted"></div>
                    <hr class="my-4">
                    <h5 class="fw-bold mb-1">3. Envoyer la notification</h5>
                    <p class="small text-muted">Seuls les numéros marqués « Autorisé » peuvent recevoir un message.</p>
                    <div class="mb-3">
                        <label class="form-label" for="bulk_template_variables">Personnaliser le message (variables du modèle)</label>
                        <textarea id="bulk_template_variables" class="form-control" rows="3" placeholder="Une valeur par ligne, dans l’ordre des variables {{1}}, {{2}}, etc."></textarea>
                        <div class="form-text">Le texte fixe doit déjà être approuvé par Meta. Chaque ligne remplit une variable du modèle; sans variable, laissez vide.</div>
                    </div>
                    <button class="btn btn-warning" type="button" id="btn-send-bulk" disabled>Envoyer aux contacts autorisés</button>
                    <div id="bulk-result" class="mt-3 small"></div>
                </div>
            </div>

            <div class="card shadow-sm border-0 rounded-4 mt-4">
                <div class="card-body p-4">
                    <details>
                        <summary class="fw-bold">Gestion avancée des consentements</summary>
                        <p class="text-muted small mt-2">Pour un usage normal, utilisez le bouton « Autoriser ce contact » dans la liste ci-dessus.</p>
                        <div class="row g-3 mt-1">
                            <div class="col-md-4">
                                <label class="form-label">ID élève</label>
                                <input id="consent_student_id" class="form-control" placeholder="1">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Téléphone</label>
                                <input id="consent_phone" class="form-control" placeholder="+2376...">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Statut</label>
                                <select id="consent_status" class="form-select">
                                    <option value="granted">Autorisé</option>
                                    <option value="denied">Refusé</option>
                                    <option value="unknown">Inconnu</option>
                                </select>
                            </div>
                        </div>
                        <div class="mt-3">
                            <button class="btn btn-outline-success" type="button" id="btn-save-consent">Enregistrer le consentement</button>
                        </div>
                    </details>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card shadow-sm border-0 rounded-4">
                <div class="card-body p-4">
                    <h5 class="fw-bold mb-3">Journal</h5>
                    <div id="whatsapp-log-list" class="small text-muted">Chargement...</div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
window.WHATSAPP_CSRF_TOKEN = <?= json_encode(\App\Core\Session::generateCsrfToken(), JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;

function apiFetch(url, options = {}) {
    return new Promise((resolve, reject) => {
        const xhr = new XMLHttpRequest();
        xhr.open(options.method || 'GET', url, true);
        xhr.setRequestHeader('Accept', 'application/json');
        xhr.setRequestHeader('X-CSRF-Token', window.WHATSAPP_CSRF_TOKEN);
        if (!(options.body instanceof FormData)) {
            xhr.setRequestHeader('Content-Type', 'application/json');
        }
        xhr.onreadystatechange = function () {
            if (xhr.readyState !== 4) return;
            const contentType = xhr.getResponseHeader('Content-Type') || '';
            const redirectedToLogin = /\/login(?:[/?#]|$)/i.test(xhr.responseURL || '');
            if (redirectedToLogin || !contentType.toLowerCase().includes('application/json')) {
                reject(new Error(
                    redirectedToLogin
                        ? 'Votre session a expiré. Reconnectez-vous, puis rechargez la page WhatsApp.'
                        : 'Le serveur a renvoyé une page HTML au lieu des données attendues. Rechargez la page et reconnectez-vous si nécessaire.'
                ));
                return;
            }

            let data;
            try {
                data = xhr.responseText ? JSON.parse(xhr.responseText) : {};
            } catch (e) {
                reject(new Error('La réponse du serveur est invalide. Rechargez la page puis réessayez.'));
                return;
            }
            if (xhr.status >= 200 && xhr.status < 300) {
                resolve(data);
            } else {
                reject(new Error((data && data.message) || 'Erreur serveur (HTTP ' + xhr.status + ').'));
            }
        };
        xhr.send(options.body || null);
    });
}

function setFieldValue(key, value) {
    const el = document.getElementById(key);
    if (el) {
        el.value = value ?? '';
    }
}

const selectedStudents = new Map();
let targetTypes = [];
let lastMetaVerified = false;
let eligibleContactCount = 0;
let savedTestMode = null;
let loadingConfiguration = false;
let modeSaveInProgress = false;
let modeSaveError = '';

function updateSelectedStudents() {
    eligibleContactCount = 0;
    const selected = document.getElementById('selected-students');
    selected.replaceChildren();
    selectedStudents.forEach(student => {
        const chip = document.createElement('span');
        chip.className = 'badge text-bg-primary d-inline-flex align-items-center gap-2 py-2 px-3';
        chip.append(document.createTextNode(student.name));
        const remove = document.createElement('button');
        remove.type = 'button';
        remove.className = 'btn-close btn-close-white';
        remove.setAttribute('aria-label', 'Retirer ' + student.name);
        remove.addEventListener('click', () => {
            selectedStudents.delete(student.id);
            eligibleContactCount = 0;
            updateSelectedStudents();
            document.getElementById('preview-result').replaceChildren();
            document.getElementById('btn-preview').disabled = selectedStudents.size === 0;
            updateChecklist();
        });
        chip.appendChild(remove);
        selected.appendChild(chip);
    });
    document.getElementById('student_ids').value = Array.from(selectedStudents.keys()).join(',');
    document.getElementById('selected-count').textContent = String(selectedStudents.size);
    document.getElementById('btn-preview').disabled = selectedStudents.size === 0;
    updateChecklist();
}

function currentTargetFilters() {
    return {
        teaching_type_id: Number(document.getElementById('target-teaching-type').value),
        class_id: Number(document.getElementById('target-class').value),
        criterion: document.getElementById('target-criterion').value
    };
}

function clearTargetSelection() {
    selectedStudents.clear();
    eligibleContactCount = 0;
    updateSelectedStudents();
    document.getElementById('preview-result').replaceChildren();
    document.getElementById('target-results').replaceChildren();
}

function updateTargetClasses() {
    const typeId = Number(document.getElementById('target-teaching-type').value);
    const type = targetTypes.find(item => Number(item.id) === typeId);
    const classSelect = document.getElementById('target-class');
    classSelect.replaceChildren();
    const allClasses = document.createElement('option');
    allClasses.value = '0';
    allClasses.textContent = 'Toutes les classes actives';
    classSelect.appendChild(allClasses);
    (type ? type.classes : []).forEach(classItem => {
        const option = document.createElement('option');
        option.value = String(classItem.id);
        option.textContent = classItem.nom;
        classSelect.appendChild(option);
    });
}

async function loadTargetOptions() {
    try {
        const response = await apiFetch('/api/whatsapp/filters');
        targetTypes = response.teaching_types || [];
        const typeSelect = document.getElementById('target-teaching-type');
        typeSelect.replaceChildren();
        if (targetTypes.length === 0) {
            const option = document.createElement('option');
            option.value = '';
            option.textContent = 'Aucun type avec des inscrits actifs';
            typeSelect.appendChild(option);
            return;
        }
        targetTypes.forEach(type => {
            const option = document.createElement('option');
            option.value = String(type.id);
            option.textContent = type.nom;
            typeSelect.appendChild(option);
        });
        updateTargetClasses();
    } catch (e) {
        document.getElementById('target-filter-note').textContent = e.message || 'Impossible de charger les filtres.';
    }
}

async function searchTargets() {
    const results = document.getElementById('target-results');
    results.replaceChildren();
    if (!currentTargetFilters().teaching_type_id) {
        results.textContent = 'Choisis un type d’enseignement actif.';
        return;
    }

    try {
        const response = await apiFetch('/api/whatsapp/targets', {
            method: 'POST',
            body: JSON.stringify({
                filters: currentTargetFilters(),
                query: document.getElementById('target-search').value.trim()
            })
        });
        const heading = document.createElement('div');
        heading.className = 'small text-muted mb-2';
        heading.textContent = response.count + ' élève(s) correspondent aux filtres.'
            + (response.truncated ? ' Affichage limité aux 500 premiers; réduis le ciblage pour un lot plus petit.' : '');
        results.appendChild(heading);
        if (!response.data || response.data.length === 0) {
            const empty = document.createElement('div');
            empty.className = 'alert alert-secondary mb-0';
            empty.textContent = 'Aucun élève ne correspond à ces filtres.';
            results.appendChild(empty);
            return;
        }
        const list = document.createElement('div');
        list.className = 'list-group';
        response.data.forEach(student => {
            const label = document.createElement('label');
            label.className = 'list-group-item d-flex align-items-center gap-2';
            const checkbox = document.createElement('input');
            checkbox.type = 'checkbox';
            checkbox.className = 'form-check-input mt-0';
            checkbox.checked = selectedStudents.has(Number(student.id));
            checkbox.addEventListener('change', () => {
                const id = Number(student.id);
                if (checkbox.checked) {
                    if (selectedStudents.size >= 500) {
                        checkbox.checked = false;
                        alert('La sélection est limitée à 500 élèves par envoi.');
                        return;
                    }
                    selectedStudents.set(id, {
                        id,
                        name: student.prenom + ' ' + student.nom + ' — ' + student.class_name
                    });
                } else {
                    selectedStudents.delete(id);
                }
                updateSelectedStudents();
                document.getElementById('preview-result').replaceChildren();
            });
            const text = document.createElement('span');
            text.textContent = student.prenom + ' ' + student.nom + ' — ' + student.class_name;
            label.append(checkbox, text);
            list.appendChild(label);
        });
        results.appendChild(list);
    } catch (e) {
        const error = document.createElement('div');
        error.className = 'alert alert-danger mb-0';
        error.textContent = e.message || 'Recherche impossible.';
        results.appendChild(error);
    }
}

document.getElementById('btn-search-targets').addEventListener('click', searchTargets);
document.getElementById('target-search').addEventListener('keydown', event => {
    if (event.key === 'Enter') {
        event.preventDefault();
        searchTargets();
    }
});
document.getElementById('target-teaching-type').addEventListener('change', () => {
    updateTargetClasses();
    clearTargetSelection();
});
document.getElementById('target-class').addEventListener('change', clearTargetSelection);
document.getElementById('target-criterion').addEventListener('change', clearTargetSelection);

function updateChecklist() {
    const selectedTestMode = document.getElementById('whatsapp_test_mode').value;
    const modeUnsaved = savedTestMode !== null && selectedTestMode !== savedTestMode;
    const config = {
        token: document.getElementById('whatsapp-token-status').dataset.configured === 'true',
        phone: !!document.getElementById('whatsapp_phone_number_id').value.trim()
            && !!document.getElementById('whatsapp_business_phone_number').value.trim(),
        meta: lastMetaVerified,
        test: selectedTestMode === '1'
    };
    const rows = [
        ['check-token', config.token, 'Jeton Meta enregistré'],
        ['check-phone', config.phone, 'Numéro et identifiant renseignés'],
        ['check-meta', config.meta, 'Compte Meta vérifié'],
        ['check-mode', config.test, config.test ? 'Mode test activé (sans envoi réel)' : 'Mode production activé']
    ];
    rows.forEach(([id, complete, label]) => {
        const row = document.getElementById(id);
        const unsaved = id === 'check-mode' && modeUnsaved;
        const modeSaved = id === 'check-mode' && !modeUnsaved;
        row.textContent = (unsaved ? '○ ' : (complete || modeSaved ? '✓ ' : '○ '))
            + (unsaved ? label + ' — enregistrement en cours/requis' : label);
        row.className = unsaved ? 'mb-2 text-warning' : (complete || modeSaved ? 'mb-2 text-success' : 'mb-2 text-muted');
    });
    const modeStatus = document.getElementById('whatsapp-mode-status');
    modeStatus.textContent = modeSaveError
        || (modeUnsaved
            ? 'Enregistrement du nouveau mode…'
            : config.test
                ? 'Mode test enregistré. Aucun message réel ne sera transmis.'
                : 'Mode production enregistré. Les envois seront réels.');
    modeStatus.className = modeSaveError ? 'form-text text-danger' : 'form-text';
    document.getElementById('btn-send-bulk').disabled =
        selectedStudents.size === 0
        || eligibleContactCount === 0
        || modeUnsaved
        || modeSaveInProgress
        || (!config.test && !config.meta);
}

async function loadConfig() {
    try {
        const result = await apiFetch('/api/whatsapp/config');
        const data = result && result.data ? result.data : {};
        loadingConfiguration = true;
        setFieldValue('whatsapp_test_mode', data.whatsapp_test_mode ?? '1');
        savedTestMode = document.getElementById('whatsapp_test_mode').value;
        setFieldValue('whatsapp_business_phone_number', data.whatsapp_business_phone_number ?? '');
        setFieldValue('whatsapp_phone_number_id', data.whatsapp_phone_number_id ?? '');
        setFieldValue('whatsapp_api_base_url', data.whatsapp_api_base_url ?? 'https://graph.facebook.com/v20.0');
        setFieldValue('whatsapp_template_name', data.whatsapp_template_name ?? 'notemaster_notification');
        setFieldValue('whatsapp_webhook_verify_token', data.whatsapp_webhook_verify_token ?? '');
        const tokenStatus = document.getElementById('whatsapp-token-status');
        tokenStatus.textContent = data.whatsapp_token_configured
            ? 'Un jeton est enregistré. Il ne sera pas affiché.'
            : 'Aucun jeton enregistré.';
        tokenStatus.dataset.configured = data.whatsapp_token_configured ? 'true' : 'false';
        loadingConfiguration = false;
        updateChecklist();
    } catch (e) {
        loadingConfiguration = false;
        console.error('loadConfig error', e);
        document.getElementById('whatsapp-mode-status').textContent = e.message || 'Impossible de lire le mode enregistré.';
        document.getElementById('whatsapp-mode-status').className = 'form-text text-danger';
    }
}

async function saveWhatsAppConfig(modeOnly = false) {
    modeSaveError = '';
    const payload = modeOnly ? {
        whatsapp_test_mode: document.getElementById('whatsapp_test_mode').value
    } : {
        whatsapp_test_mode: document.getElementById('whatsapp_test_mode').value,
        whatsapp_business_phone_number: document.getElementById('whatsapp_business_phone_number').value,
        whatsapp_phone_number_id: document.getElementById('whatsapp_phone_number_id').value,
        whatsapp_access_token: document.getElementById('whatsapp_access_token').value,
        whatsapp_api_base_url: document.getElementById('whatsapp_api_base_url').value,
        whatsapp_template_name: document.getElementById('whatsapp_template_name').value,
        whatsapp_webhook_verify_token: document.getElementById('whatsapp_webhook_verify_token').value,
    };

    try {
        const result = await apiFetch('/api/whatsapp/config', {
            method: 'POST',
            body: JSON.stringify(payload)
        });
        if (!modeOnly) {
            document.getElementById('whatsapp_access_token').value = '';
        }
        savedTestMode = document.getElementById('whatsapp_test_mode').value;
        await loadConfig();
        document.getElementById('whatsapp-mode-status').textContent = result.message
            || (savedTestMode === '1' ? 'Mode test enregistré.' : 'Mode production enregistré.');
        return true;
    } catch (e) {
        modeSaveError = e.message || 'Enregistrement impossible.';
        updateChecklist();
        return false;
    }
}

document.getElementById('btn-save-config').addEventListener('click', async () => {
    const saved = await saveWhatsAppConfig();
    if (saved) {
        alert('Configuration WhatsApp enregistrée.');
    }
});

document.getElementById('whatsapp_test_mode').addEventListener('change', async event => {
    if (loadingConfiguration || savedTestMode === event.target.value) {
        updateChecklist();
        return;
    }

    if (event.target.value === '0' && !window.confirm(
        'Le mode production transmettra de vrais messages WhatsApp et peut entraîner des frais Meta. Confirmer le passage en production ?'
    )) {
        event.target.value = savedTestMode || '1';
        updateChecklist();
        return;
    }

    modeSaveInProgress = true;
    updateChecklist();
    const saved = await saveWhatsAppConfig(true);
    modeSaveInProgress = false;
    if (!saved) {
        event.target.value = savedTestMode || '1';
    }
    updateChecklist();
});

document.getElementById('btn-verify-meta').addEventListener('click', async () => {
    const resultElement = document.getElementById('meta-connection-result');
    resultElement.textContent = 'Vérification en cours…';
    resultElement.className = 'small mt-3 text-muted';
    try {
        const result = await apiFetch('/api/whatsapp/verify', {method: 'POST', body: JSON.stringify({})});
        const data = result.data || {};
        lastMetaVerified = data.phone_number_matches === true;
        resultElement.textContent = result.message
            + (data.display_phone_number ? ' — Numéro Meta : ' + data.display_phone_number : '')
            + (data.verified_name ? ' (' + data.verified_name + ')' : '')
            + (!data.phone_number_matches ? ' — Corrige le numéro configuré avant tout envoi réel.' : '');
        resultElement.className = 'small mt-3 ' + (data.phone_number_matches ? 'text-success' : 'text-warning');
        updateChecklist();
    } catch (e) {
        lastMetaVerified = false;
        resultElement.textContent = e.message || 'Vérification Meta échouée.';
        resultElement.className = 'small mt-3 text-danger';
        updateChecklist();
    }
});

document.getElementById('btn-send-test').addEventListener('click', async () => {
    const phone = document.getElementById('test_phone').value.trim();
    if (!phone) {
        alert('Saisis ton numéro WhatsApp pour recevoir le test.');
        return;
    }
    try {
        const result = await apiFetch('/api/whatsapp/test', {
            method: 'POST',
            body: JSON.stringify({
                phone,
                message: 'Test de configuration WhatsApp NotesMaster.'
            })
        });
        alert(result.message || 'OK');
    } catch (e) {
        alert(e.message || 'Erreur');
    }
});

document.getElementById('btn-preview').addEventListener('click', async () => {
    const ids = Array.from(selectedStudents.keys());
    if (ids.length === 0) {
        document.getElementById('preview-result').textContent = 'Affiche les élèves correspondants puis sélectionne-en au moins un.';
        return;
    }
    try {
        const result = await apiFetch('/api/whatsapp/preview', {
            method: 'POST',
            body: JSON.stringify({ student_ids: ids, filters: currentTargetFilters() })
        });

        const preview = document.getElementById('preview-result');
        if (!result.success) {
            eligibleContactCount = 0;
            updateChecklist();
            preview.replaceChildren();
            const error = document.createElement('div');
            error.className = 'alert alert-danger mb-0';
            error.textContent = result.message || 'Erreur';
            preview.appendChild(error);
            return;
        }

        eligibleContactCount = result.eligible_count || 0;
        updateChecklist();
        preview.replaceChildren();
        const summary = document.createElement('div');
        summary.className = 'alert alert-info mb-0';
        summary.textContent = result.count + ' contact(s) trouvé(s), dont ' + (result.eligible_count || 0) + ' autorisé(s).';
        preview.appendChild(summary);
        const list = document.createElement('ul');
        list.className = 'list-group mt-3';
        (result.data || []).forEach(item => {
            const entry = document.createElement('li');
            entry.className = 'list-group-item d-flex justify-content-between align-items-center gap-2 flex-wrap';
            const description = document.createElement('span');
            description.textContent = item.student_name + ' — ' + item.phone + ' — consentement : ' + (item.consent_status || 'inconnu');
            entry.appendChild(description);
            const consentButton = document.createElement('button');
            consentButton.type = 'button';
            consentButton.className = item.consent_status === 'granted'
                ? 'btn btn-sm btn-outline-danger'
                : 'btn btn-sm btn-outline-success';
            consentButton.textContent = item.consent_status === 'granted'
                ? 'Retirer l’autorisation'
                : 'Autoriser ce contact';
            consentButton.addEventListener('click', async () => {
                const newStatus = item.consent_status === 'granted' ? 'revoked' : 'granted';
                try {
                    await apiFetch('/api/whatsapp/consent', {
                        method: 'POST',
                        body: JSON.stringify({
                            student_id: item.student_id,
                            phone: item.phone,
                            status: newStatus,
                            source: 'manual_ui'
                        })
                    });
                    document.getElementById('btn-preview').click();
                } catch (e) {
                    alert(e.message || 'Impossible de modifier le consentement.');
                }
            });
            entry.appendChild(consentButton);
            list.appendChild(entry);
        });
        preview.appendChild(list);
    } catch (e) {
        const preview = document.getElementById('preview-result');
        preview.replaceChildren();
        const error = document.createElement('div');
        error.className = 'alert alert-danger mb-0';
        error.textContent = e.message || 'Erreur';
        preview.appendChild(error);
    }
});

document.getElementById('btn-send-bulk').addEventListener('click', async () => {
    const ids = Array.from(selectedStudents.keys());
    const templateName = document.getElementById('whatsapp_template_name').value.trim();
    const variablesText = document.getElementById('bulk_template_variables').value.trim();
    const resultElement = document.getElementById('bulk-result');
    if (ids.length === 0 || templateName === '') {
        resultElement.textContent = 'Sélectionne des élèves et vérifie le nom du modèle Meta approuvé.';
        return;
    }
    if (!window.confirm('Confirmer l’envoi du modèle Meta approuvé à ' + eligibleContactCount + ' contact(s) autorisé(s) ?')) {
        return;
    }

    try {
        let components = [];
        if (variablesText !== '') {
            const values = variablesText.split(/\r?\n/).map(value => value.trim());
            if (values.some(value => value === '')) {
                throw new Error('Chaque variable du modèle doit avoir une valeur.');
            }
            if (values.length > 10) {
                throw new Error('Le modèle est limité à 10 variables par envoi.');
            }
            components = [{
                type: 'body',
                parameters: values.map(text => ({ type: 'text', text }))
            }];
        }
        const result = await apiFetch('/api/whatsapp/send', {
            method: 'POST',
            body: JSON.stringify({
                student_ids: ids,
                filters: currentTargetFilters(),
                template_name: templateName,
                components
            })
        });
        resultElement.replaceChildren();
        const summary = document.createElement('div');
        summary.className = 'alert ' + (result.data?.failed ? 'alert-warning' : 'alert-success') + ' mb-2';
        summary.textContent = result.message || 'Traitement terminé.';
        resultElement.appendChild(summary);
        const failures = (result.data?.items || []).filter(item => !item.success);
        if (failures.length > 0) {
            const explanation = document.createElement('p');
            explanation.className = 'mb-2';
            explanation.textContent = 'Détail des échecs :';
            resultElement.appendChild(explanation);
            const list = document.createElement('ul');
            list.className = 'list-group';
            failures.forEach(item => {
                const entry = document.createElement('li');
                entry.className = 'list-group-item';
                const reason = String(item.reason || 'Erreur inconnue');
                const knownMessages = {
                    consent_unknown: 'consentement non enregistré pour ce numéro',
                    consent_denied: 'consentement refusé pour ce numéro',
                    consent_revoked: 'consentement retiré pour ce numéro',
                    invalid_recipient: 'numéro ou élève invalide',
                    whatsapp_configuration_incomplete: 'identifiants Meta incomplets'
                };
                entry.textContent = (item.to || 'Numéro inconnu') + ' — '
                    + (knownMessages[reason] || reason)
                    + (item.error_code ? ' (code Meta ' + item.error_code
                        + (item.error_subcode ? '/' + item.error_subcode : '') + ')' : '')
                    + (item.status ? ' — HTTP ' + item.status : '');
                list.appendChild(entry);
            });
            resultElement.appendChild(list);
        }
        await loadLogs();
    } catch (e) {
        resultElement.textContent = e.message || 'Erreur lors de l’envoi.';
        resultElement.className = 'mt-3 small text-danger';
    }
});

document.getElementById('btn-save-consent').addEventListener('click', async () => {
    try {
        const result = await apiFetch('/api/whatsapp/consent', {
            method: 'POST',
            body: JSON.stringify({
                student_id: Number(document.getElementById('consent_student_id').value),
                phone: document.getElementById('consent_phone').value,
                status: document.getElementById('consent_status').value,
                source: 'manual_ui'
            })
        });
        alert(result.message || 'Consentement enregistré');
    } catch (e) {
        alert(e.message || 'Erreur');
    }
});

async function loadLogs() {
    try {
        const result = await apiFetch('/api/whatsapp/logs?limit=10');
        const list = document.getElementById('whatsapp-log-list');
        if (!result.success) {
            list.innerHTML = 'Impossible de charger les logs.';
            return;
        }
        if (!result.data || result.data.length === 0) {
            list.innerHTML = '<div class="text-muted">Aucun journal pour le moment.</div>';
            return;
        }
        list.replaceChildren();
        (result.data || []).slice(0, 10).forEach(row => {
            const entry = document.createElement('div');
            entry.className = 'border-bottom py-2';
            entry.textContent = (row.message_type || 'unknown') + ' — ' + (row.phone_number || '');
            list.appendChild(entry);
        });
    } catch (e) {
        document.getElementById('whatsapp-log-list').innerHTML = 'Impossible de charger les logs.';
    }
}

document.getElementById('btn-refresh-preview').addEventListener('click', loadLogs);
document.getElementById('whatsapp_phone_number_id').addEventListener('input', () => { lastMetaVerified = false; updateChecklist(); });
document.getElementById('whatsapp_business_phone_number').addEventListener('input', () => { lastMetaVerified = false; updateChecklist(); });
loadConfig();
loadTargetOptions();
loadLogs();
</script>
    <?php endif; ?>
<?php
$content = ob_get_clean();
include __DIR__ . '/../templates/layout.php';
?>
