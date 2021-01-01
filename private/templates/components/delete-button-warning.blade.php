<script>
    var deleteButton = function deleteButton(warningMessage, redirectUrl) {
        $(window).off('beforeunload');

        if (!confirm(warningMessage)) {
            $(window).on('beforeunload', function(){
                return "Unsaved data will be lost.";
            });
            return false;
        } else {
            window.location.href=redirectUrl;
        }
    }
</script>