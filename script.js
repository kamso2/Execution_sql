// Theme Logic
function toggleTheme() {
    document.body.classList.toggle('dark-mode');
    const isDark = document.body.classList.contains('dark-mode');
    localStorage.setItem('theme', isDark ? 'dark' : 'light');
    updateThemeIcon();
}

function updateThemeIcon() {
    const btn = document.getElementById('themeBtn');
    if (btn) {
        const isDark = document.body.classList.contains('dark-mode');
        btn.textContent = isDark ? '☀️' : '🌙';
    }
}

// Apply theme on load
if (localStorage.getItem('theme') === 'dark') {
    document.body.classList.add('dark-mode');
}
updateThemeIcon();

// =============================================================================
// 0. AUTHENTIFICATION & PAGINATION & VARIABLES
// =============================================================================
let currentPage = 1;
const itemsPerPage = 50;
let allResults = [];
let currentSort = { column: null, direction: 'asc' };
let csrfToken = '';

window.addEventListener('DOMContentLoaded', () => {
    fetch('check_auth.php')
        .then(r => r.json())
        .then(data => {
            if (!data.authenticated) {
                window.location.href = 'login.html';
            } else {
                document.getElementById('currentUser').textContent = data.user;
                csrfToken = data.csrf_token;
                if (data.role !== 'admin') {
                    const auditLink = document.getElementById('navAuditLink');
                    const usersLink = document.getElementById('navUsersLink');
                    if (auditLink) auditLink.style.display = 'none';
                    if (usersLink) usersLink.style.display = 'none';
                }
                populateQuerySelector();
            }
        })
        .catch(err => {
            console.error('Erreur auth:', err);
            window.location.href = 'login.html';
        });
});

function logout() {
    fetch('logout.php', { method: 'POST' })
        .then(() => window.location.href = 'login.html');
}

// =============================================================================
// 1. CONFIGURATION (FACTORISÉE)
// =============================================================================

// Liste commune des tables pour les dropdowns
const COMMON_TABLE_OPTIONS = [
    { value: "", label: "-- Sélectionnez une table --" },
    { value: "repeteurs", label: "FRAGILE_REPETEUR" },
    { value: "retour_echus", label: "RETOUR_ECHUS" },
    { value: "one_three_months", label: "ECHUS_1_3_MOIS" },
    { value: "welcome_call", label: "WC_QUALIFICATION_RECRUTES" },
    { value: "access_evasion_tc_inactifs", label: "ACCESS_EVASION_TOUT_CANAL_INACTIFS" },
    { value: "echus_3_4_mois", label: "ECHUS_3_4_MOIS" },
    { value: "evasion_actifs", label: "EVASION_ACTIFS" },
    { value: "fragile_retour_echus", label: "FRAGILE_RETOUR_ECHUS_" },
    { value: "g11_bundle", label: "G11_BUNDLE" },
    { value: "insight_plus", label: "INSIGHT_PLUS" },
    { value: "netflix_inactif", label: "NETFLIX_INACTIF" },
    { value: "project_g11", label: "PROJECT_G11" },
    { value: "reconquete_access_evasion", label: "RECONQUETE_ACCESS_EVASION_TC_INACTIFS" },
    { value: "service_plus", label: "SERVICE_PLUS" },
    { value: "ultimate_evasion_plus", label: "ULTIMATE_EVASION_PLUS" },
    { value: "ultimate_toutcanal_plus", label: "ULTIMATE_TOUTCANAL_PLUS" },
    { value: "upgrade_access_evasion", label: "UPGRADE_ACCESS_EVASION_MOMO_RP" },
    { value: "welcome_g11", label: "WELCOME_G11" }
];

const QUERY_MAPPING = {
    "nombre_de_fiches": {
        label: "🔍 NOMBRE DE FICHES",
        dropdowns: [
            { id: "table", label: "Base de données cible (Table)", options: COMMON_TABLE_OPTIONS },
            {
                id: "condition_column",
                label: "Colonne de Filtre (Optionnel)",
                options: [
                    { value: "", label: "-- Aucun filtre --" },
                    { value: "status", label: "STATUS" }
                ]
            },
            {
                id: "value",
                label: "Valeur du Filtre",
                options: [
                    { value: "", label: "-- Sélectionnez --" },
                    { value: "NOT_DIALED", label: "NOT_DIALED" },
                    { value: "COMPLETED", label: "COMPLETED" },
                    { value: "CALLBACK", label: "CALLBACK" },
                    { value: " ", label: "VIDE (Espace)" }
                ]
            },
            {
                id: "date_column",
                label: "Filtre Optionnel (Date)",
                options: [
                    { value: "", label: "-- Aucune date --" },
                    { value: "fin_abonnement", label: "Date fin abonnement" }
                ]
            }
        ],
        text_params: [],
        use_date_range: true
    },
    "RELANCE": {
        label: "⚡ RELANCE (Admin)",
        dropdowns: [
            {
                id: "table",
                label: "Base de données cible (Table)",
                options: COMMON_TABLE_OPTIONS
            },
            {
                id: "value",
                label: "Valeur Actuelle (Filtre WHERE)",
                options: [
                    { value: " ", label: "VIDE (Espace)" },
                    { value: "NOT_DIALED", label: "NOT_DIALED" },
                    { value: "COMPLETED", label: "COMPLETED" },
                    { value: "CALLBACK", label: "CALLBACK" }
                ]
            },
            {
                id: "new_status",
                label: "NOUVEAU STATUT (SET)",
                options: [
                    { value: "CALLBACK", label: "CALLBACK" },
                    { value: "COMPLETED", label: "COMPLETED" },
                    { value: "NOT_DIALED", label: "NOT_DIALED" },
                    { value: " ", label: "VIDE (Espace)" }
                ]
            },
            {
                id: "date_column",
                label: "Filtre Date Optionnel",
                options: [
                    { value: "", label: "-- Aucune date --" },
                    { value: "fin_abonnement", label: "Date fin abonnement" }
                ]
            }
        ],
        text_params: [],
        use_date_range: true
    },
    "AJOUT_DATE": {
        label: "📅 AJOUT DATE (Admin)",
        dropdowns: [
            { id: "table", label: "Base de données cible (Table)", options: COMMON_TABLE_OPTIONS },
            {
                id: "value",
                label: "Valeur Actuelle (Filtre WHERE)",
                options: [
                    { value: " ", label: "VIDE (Espace)" },
                    { value: "NOT_DIALED", label: "NOT_DIALED" },
                    { value: "COMPLETED", label: "COMPLETED" },
                    { value: "CALLBACK", label: "CALLBACK" }
                ]
            },
            {
                id: "date_column",
                label: "Filtre Date",
                options: [
                    { value: "fin_abonnement", label: "Date fin abonnement" }
                ]
            }
        ],
        text_params: [],
        use_date_range: true
    }
};

// =============================================================================
// 2. DOM ELEMENTS
// =============================================================================
const querySelector = document.getElementById('querySelector');
const parameterInputs = document.getElementById('parameterInputs');
const resultsTableContainer = document.getElementById('resultsTableContainer');
const exportCsvBtn = document.getElementById('exportCsvBtn');
const executeBtn = document.getElementById('executeBtn');
const loadingSpinner = document.getElementById('loadingSpinner');
const API_ENDPOINT = 'api_execute.php';

// =============================================================================
// 3. AFFICHAGE FORMULAIRE
// =============================================================================
function populateQuerySelector() {
    querySelector.innerHTML = `<option value="">-- Sélectionnez un rapport --</option>`;
    for (const queryId in QUERY_MAPPING) {
        const option = document.createElement('option');
        option.value = queryId;
        option.textContent = QUERY_MAPPING[queryId].label;
        querySelector.appendChild(option);
    }
    querySelector.addEventListener('change', displayDynamicParameters);
}

function displayDynamicParameters() {
    const selectedQueryId = querySelector.value;
    parameterInputs.innerHTML = '';

    if (!selectedQueryId) return;

    const queryInfo = QUERY_MAPPING[selectedQueryId];

    // Dropdowns
    if (queryInfo.dropdowns) {
        queryInfo.dropdowns.forEach(dd => {
            createFieldWrapper(dd.label, () => {
                const select = document.createElement('select');
                select.id = dd.id;
                select.className = 'form-control';
                dd.options.forEach(opt => {
                    const option = document.createElement('option');
                    // Gérer les objets {value, label} ou les chaînes simples
                    const val = (typeof opt === 'object' && opt !== null) ? opt.value : opt;
                    const lab = (typeof opt === 'object' && opt !== null) ? (opt.label || opt.value) : opt;

                    option.value = val;
                    option.textContent = lab;
                    select.appendChild(option);
                });
                return select;
            });
        });
    }

    // Text Inputs
    if (queryInfo.text_params) {
        queryInfo.text_params.forEach(param => {
            createFieldWrapper(param.label, () => {
                const input = document.createElement('input');
                input.className = 'form-control';
                input.type = 'text';
                input.id = param.name;
                input.placeholder = param.placeholder || 'Entrez une valeur...';
                return input;
            });
        });
    }

    // Date Range (Nouveau)
    if (queryInfo.use_date_range) {
        // Conteneur Flex pour Start et End
        const wrapper = document.createElement('div');
        wrapper.className = 'form-group';
        wrapper.innerHTML = `<label class="form-label">Période (Optionnel)</label>`;

        const flexDiv = document.createElement('div');
        flexDiv.style.display = 'flex';
        flexDiv.style.gap = '10px';

        const startInput = document.createElement('input');
        startInput.type = 'date';
        startInput.id = 'start_date';
        startInput.className = 'form-control';
        startInput.placeholder = 'Du...';

        const endInput = document.createElement('input');
        endInput.type = 'date';
        endInput.id = 'end_date';
        endInput.className = 'form-control';
        endInput.placeholder = 'Au...';

        flexDiv.appendChild(startInput);
        flexDiv.appendChild(endInput);
        wrapper.appendChild(flexDiv);
        parameterInputs.appendChild(wrapper);
    }
}

function createFieldWrapper(labelText, elementCreator) {
    const div = document.createElement('div');
    div.className = 'form-group';

    const label = document.createElement('label');
    label.className = 'form-label';
    label.textContent = labelText;

    const element = elementCreator();

    div.appendChild(label);
    div.appendChild(element);
    parameterInputs.appendChild(div);
}

// =============================================================================
// 4. EXÉCUTION & LOGIQUE
// =============================================================================
async function executeQuery() {
    const selectedQueryId = querySelector.value;
    if (!selectedQueryId) {
        showToast("Veuillez sélectionner une requête.", "error");
        return;
    }

    // UI Loading
    setLoading(true);
    resultsTableContainer.innerHTML = `<div style="text-align:center; padding:2rem; color:#64748b;">Chargement des données...</div>`;
    exportCsvBtn.classList.add('hidden');
    document.getElementById('paginationContainer').classList.add('hidden');

    const queryInfo = QUERY_MAPPING[selectedQueryId];
    const payload = { query_id: selectedQueryId, params: {} };

    // Collect Data form DOM
    // 1. Dropdowns
    if (queryInfo.dropdowns) {
        queryInfo.dropdowns.forEach(dd => {
            const el = document.getElementById(dd.id);
            if (el) {
                // Fix: Ensure we get the string value, not the element object
                const value = el.value || '';
                if (dd.id === 'value') {
                    payload.params['value'] = value;
                } else {
                    payload[dd.id] = value;
                }
            }
        });
    }

    // 2. Text Params
    if (queryInfo.text_params) {
        queryInfo.text_params.forEach(p => {
            const el = document.getElementById(p.name);
            if (el && el.value.trim() !== '') {
                payload.params[p.name] = el.value.trim();
            }
        });
    }

    // 3. Dates
    const startEl = document.getElementById('start_date');
    const endEl = document.getElementById('end_date');
    if (startEl && startEl.value) payload.params['start_date'] = startEl.value;
    if (endEl && endEl.value) payload.params['end_date'] = endEl.value;

    // Validation (Table est toujours requis)
    if (!payload.table || payload.table === '') {
        setLoading(false);
        showToast("Veuillez choisir une table.", "error");
        return;
    }

    // Validation des colonnes uniquement si le champ est présent dans le DOM
    const colEl = document.getElementById('columns');
    if (colEl && (!payload.columns || payload.columns === '')) {
        setLoading(false);
        showToast("Veuillez choisir les colonnes à afficher.", "error");
        return;
    }

    // Clean up empty values to prevent [object Object] errors
    if (payload.condition_column === '') delete payload.condition_column;
    if (payload.date_column === '') delete payload.date_column;
    // Ne supprimer params.value que si condition_column n'est pas défini
    if (!payload.condition_column && payload.params.value === '') {
        delete payload.params.value;
    }

    // Fetch
    try {
        const res = await fetch(API_ENDPOINT, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-Token': csrfToken
            },
            body: JSON.stringify(payload)
        });

        const text = await res.text();
        let data;
        try { data = JSON.parse(text); } catch (e) { data = null; }

        // Nouvelle gestion d'erreur BDD
        if (res.status === 500 && data && data.is_db_error) {
            window.location.href = 'maintenance.html';
            return;
        }

        if (!res.ok || (data && data.error)) {
            throw new Error(data && data.error ? data.error : `Erreur serveur ${res.status}`);
        }

        // Success
        allResults = data.results || [];
        const count = data.count !== undefined ? data.count : allResults.length;
        const queryLabel = queryInfo.label || selectedQueryId;

        let msg = `<b>${queryLabel}</b> : `;
        if (allResults.length > 0) {
            msg += `Succès ! ${count} lignes récupérées.`;
        } else {
            // Cas UPDATE / INSERT / DELETE
            msg += `Succès ! ${count} ligne(s) affectée(s).`;
        }
        showToast(msg, "success");

        // Reset sort
        currentSort = { column: null, direction: 'asc' };

        currentPage = 1;
        displayPageResults();
        updatePaginationControls();

        if (allResults.length > 0) exportCsvBtn.classList.remove('hidden');

    } catch (err) {
        console.error(err);
        showToast(err.message, "error");
        resultsTableContainer.innerHTML = `<div style="text-align:center; padding:2rem; color:#ef4444;">Erreur: ${err.message}</div>`;
    } finally {
        setLoading(false);
    }
}

function setLoading(isLoading) {
    executeBtn.disabled = isLoading;
    if (isLoading) {
        loadingSpinner.classList.remove('hidden');
    } else {
        loadingSpinner.classList.add('hidden');
    }
}

// =============================================================================
// 5. AFFICHAGE & TRI
// =============================================================================
function displayResultsTable(results) {
    resultsTableContainer.innerHTML = '';

    if (!results || results.length === 0) {
        resultsTableContainer.innerHTML = '<div style="padding:1rem; text-align:center;">Aucun résultat.</div>';
        return;
    }

    const table = document.createElement('table');
    const thead = document.createElement('thead');
    const tbody = document.createElement('tbody');
    const headerRow = document.createElement('tr');

    const columns = Object.keys(results[0]);

    columns.forEach(col => {
        const th = document.createElement('th');
        th.textContent = col; // Clé brute

        // Indicateur de tri
        if (currentSort.column === col) {
            th.textContent += currentSort.direction === 'asc' ? ' ▲' : ' ▼';
        }

        th.onclick = () => sortData(col);
        headerRow.appendChild(th);
    });
    thead.appendChild(headerRow);

    results.forEach(row => {
        const tr = document.createElement('tr');
        columns.forEach(col => {
            const td = document.createElement('td');
            td.textContent = row[col] !== null ? row[col] : '';
            tr.appendChild(td);
        });
        tbody.appendChild(tr);
    });

    table.appendChild(thead);
    table.appendChild(tbody);
    resultsTableContainer.appendChild(table);
}

function sortData(column) {
    if (currentSort.column === column) {
        // Toggle direction
        currentSort.direction = currentSort.direction === 'asc' ? 'desc' : 'asc';
    } else {
        // New column
        currentSort.column = column;
        currentSort.direction = 'asc';
    }

    // Tri simple
    allResults.sort((a, b) => {
        let valA = a[column];
        let valB = b[column];

        // Gérer null
        if (valA === null) valA = "";
        if (valB === null) valB = "";

        // Si nombres
        if (!isNaN(valA) && !isNaN(valB) && valA !== "" && valB !== "") {
            valA = Number(valA);
            valB = Number(valB);
        } else {
            // String case insensitive
            valA = String(valA).toLowerCase();
            valB = String(valB).toLowerCase();
        }

        if (valA < valB) return currentSort.direction === 'asc' ? -1 : 1;
        if (valA > valB) return currentSort.direction === 'asc' ? 1 : -1;
        return 0;
    });

    displayPageResults();
}

function displayPageResults() {
    const start = (currentPage - 1) * itemsPerPage;
    const end = start + itemsPerPage;
    const pageResults = allResults.slice(start, end);
    displayResultsTable(pageResults);
}

// =============================================================================
// 6. PAGINATION & CSV & TOAST
// =============================================================================
function updatePaginationControls() {
    const totalPages = Math.ceil(allResults.length / itemsPerPage);
    const container = document.getElementById('paginationContainer');

    if (totalPages > 1) {
        container.classList.remove('hidden');
        document.getElementById('paginationInfo').textContent = `${currentPage} / ${totalPages}`;
        document.getElementById('prevBtn').disabled = currentPage === 1;
        document.getElementById('nextBtn').disabled = currentPage === totalPages;
    } else {
        container.classList.add('hidden');
    }
}

function previousPage() {
    if (currentPage > 1) { currentPage--; displayPageResults(); updatePaginationControls(); }
}
function nextPage() {
    const totalPages = Math.ceil(allResults.length / itemsPerPage);
    if (currentPage < totalPages) { currentPage++; displayPageResults(); updatePaginationControls(); }
}

function downloadCSV() {
    const selectedQueryId = querySelector.value;
    if (!selectedQueryId) return;

    // Création d'un formulaire caché pour envoyer les données en POST
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = 'api_export.php';
    form.style.display = 'none';

    // Helper pour ajouter un input
    const addInput = (name, value) => {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = name;
        input.value = value;
        form.appendChild(input);
    };

    addInput('query_id', selectedQueryId);

    // Paramètres dynamiques (Dropdowns)
    const queryInfo = QUERY_MAPPING[selectedQueryId];
    if (queryInfo.dropdowns) {
        queryInfo.dropdowns.forEach(dd => {
            const el = document.getElementById(dd.id);
            if (el) addInput(dd.id, el.value);
        });
    }

    // Textes
    if (queryInfo.text_params) {
        queryInfo.text_params.forEach(p => {
            const el = document.getElementById(p.name);
            if (el && el.value) addInput(p.name, el.value);
        });
    }

    // Dates
    const startEl = document.getElementById('start_date');
    const endEl = document.getElementById('end_date');
    if (startEl && startEl.value) addInput('start_date', startEl.value);
    if (endEl && endEl.value) addInput('end_date', endEl.value);

    // CSRF (S'assurer que l'export est autorisé)
    // Note: api_export.php ne vérifie pas le CSRF token ici par simplicité (export GET/POST classique),
    // mais pour être propre il faudrait l'ajouter. api_export.php actuel ne le demande pas explicitement.

    document.body.appendChild(form);
    form.submit();
    document.body.removeChild(form);

    showToast("Téléchargement lancé...", "success");
}

function showToast(message, type = 'success') {
    const container = document.getElementById('toastContainer');

    // LIMITATION : Max 5 notifications (Haut en bas = Ancien en haut, Nouveau en bas)
    // On supprime les plus anciens (ceux en haut de la liste) si on dépasse 5
    while (container.children.length >= 5) {
        container.removeChild(container.firstChild);
    }

    const toast = document.createElement('div');
    toast.className = `toast ${type}`;
    toast.innerHTML = `
        <span style="font-size:1.2em;">${type === 'success' ? '✅' : '⚠️'}</span>
        <span>${message}</span>
    `;

    container.appendChild(toast);

    // Auto remove après 10 minutes (600,000 ms)
    setTimeout(() => {
        toast.style.opacity = '0';
        setTimeout(() => {
            if (toast.parentNode) toast.parentNode.removeChild(toast);
        }, 300);
    }, 600000);
}

function resetForm() {
    // 1. Reset Select Principal
    const querySelector = document.getElementById('querySelector');
    if (querySelector) querySelector.value = "";

    // 2. Vider les paramètres dynamiques
    const parameterInputs = document.getElementById('parameterInputs');
    if (parameterInputs) parameterInputs.innerHTML = "";

    // 3. Vider les résultats
    const resultsContainer = document.getElementById('resultsTableContainer');
    if (resultsContainer) resultsContainer.innerHTML = '<div style="text-align: center; color: var(--text-muted); padding: 2rem;">Les résultats s\'afficheront ici...</div>';

    // 4. Masquer pagination et export
    document.getElementById('paginationContainer').classList.add('hidden');
    document.getElementById('exportCsvBtn').classList.add('hidden');

    // 5. Reset
    // Variables Globales
    allResults = [];
    currentPage = 1;

    showToast("Formulaire réinitialisé.", "success");
}