(function($) {
  
  $(document).ready(function() {
    
    $('.scifi-facets-terms-tags-inactive').hide();
    $('.scifi-facets-terms-format-tags .scifi-facets-terms-title')
      .on('click', function(event) {
        event.preventDefault();
        var currentInactive = $(this).closest('.scifi-facets-terms-format-tags').find('.scifi-facets-terms-tags-inactive');
        $('.scifi-facets-terms-tags-inactive:visible').not(currentInactive).slideUp(150);
        $(currentInactive).slideToggle(150);
      });
    
    $('.scifi-facets-select')
      .on('change', function(event) {
        event.preventDefault();
        window.location.href = $(this).val(); 
      });
    
    
  });
  
}(jQuery));