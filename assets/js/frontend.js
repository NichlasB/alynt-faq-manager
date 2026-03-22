jQuery(document).ready(function($) {
    const FAQ_SELECTORS = {
        container: '.alynt-faq-container',
        question: '.alynt-faq-question',
        answer: '.alynt-faq-answer',
        expandAll: '.alynt-faq-expand-all',
        collapseAll: '.alynt-faq-collapse-all'
    };
    const KEY_CODES = {
        UP: 38,
        DOWN: 40,
        HOME: 36,
        END: 35
    };

    function initLoadedState() {
        $(FAQ_SELECTORS.container).each(function() {
            const $container = $(this);

            $container.addClass('is-loaded');
            $container.find(FAQ_SELECTORS.question).attr('aria-expanded', 'false');
            $container.find(FAQ_SELECTORS.answer)
            .attr('aria-hidden', 'true')
            .prop('hidden', true);
        });
    }

    function bindQuestionToggle() {
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
    }

    function bindCollectionControls() {
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
    }

    function bindKeyboardNavigation() {
        $(document).on('keydown', FAQ_SELECTORS.question, function(e) {
            const $current = $(this);
            const $container = $current.closest('.alynt-faq-collection');
            const $questions = $container.length ? $container.find(FAQ_SELECTORS.question) : $(FAQ_SELECTORS.question);
            const index = $questions.index($current);

            switch (e.keyCode) {
                case KEY_CODES.UP:
                    e.preventDefault();
                    if (index > 0) {
                        $questions.eq(index - 1).focus();
                    }
                    break;
                case KEY_CODES.DOWN:
                    e.preventDefault();
                    if (index < $questions.length - 1) {
                        $questions.eq(index + 1).focus();
                    }
                    break;
                case KEY_CODES.HOME:
                    e.preventDefault();
                    $questions.first().focus();
                    break;
                case KEY_CODES.END:
                    e.preventDefault();
                    $questions.last().focus();
                    break;
            }
        });
    }

    initLoadedState();
    bindQuestionToggle();
    bindCollectionControls();
    bindKeyboardNavigation();
});