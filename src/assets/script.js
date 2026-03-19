/**
 * Script JavaScript pour la gestion des factures et devis
 */

document.addEventListener('DOMContentLoaded', function() {
    // ============================================
    // GESTION DES LIGNES (Factures & Devis)
    // ============================================
    const addLineBtn = document.getElementById('addLine');
    const linesTable = document.querySelector('#linesTable tbody');
    const calcInputs = document.querySelectorAll('.calc-input, .qty, .price');
    
    // ============================================
    // RECHERCHE CLIENT
    // ============================================
    const clientSearch = document.getElementById('clientSearch');
    const searchResults = document.getElementById('searchResults');
    const clientId = document.getElementById('clientId');
    const selectedClientBox = document.getElementById('selectedClientBox');
    const selectedClientName = document.getElementById('selectedClientName');
    const selectedClientModel = document.getElementById('selectedClientModel');
    
    // Initialiser la recherche client si l'élément existe
    if (clientSearch) {
        initClientSearch();
    }
    
    // Initialiser le tableau des lignes si l'élément existe
    if (addLineBtn) {
        initLinesTable();
    }
    
    // ============================================
    // FONCTION: Initialiser la recherche client
    // ============================================
    function initClientSearch() {
        let debounceTimer;
        
        clientSearch.addEventListener('input', function() {
            clearTimeout(debounceTimer);
            const query = this.value.trim();
            
            if (query.length < 2) {
                if (searchResults) searchResults.style.display = 'none';
                return;
            }
            
            debounceTimer = setTimeout(function() {
                // Déterminer la route de recherche selon la page actuelle
                const isDevis = window.location.search.includes('route=devis');
                const searchRoute = isDevis ? 'devis_search_clients' : 'search_clients';
                
                fetch('index.php?route=' + searchRoute + '&q=' + encodeURIComponent(query))
                    .then(response => {
                        if (!response.ok) throw new Error('Erreur réseau');
                        return response.json();
                    })
                    .then(data => {
                        displaySearchResults(data);
                    })
                    .catch(error => {
                        console.error('Erreur de recherche:', error);
                        if (searchResults) {
                            searchResults.innerHTML = '<div class="search-result-item" style="color: red;">Erreur de recherche</div>';
                            searchResults.style.display = 'block';
                        }
                    });
            }, 300);
        });
        
        // Gestion du focus
        clientSearch.addEventListener('focus', function() {
            if (this.value.length >= 2 && searchResults && searchResults.children.length > 0) {
                searchResults.style.display = 'block';
            }
        });
        
        // Fermer les résultats au clic en dehors
        document.addEventListener('click', function(e) {
            if (searchResults && !e.target.closest('.search-container')) {
                searchResults.style.display = 'none';
            }
        });
    }
    
    // ============================================
    // FONCTION: Afficher les résultats de recherche
    // ============================================
    function displaySearchResults(clients) {
        if (!searchResults) return;
        
        searchResults.innerHTML = '';
        
        if (!clients || clients.length === 0) {
            searchResults.innerHTML = '<div class="search-result-item">Aucun client trouvé</div>';
            searchResults.style.display = 'block';
            return;
        }
        
        clients.forEach(client => {
            const item = document.createElement('div');
            item.className = 'search-result-item';
            
            let html = '<strong>' + escapeHtml(client.name) + '</strong>';
            if (client.car_model) {
                html += '<small>Modèle: ' + escapeHtml(client.car_model) + '</small>';
            }
            if (client.phone) {
                html += '<small>Tél: ' + escapeHtml(client.phone) + '</small>';
            }
            
            item.innerHTML = html;
            
            item.addEventListener('click', function() {
                selectClient(client);
            });
            
            searchResults.appendChild(item);
        });
        
        searchResults.style.display = 'block';
    }
    
    // ============================================
    // FONCTION: Sélectionner un client
    // ============================================
    function selectClient(client) {
        if (!clientId || !clientSearch) return;
        
        clientId.value = client.id;
        clientSearch.value = client.name;
        
        if (selectedClientName) {
            selectedClientName.textContent = client.name;
        }
        if (selectedClientModel) {
            selectedClientModel.textContent = client.car_model ? ' - ' + client.car_model : '';
        }
        if (selectedClientBox) {
            selectedClientBox.classList.add('show');
        }
        if (searchResults) {
            searchResults.style.display = 'none';
        }
    }
    
    // ============================================
    // FONCTION: Initialiser le tableau des lignes
    // ============================================
    function initLinesTable() {
        // Ajouter une ligne
        addLineBtn.addEventListener('click', function() {
            const firstRow = document.querySelector('.line-row');
            if (!firstRow) return;
            
            const row = firstRow.cloneNode(true);
            const inputs = row.querySelectorAll('input');
            inputs.forEach(input => input.value = '');
            
            const select = row.querySelector('select');
            if (select) {
                select.selectedIndex = 0;
            }
            
            const lineTotal = row.querySelector('.line-total');
            if (lineTotal) {
                lineTotal.textContent = '0.000';
            }
            
            linesTable.appendChild(row);
            updateTotals();
        });
        
        // Supprimer une ligne
        linesTable.addEventListener('click', function(e) {
            if (e.target.classList.contains('remove-line')) {
                const rows = document.querySelectorAll('.line-row');
                if (rows.length > 1) {
                    e.target.closest('.line-row').remove();
                    updateTotals();
                } else {
                    alert('Vous devez avoir au moins une ligne');
                }
            }
        });
        
        // Changement dans les lignes
        linesTable.addEventListener('change', function(e) {
            if (e.target.tagName === 'SELECT') {
                const option = e.target.options[e.target.selectedIndex];
                const priceInput = e.target.closest('tr').querySelector('.price');
                if (option && option.dataset.price && priceInput) {
                    priceInput.value = option.dataset.price;
                }
                updateLineTotal(e.target.closest('tr'));
            }
            if (e.target.classList.contains('qty') || e.target.classList.contains('price')) {
                updateLineTotal(e.target.closest('tr'));
            }
        });
        
        // Calcul des totaux
        calcInputs.forEach(input => {
            input.addEventListener('input', updateTotals);
        });
    }
    
    // ============================================
    // FONCTION: Mettre à jour le total d'une ligne
    // ============================================
    function updateLineTotal(row) {
        if (!row) return;
        
        const qtyInput = row.querySelector('.qty');
        const priceInput = row.querySelector('.price');
        const lineTotalSpan = row.querySelector('.line-total');
        
        if (!qtyInput || !priceInput || !lineTotalSpan) return;
        
        const qty = parseFloat(qtyInput.value) || 0;
        const price = parseFloat(priceInput.value) || 0;
        const total = qty * price;
        
        lineTotalSpan.textContent = total.toFixed(3);
        updateTotals();
    }
    
    // ============================================
    // FONCTION: Mettre à jour les totaux généraux
    // ============================================
    function updateTotals() {
        const totalHtSpan = document.getElementById('total_ht');
        const totalTvaSpan = document.getElementById('total_tva');
        const totalTtcSpan = document.getElementById('total_ttc');
        
        if (!totalHtSpan || !totalTvaSpan || !totalTtcSpan) return;
        
        let linesTotal = 0;
        document.querySelectorAll('.line-row').forEach(row => {
            const lineTotalSpan = row.querySelector('.line-total');
            if (lineTotalSpan) {
                const total = parseFloat(lineTotalSpan.textContent) || 0;
                linesTotal += total;
            }
        });
        
        const taxRateInput = document.querySelector('input[name="tax_rate"]');
        const droitTimbreInput = document.querySelector('input[name="droit_timbre"]');
        
        const taxRate = parseFloat(taxRateInput ? taxRateInput.value : 19) || 0;
        const droitTimbre = parseFloat(droitTimbreInput ? droitTimbreInput.value : 0) || 0;
        
        const ht = linesTotal;
        const tva = ht * (taxRate / 100);
        const ttc = ht + tva + droitTimbre;
        
        totalHtSpan.textContent = ht.toFixed(3);
        totalTvaSpan.textContent = tva.toFixed(3);
        totalTtcSpan.textContent = ttc.toFixed(3);
    }
    
    // ============================================
    // FONCTION: Échapper les caractères HTML
    // ============================================
    function escapeHtml(text) {
        if (!text) return '';
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
});

// ============================================
// FONCTION: Effacer la sélection client
// ============================================
function clearClientSelection() {
    const clientId = document.getElementById('clientId');
    const clientSearch = document.getElementById('clientSearch');
    const selectedClientBox = document.getElementById('selectedClientBox');
    
    if (clientId) clientId.value = '';
    if (clientSearch) {
        clientSearch.value = '';
        clientSearch.focus();
    }
    if (selectedClientBox) {
        selectedClientBox.classList.remove('show');
    }
}