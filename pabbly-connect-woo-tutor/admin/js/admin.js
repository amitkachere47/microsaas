jQuery(document).ready(function($) {
    $('.section-toggle').on('click', function() {
        $(this).toggleClass('collapsed');
        $(this).closest('h2').next('.form-table').slideToggle();
    });

    // Collapse all sections by default
    $('.section-toggle').addClass('collapsed');
    $('.form-table').hide();
});
