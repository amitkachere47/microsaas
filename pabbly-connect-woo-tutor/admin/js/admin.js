jQuery(document).ready(function($) {
    // Section toggling
    $('.section-toggle').on('click', function() {
        $(this).toggleClass('collapsed');
        $(this).closest('h2').next('.form-table').slideToggle();
    });
    $('.section-toggle').addClass('collapsed').closest('h2').next('.form-table').hide();

    // Conditional logic UI
    $('body').on('change', '.webhook-checkbox', function() {
        $(this).closest('tr').find('.conditions-wrapper').slideToggle($(this).is(':checked'));
    });

    // Make conditions sortable
    $('.conditions-list').sortable({
        handle: '.condition-row, .condition-group',
        update: function( event, ui ) {
            updateNames();
        }
    });

    // Add Condition
    $('body').on('click', '.add-condition', function() {
        var conditionsList = $(this).closest('.group-actions').prev('.conditions-list');
        var trigger = $(this).data('trigger');
        var template = $('#condition-row-template').html().replace(/{{trigger}}/g, trigger);
        conditionsList.append(template);
        updateNames();
    });

    // Add Group
    $('body').on('click', '.add-group', function() {
        var conditionsList = $(this).closest('.group-actions').prev('.conditions-list');
        var trigger = $(this).data('trigger');
        var template = $('#condition-group-template').html().replace(/{{trigger}}/g, trigger);
        conditionsList.append(template);
        updateNames();
    });

    // Remove Condition
    $('body').on('click', '.remove-condition', function() {
        $(this).closest('.condition-row').remove();
        updateNames();
    });

    // Remove Group
    $('body').on('click', '.remove-group', function() {
        $(this).closest('.condition-group').remove();
        updateNames();
    });

    function updateNames() {
        $('.conditions-wrapper').each(function() {
            var trigger = $(this).closest('tr').find('.webhook-checkbox').attr('id');
            var basePath = 'pcwt_webhook_conditions[' + trigger + ']';
            updateGroupNames($(this).children('.condition-group'), basePath);
        });
    }

    function updateGroupNames(group, path) {
        group.children('.group-logic').find('select').attr('name', path + '[logic]');
        group.children('.conditions-list').children().each(function(index) {
            var newPath = path + '[conditions][' + index + ']';
            if ($(this).hasClass('condition-group')) {
                $(this).attr('data-path', newPath);
                updateGroupNames($(this), newPath);
            } else {
                updateRowNames($(this), newPath);
            }
        });
    }

    function updateRowNames(row, path) {
        row.find('select, input').each(function() {
            var name = $(this).data('name');
            if (name) {
                $(this).attr('name', path + '[' + name + ']');
            }
        });
    }
});
