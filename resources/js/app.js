import './bootstrap';
import '@popperjs/core';

// Property search functionality
$('#propertySearch').change(function() {
    
    var searchTerm = $(this).val();

    if (searchTerm == '') {
        $('.pl-search-hide').each(function() {
            $(this).removeClass('pl-search-hide');    
        });
        return;
    }

    // Loop through items and check for matches
    $('.pl-item').each(function() {

        if ( !$('.pli-name', $(this)).html().includes(searchTerm) && 
             !$('.pli-id', $(this)).html().includes(searchTerm) && 
             !$('.pli-url', $(this)).html().includes(searchTerm) ) 
        {
            $(this).addClass('pl-search-hide');
        } else {
            $(this).removeClass('pl-search-hide');
        }

    });

});