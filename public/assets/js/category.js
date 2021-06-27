$( function() {
        $( "#sortable" ).sortable({
            placeholder: 'ui-state-highlight',
            axis: 'y',
            revert: true,
            handle: '.card-icon',
            stop: function (event, ui){
                var sortedIds = $(this).sortable("toArray");
                var $ajax_url = BASE_URL + '/categories/ajax/sort-order';
                $.ajax({
                    url: $ajax_url,
                    type: 'post',
                    data: { orderedCategoryIds : sortedIds },
                    error: function(xhr){
                        alert(xhr.responseText + ', please refresh the page and try again.');
                    }
                });
            }
    }).disableSelection();
} );
	