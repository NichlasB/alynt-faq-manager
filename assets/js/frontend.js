jQuery(document).ready(function($) {
    $('.alynt-faq-container').addClass('is-loaded');
    const FAQ_SELECTORS = {
        container: '.alynt-faq-container',
        question: '.faq-question',
        answer: '.faq-answer',
        expandAll: '.expand-all',
        collapseAll: '.collapse-all'
    };

    // Handle individual FAQ toggles
    $(document).on('click', FAQ_SELECTORS.question, function(e) {
        e.preventDefault();
        const $question = $(this);
        const $answer = $question.closest('.faq-header').siblings('.faq-answer');
        const $container = $question.closest(FAQ_SELECTORS.container);
        const closeOpened = $container.hasClass('close-opened-yes');

        if (closeOpened) {
            const $otherQuestions = $container
            .find(FAQ_SELECTORS.question)
            .not($question);
            const $otherAnswers = $container
            .find(FAQ_SELECTORS.answer)
            .not($answer);

            $otherQuestions.attr('aria-expanded', 'false');
            $otherAnswers.attr('aria-hidden', 'true').prop('hidden', true);
        }

        const isExpanded = $question.attr('aria-expanded') === 'true';
        $question.attr('aria-expanded', !isExpanded);
        $answer.attr('aria-hidden', isExpanded).prop('hidden', isExpanded);
    });

    // Handle expand all button
    $(document).on('click', FAQ_SELECTORS.expandAll, function(e) {
        e.preventDefault();
        const $collection = $(this).closest('.alynt-faq-collection');
        $collection.find(FAQ_SELECTORS.question).attr('aria-expanded', 'true');
        $collection.find(FAQ_SELECTORS.answer)
        .attr('aria-hidden', 'false')
        .prop('hidden', false);
    });

    // Handle collapse all button
    $(document).on('click', FAQ_SELECTORS.collapseAll, function(e) {
        e.preventDefault();
        const $collection = $(this).closest('.alynt-faq-collection');
        $collection.find(FAQ_SELECTORS.question).attr('aria-expanded', 'false');
        $collection.find(FAQ_SELECTORS.answer)
        .attr('aria-hidden', 'true')
        .prop('hidden', true);
    });

    // Keyboard navigation
    $(document).on('keydown', FAQ_SELECTORS.question, function(e) {
        const $current = $(this);
        const $questions = $(FAQ_SELECTORS.question);
        const index = $questions.index($current);

        switch (e.keyCode) {
            case 38: // Up arrow
                e.preventDefault();
                if (index > 0) {
                    $questions.eq(index - 1).focus();
                }
                break;
            case 40: // Down arrow
                e.preventDefault();
                if (index < $questions.length - 1) {
                    $questions.eq(index + 1).focus();
                }
                break;
            case 36: // Home
                e.preventDefault();
                $questions.first().focus();
                break;
            case 35: // End
                e.preventDefault();
                $questions.last().focus();
                break;
            }
        });
});