jQuery(document).ready(function($) {
    // Initialize sortable
    var $sortableList = $('#sortable-faq-list');
    
    if ($sortableList.length) {
        $sortableList.sortable({
            handle: '.faq-handle',
            placeholder: 'ui-sortable-placeholder',
            update: function(event, ui) {
                updateOrder();
            }
        });
    }

    // Handle collection dropdown change
    $('#collection-dropdown').on('change', function() {
        var collectionId = $(this).val();
        if (collectionId) {
            window.location.href = 'edit.php?post_type=alynt_faq&page=alynt-faq-order&collection=' + collectionId;
        }
    });

    // Update order function
    function updateOrder() {
        var postIds = $sortableList.find('.faq-item').map(function() {
            return $(this).data('post-id');
        }).get();

        $.ajax({
            url: alyntFaqAdmin.ajaxurl,
            type: 'POST',
            data: {
                action: 'alynt_faq_update_order',
                postIds: postIds,
                nonce: alyntFaqAdmin.nonce
            },
            success: function(response) {
                if (response.success) {
                    showFeedback(alyntFaqAdmin.messages.orderSaved, 'success');
                } else {
                    showFeedback(alyntFaqAdmin.messages.error, 'error');
                }
            },
            error: function() {
                showFeedback(alyntFaqAdmin.messages.error, 'error');
            }
        });
    }

    // Show feedback message
    function showFeedback(message, type) {
        var $feedback = $('#save-feedback');
        $feedback
            .removeClass('notice-success notice-error')
            .addClass('notice notice-' + type)
            .html('<p>' + message + '</p>')
            .fadeIn()
            .delay(3000)
            .fadeOut();
    }
});