jQuery(document).ready(function ($) {
// Drag n drop social networks
	$('#mashsb_network_list').sortable({
                items: '.mashsb_list_item',
                opacity: 0.6,
                cursor: 'move',
                axis: 'y',
                update: function(){
                    var order = $(this).sortable('serialize') + '&action=mashsb_update_order';
                    $.post(ajaxurl, order, function(response){
                        alert(response);
                    });
                }    
        });
});