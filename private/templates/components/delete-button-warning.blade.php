<script>
    document.getElementById('delete-record')?.addEventListener('submit', event => {
        if (!confirm('Delete this item? This cannot be undone. Entries and templates in a deleted category will become uncategorized.')) {
            event.preventDefault();
            return;
        }
        window.onbeforeunload = null;
    });
</script>
