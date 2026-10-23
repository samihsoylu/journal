const titleInput = document.getElementById('entry_title');
const dateInput = document.getElementById('entry_date_time');
let loadedTemplateId = null;
let generatedTitle = null;
let templateRequest = 0;

async function fetchTemplate(templateId) {
    const query = new URLSearchParams({ entry_date_time: dateInput?.value ?? '' });
    const response = await fetch(BASE_URL + '/template/' + encodeURIComponent(templateId) + '/ajax?' + query, {
        credentials: 'same-origin', headers: { Accept: 'application/json' }
    });
    if (!response.ok) throw new Error('Template could not be loaded');
    return response.json();
}

function useDefaultTitle(data) {
    if (typeof data.defaultEntryTitle === 'string' && (!titleInput.value || titleInput.value === generatedTitle)) {
        titleInput.value = data.defaultEntryTitle;
        generatedTitle = data.defaultEntryTitle;
        titleInput.dispatchEvent(new Event('input', { bubbles: true }));
    } else {
        generatedTitle = null;
    }
}

document.getElementById('load-template')?.addEventListener('click', async () => {
    const templateId = document.getElementById('template_id_d').value;
    if (!templateId) return;
    const request = ++templateRequest;
    try {
        const data = await fetchTemplate(templateId);
        if (request !== templateRequest) return;
        const select = document.getElementById('category_id_d');
        M.FormSelect.getInstance(select)?.destroy();
        select.value = data.categoryId ?? '';
        select.dispatchEvent(new Event('change', { bubbles: true }));
        M.FormSelect.init(select);
        tinymce.activeEditor.setContent(data.content);
        tinymce.triggerSave();
        loadedTemplateId = templateId;
        useDefaultTitle(data);
    } catch (error) {
        alert('Unable to load this template. Please refresh the page and try again.');
    }
});

dateInput?.addEventListener('change', async () => {
    if (!loadedTemplateId || generatedTitle === null || titleInput.value !== generatedTitle) return;
    const request = ++templateRequest;
    const previousTitle = titleInput.value;
    try {
        const data = await fetchTemplate(loadedTemplateId);
        if (request === templateRequest && titleInput.value === previousTitle) useDefaultTitle(data);
    } catch (error) {
        alert('Unable to update the default title. Check the entry date and try again.');
    }
});
