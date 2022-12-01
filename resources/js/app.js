import './bootstrap';
import '@popperjs/core';

import.meta.glob([
    '../images/**',
]);

// Property search functionality
$('#propertySearch').change(function() {
    
    var searchTerm = $(this).val().toLowerCase();

    if (searchTerm == '') {
        $('.pl-search-hide').each(function() {
            $(this).removeClass('pl-search-hide');    
        });
        return;
    }

    // Loop through items and check for matches
    $('.pl-item').each(function() {

        if ( !$('.pli-name', $(this)).html().toLowerCase().includes(searchTerm) && 
             !$('.pli-id', $(this)).html().toLowerCase().includes(searchTerm) && 
             !$('.pli-url', $(this)).html().toLowerCase().includes(searchTerm) ) 
        {
            $(this).addClass('pl-search-hide');
        } else {
            $(this).removeClass('pl-search-hide');
        }

    });

});