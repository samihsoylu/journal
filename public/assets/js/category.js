document.addEventListener('DOMContentLoaded', () => {
    const list = document.getElementById('sortable');
    if (!list) return;
    Sortable.create(list, {
        handle: '.card-icon', animation: 150,
        onEnd: async () => {
            const data = new URLSearchParams({ form_key: list.dataset.formKey });
            Array.from(list.children).forEach((item, index) => {
                data.append('orderedCategoryIds[' + index + ']', item.id);
            });
            try {
                const response = await fetch(BASE_URL + '/categories/ajax/sort-order', {
                    method: 'POST', credentials: 'same-origin', body: data
                });
                if (!response.ok) throw new Error('Category order could not be saved');
            } catch (error) {
                alert('Unable to save this order. Please refresh the page and try again.');
            }
        }
    });
});
