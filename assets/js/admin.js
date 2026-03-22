jQuery(document).ready(function($) {
    var $sortableList = $('#sortable-faq-list');
    var $customCssForm = $('#custom-css-form');
    var $customCssField = $('#alynt_faq_custom_css');
    var $customCssVersionField = $('#alynt_faq_custom_css_version');
    var $cssValidation = $('#alynt-faq-css-validation');
    var reorderRequest = null;
    var reorderPending = false;
    var reorderOrderVersion = $sortableList.length ? String($sortableList.data('order-version') || '') : '';
    var hasUnsavedCssChanges = false;
    var initialCssValue = $customCssField.length ? $customCssField.val() : '';
    var KEY_CODES = {
        UP: 38,
        DOWN: 40
    };
    var DEFAULT_UNSAFE_CSS_PATTERNS = ['expression', 'javascript:', 'behavior:', '-moz-binding', '@import', 'data:'];

    function getUnsafeCssPatterns() {
        if (typeof alyntFaqAdmin !== 'undefined' && Array.isArray(alyntFaqAdmin.unsafeCssPatterns) && alyntFaqAdmin.unsafeCssPatterns.length) {
            return alyntFaqAdmin.unsafeCssPatterns;
        }

        return DEFAULT_UNSAFE_CSS_PATTERNS;
    }

    function responseRequiresRefresh(jqXHR) {
        return !!(
            jqXHR &&
            jqXHR.responseJSON &&
            jqXHR.responseJSON.data &&
            jqXHR.responseJSON.data.refresh
        );
    }

    function showFeedback(message, type) {
        var $feedback = $('#save-feedback');
        if (!$feedback.length) {
            $feedback = $('<div id="save-feedback" class="notice" role="status" tabindex="-1" style="display: none;"></div>');
            $('.wrap').find('h1').first().after($feedback);
        }

        var isAssertive = type === 'error';

        $feedback.stop(true, true);
        $feedback
        .removeClass('notice-success notice-error notice-info is-dismissible')
        .addClass('notice notice-' + type)
        .empty()
        .append($('<p>').text(message));

        if (type !== 'info') {
            $feedback.addClass('is-dismissible');
        }

        $feedback.show();

        var $announce = $('#alynt-faq-announce');
        $announce.attr('aria-live', isAssertive ? 'assertive' : 'polite');
        $announce.text('');
        window.setTimeout(function() {
            $announce.text(message);
        }, 20);

        if (type === 'error' || type === 'success') {
            window.setTimeout(function() {
                $feedback.trigger('focus');
            }, 20);
        }
    }

    function getResponseMessage(response, fallback) {
        if (response && response.data && response.data.message) {
            return response.data.message;
        }

        return fallback;
    }

    function getRequestErrorMessage(jqXHR, textStatus, fallback) {
        var responseMessage = fallback;

        if (jqXHR && jqXHR.responseJSON && jqXHR.responseJSON.data && jqXHR.responseJSON.data.message) {
            responseMessage = jqXHR.responseJSON.data.message;
        }

        if (textStatus === 'timeout') {
            return alyntFaqAdmin.messages.timeoutError;
        }

        if (typeof navigator !== 'undefined' && navigator.onLine === false) {
            return alyntFaqAdmin.messages.networkError;
        }

        if (jqXHR && jqXHR.status >= 500) {
            return responseMessage !== fallback ? responseMessage : alyntFaqAdmin.messages.serverError;
        }

        return responseMessage;
    }

    function syncDisplayedOrder() {
        if (!$sortableList.length) {
            return;
        }

        $sortableList.find('.faq-item').each(function(index) {
            $(this).find('.faq-order').text(index);
        });
    }

    function setReorderBusyState(isBusy) {
        if (!$sortableList.length) {
            return;
        }

        $sortableList.toggleClass('is-saving', isBusy);
        $sortableList.attr('aria-busy', isBusy ? 'true' : 'false');
        $sortableList.find('.faq-item').toggleClass('is-disabled', isBusy);

        if ($sortableList.data('ui-sortable')) {
            $sortableList.sortable(isBusy ? 'disable' : 'enable');
        }
    }

    function clearCssValidation() {
        if (!$customCssField.length || !$cssValidation.length) {
            return;
        }

        $customCssField.attr('aria-invalid', 'false');
        $cssValidation.text('').hide();
    }

    function showCssValidation(message) {
        if (!$customCssField.length || !$cssValidation.length) {
            return;
        }

        $customCssField.attr('aria-invalid', 'true');
        $cssValidation.text(message).show();
    }

    function isCssValidationMessage(message) {
        return message === alyntFaqAdmin.messages.cssFormatError || message === alyntFaqAdmin.messages.cssUnsafeError;
    }

    function validateCustomCss() {
        if (!$customCssField.length) {
            return true;
        }

        var customCSS = $customCssField.val();

        if (!customCSS) {
            clearCssValidation();
            return true;
        }

        if (customCSS.indexOf('{') === -1 || customCSS.indexOf('}') === -1) {
            showCssValidation(alyntFaqAdmin.messages.cssFormatError);
            return false;
        }

        var harmfulPatterns = getUnsafeCssPatterns();
        var normalizedCSS = customCSS.toLowerCase();

        for (var i = 0; i < harmfulPatterns.length; i++) {
            if (normalizedCSS.indexOf(harmfulPatterns[i]) !== -1) {
                showCssValidation(alyntFaqAdmin.messages.cssUnsafeError);
                return false;
            }
        }

        clearCssValidation();
        return true;
    }

    function setCssFormBusyState(isBusy) {
        if (!$customCssForm.length) {
            return;
        }

        $customCssForm.attr('aria-busy', isBusy ? 'true' : 'false');
        $customCssForm.find('button, input[type="submit"]').prop('disabled', isBusy);

        if (isBusy) {
            $customCssForm.find('button, input[type="submit"]').attr('aria-disabled', 'true');
        } else {
            $customCssForm.find('button, input[type="submit"]').removeAttr('aria-disabled');
        }
    }

    function openResetConfirmation() {
        var $confirmation = $('#alynt-faq-reset-confirmation');
        if (!$confirmation.length) {
            return;
        }

        $confirmation.prop('hidden', false);
        $('#reset-css').attr('aria-expanded', 'true');
        $('#cancel-reset-css').trigger('focus');
    }

    function closeResetConfirmation() {
        var $confirmation = $('#alynt-faq-reset-confirmation');
        if (!$confirmation.length) {
            return;
        }

        $confirmation.prop('hidden', true);
        $('#reset-css').attr('aria-expanded', 'false').trigger('focus');
    }

    function updateOrder() {
        if (!$sortableList.length) {
            return;
        }

        if (reorderRequest && reorderRequest.readyState !== 4) {
            reorderPending = true;
            return;
        }

        var postIds = $sortableList.find('.faq-item').map(function() {
            return $(this).data('post-id');
        }).get();

        reorderPending = false;

        reorderRequest = $.ajax({
            url: alyntFaqAdmin.ajaxurl,
            type: 'POST',
            timeout: alyntFaqAdmin.requestTimeout,
            data: {
                action: 'alynt_faq_update_order',
                postIds: postIds,
                collectionId: alyntFaqAdmin.collectionId,
                orderVersion: reorderOrderVersion,
                nonce: alyntFaqAdmin.nonce
            },
            beforeSend: function() {
                setReorderBusyState(true);
                showFeedback(alyntFaqAdmin.messages.orderSaving, 'info');
            },
            success: function(response) {
                if (response.success) {
                    if (response.data && response.data.orderVersion) {
                        reorderOrderVersion = response.data.orderVersion;
                        $sortableList.attr('data-order-version', reorderOrderVersion);
                    }
                    showFeedback(getResponseMessage(response, alyntFaqAdmin.messages.orderSaved), 'success');
                } else {
                    showFeedback(getResponseMessage(response, alyntFaqAdmin.messages.error), 'error');
                }
            },
            error: function(jqXHR, textStatus) {
                if (responseRequiresRefresh(jqXHR)) {
                    reorderPending = false;
                }

                var errorMessage = getRequestErrorMessage(jqXHR, textStatus, alyntFaqAdmin.messages.error);
                showFeedback(errorMessage, 'error');
            },
            complete: function() {
                reorderRequest = null;
                setReorderBusyState(false);

                if (reorderPending) {
                    updateOrder();
                }
            }
        });
    }

    function initSortable() {
        // Initialize sortable
        if ($sortableList.length) {
            $sortableList.attr('aria-busy', 'false');
            $sortableList.sortable({
                handle: '.faq-handle',
                placeholder: 'ui-sortable-placeholder',
                update: function() {
                    syncDisplayedOrder();
                    updateOrder();
                }
            });
        }
    }

    function initCollectionDropdown() {
        // Handle collection dropdown change
        $('#collection-dropdown').on('change', function() {
            var collectionId = $(this).val();
            if (collectionId) {
                window.location.href = 'edit.php?post_type=alynt_faq&page=alynt-faq-order&collection=' + collectionId;
            } else {
                window.location.href = 'edit.php?post_type=alynt_faq&page=alynt-faq-order';
            }
        });
    }

    function initCustomCssForm() {
        // Handle Custom CSS form submission
        $customCssForm.on('submit', function(e) {
            e.preventDefault();

            if (!validateCustomCss()) {
                showFeedback($cssValidation.text(), 'error');
                $customCssField.trigger('focus');
                return;
            }

            var customCSS = $customCssField.val();
            var nonce = $('#alynt_faq_custom_css_nonce').val();
            var cssVersion = $customCssVersionField.val();

            $.ajax({
                url: alyntFaqAdmin.ajaxurl,
                type: 'POST',
                timeout: alyntFaqAdmin.requestTimeout,
                data: {
                    action: 'alynt_faq_save_custom_css',
                    css: customCSS,
                    cssVersion: cssVersion,
                    nonce: nonce
                },
                beforeSend: function() {
                    clearCssValidation();
                    setCssFormBusyState(true);
                    showFeedback(alyntFaqAdmin.messages.cssSaving, 'info');
                },
                success: function(response) {
                    if (response.success) {
                        if (response.data && response.data.cssVersion) {
                            $customCssVersionField.val(response.data.cssVersion);
                        }
                        initialCssValue = $customCssField.val();
                        hasUnsavedCssChanges = false;
                        clearCssValidation();
                        showFeedback(getResponseMessage(response, alyntFaqAdmin.messages.cssSaved), 'success');
                    } else {
                        var errorMessage = getResponseMessage(response, alyntFaqAdmin.messages.cssError);
                        showFeedback(errorMessage, 'error');

                        if (isCssValidationMessage(errorMessage)) {
                            showCssValidation(errorMessage);
                            $customCssField.trigger('focus');
                        }
                    }
                },
                error: function(jqXHR, textStatus) {
                    var errorMessage = getRequestErrorMessage(jqXHR, textStatus, alyntFaqAdmin.messages.cssError);
                    showFeedback(errorMessage, 'error');

                    if (isCssValidationMessage(errorMessage)) {
                        showCssValidation(errorMessage);
                        $customCssField.trigger('focus');
                    }
                },
                complete: function() {
                    setCssFormBusyState(false);
                }
            });
        });
    }

    function initResetButton() {
        $('#reset-css').on('click', function(e) {
            e.preventDefault();
            openResetConfirmation();
        });

        $('#cancel-reset-css').on('click', function(e) {
            e.preventDefault();
            closeResetConfirmation();
        });

        $('#confirm-reset-css').on('click', function(e) {
            e.preventDefault();
            closeResetConfirmation();
            $customCssField.val('');
            $customCssField.trigger('input');
            clearCssValidation();
            $customCssForm.trigger('submit');
        });
    }

    function initCustomCssFieldState() {
        if (!$customCssField.length) {
            return;
        }

        $customCssField.on('input', function() {
            hasUnsavedCssChanges = $(this).val() !== initialCssValue;
            clearCssValidation();
        });

        $customCssField.on('blur', function() {
            if ($(this).val()) {
                validateCustomCss();
            } else {
                clearCssValidation();
            }
        });

        $(window).on('beforeunload', function() {
            if (hasUnsavedCssChanges) {
                return alyntFaqAdmin.messages.unsavedChanges;
            }
        });
    }

    function initKeyboardReorder() {
        if (!$sortableList.length) {
            return;
        }

        $sortableList.on('keydown', '.faq-item', function(e) {
            if ($sortableList.hasClass('is-saving')) {
                return;
            }

            var $item = $(this);
            var $items = $sortableList.find('.faq-item');
            var index = $items.index($item);

            if (e.keyCode === KEY_CODES.UP && index > 0) {
                e.preventDefault();
                $item.insertBefore($items.eq(index - 1));
                $item.focus();
                syncDisplayedOrder();
                updateOrder();
            } else if (e.keyCode === KEY_CODES.DOWN && index < $items.length - 1) {
                e.preventDefault();
                $item.insertAfter($items.eq(index + 1));
                $item.focus();
                syncDisplayedOrder();
                updateOrder();
            }
        });
    }

    initSortable();
    initCollectionDropdown();
    initCustomCssForm();
    initResetButton();
    initCustomCssFieldState();
    initKeyboardReorder();
});
