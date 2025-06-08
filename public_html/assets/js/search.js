let searchTimeout = null;
const searchInput = document.querySelector('.search-input');
const suggestionsContainer = document.querySelector('.search-suggestions');

searchInput.addEventListener('input', function(e) {
    if (searchTimeout) {
        clearTimeout(searchTimeout);
    }
    
    const query = e.target.value.trim();
    if (query.length < 2) {
        suggestionsContainer.style.display = 'none';
        return;
    }
    
    searchTimeout = setTimeout(() => {
        fetch(`${BASE_URL}/items/api/suggest.php?q=${encodeURIComponent(query)}`)
            .then(response => response.json())
            .then(data => {
                if (data.success && data.suggestions.length > 0) {
                    renderSuggestions(data.suggestions);
                }
            });
    }, 300);
});

function renderSuggestions(suggestions) {
    suggestionsContainer.innerHTML = suggestions
        .map(item => `
            <div class="suggestion-item">
                <a href="${BASE_URL}/items/view.php?id=${item.item_id}">
                    ${item.title}
                    <span class="suggestion-category">${item.category}</span>
                </a>
            </div>
        `).join('');
    suggestionsContainer.style.display = 'block';
}
