(function($) {
  $(document).ready(function() {

    // Handle tags actions
    $('.scifi-facets-terms-tags-inactive').hide();
    $('.scifi-facets-terms-format-tags .scifi-facets-terms-title')
      .on('click', function(event) {
        event.preventDefault();
        var currentInactive = $(this).closest('.scifi-facets-terms-format-tags').find('.scifi-facets-terms-tags-inactive');
        $('.scifi-facets-terms-tags-inactive:visible').not(currentInactive).slideUp(150);
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
