<script>
    $(document).ready(function () {
        // Show a warning before the user leaves the page
        $(window).on('beforeunload', function(){
            return "Unsaved data will be lost.";
        });

        // When a form is submitted
        $(document).on("submit", "form", function(event){
            // disable warning
            $(window).off('beforeunload');
        });
    });
</script>