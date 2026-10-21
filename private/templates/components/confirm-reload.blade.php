<script>
    window.onbeforeunload = () => 'Unsaved data will be lost.';
    document.addEventListener('submit', event => {
        if (!event.defaultPrevented) window.onbeforeunload = null;
    });
</script>
