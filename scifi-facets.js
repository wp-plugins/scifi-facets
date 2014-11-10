/**
 * @file
 * scifi Facets default JS file.
 */
(function($) {
  $(document).ready(function() {

  
  
    // Handle time links actions.
    $('.scifi-facets-group-time.scifi-facets-format-links .scifi-facets-group-list:not(.scifi-facets-current-group)').hide();
    $('.scifi-facets-group-time.scifi-facets-format-links .scifi-facets-group-title')
      .css('cursor', 'pointer')
      .on('click', function(event) {
        event.preventDefault();
        var currentInactive = $(this).next('.scifi-facets-group-list ');
        $(currentInactive).slideToggle(150);
      });
  
    // Handle taxonomy tags actions.
    $('.scifi-facets-tags-inactive').hide();
    $('.scifi-facets-format-tags .scifi-facets-group-title')
      .css('cursor', 'pointer')
      .on('click', function(event) {
        event.preventDefault();
        var currentInactive = $(this).next('.scifi-facets-group-list').find('.scifi-facets-tags-inactive');
        $('.scifi-facets-tags-inactive:visible').not(currentInactive).slideUp(150);
        $(currentInactive).slideToggle(150);
      });

    // Handle singular select.
    $('.scifi-facets-select')
      .on('change', function(event) {
        event.preventDefault();
        window.location.href = $(this).val();
      });

    // Handle multiple selects.
    $('.scifi-facets-select-multiple')
      .on('change', function(event) {
        event.preventDefault();
        var val = $(this).val();
        if (val && val.length > 0) {
          var url = $(this).attr('data-scifi-facets-addurl').replace('#slug#', $(this).val().join(','));
        }
        else {
          var url = $(this).attr('data-scifi-facets-removeurl');
        }
        window.location.href = url;
      });

  });
}(jQuery));
