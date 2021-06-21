$('#load-template').on('click', function(){
    var $template_id = $('#template_id_d option:selected').val();
    var $ajax_url = BASE_URL + '/template/' + $template_id + '/ajax';
    var select = $('#category_id_d');

    $.ajax({
        url: $ajax_url,
        dataType: 'json',
        success: function(data){
            // Update dropdown and reload form
            select.val(data.categoryId).change();
            select.formSelect();

            // Update values in fields
            $('#entry_title').val(data.title);
            $(".input-field label[for='entry_title']").attr("class", "active");
            $('#entry_content').val(data.content);
            $(".input-field label[for='entry_content']").attr("class", "active");
        },
        error: function(xhr){
            alert(xhr.responseText + ', please refresh the page and try again.');
        }
    });
});