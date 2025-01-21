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

    // Handle Custom CSS form submission
    $('#custom-css-form').on('submit', function(e) {
        e.preventDefault();
        
        var $form = $(this);
        var customCSS = $('#alynt_faq_custom_css').val();
        var nonce = $('#alynt_faq_custom_css_nonce').val();

        $.ajax({
            url: alyntFaqAdmin.ajaxurl,
            type: 'POST',
            data: {
                action: 'alynt_faq_save_custom_css',
                css: customCSS,
                nonce: nonce
            },
            beforeSend: function() {
                $form.find('button, input[type="submit"]').prop('disabled', true);
                showFeedback('Saving CSS...', 'info');
            },
            success: function(response) {
                if (response.success) {
                    showFeedback(response.data.message || alyntFaqAdmin.messages.cssSaved, 'success');
                } else {
                    showFeedback(response.data.message || alyntFaqAdmin.messages.cssError, 'error');
                }
            },
            error: function() {
                showFeedback(alyntFaqAdmin.messages.cssError, 'error');
            },
            complete: function() {
                $form.find('button, input[type="submit"]').prop('disabled', false);
            }
        });
    });

    // Handle Reset CSS button
    $('#reset-css').on('click', function(e) {
        e.preventDefault();
        if (confirm('Are you sure you want to reset to default CSS? This will remove all custom CSS.')) {
            $('#alynt_faq_custom_css').val('');
            $('#custom-css-form').submit();
        }
    });

    // Show feedback message
    function showFeedback(message, type) {
        var $feedback = $('#save-feedback');
        if (!$feedback.length) {
            $feedback = $('<div id="save-feedback" class="notice" style="display: none;"></div>');
            $('.wrap').prepend($feedback);
        }

        $feedback
        .removeClass('notice-success notice-error')
        .addClass('notice notice-' + type)
        .html('<p>' + message + '</p>')
        .fadeIn()
        .delay(3000)
        .fadeOut();
    }
});
