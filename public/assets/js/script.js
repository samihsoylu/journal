$( document ).ready(function() {
    // Navigation
    $('.sidenav').sidenav();
    $(".dropdown-trigger").dropdown();

    // Forms: select field
    $('form select').formSelect();

    // General site heading
    $('.parallax').parallax();

    // ----- View all entries page -----
    /** $('form#filters select').on('change', function(){
        $(this).closest('form').submit();
    });**/
    // View all entries page date picker
    $('.datepicker').datepicker();

    $('.modal').modal();

    $(".corner-alert").click(function(){
        $(".corner-alert").hide();
    });
});