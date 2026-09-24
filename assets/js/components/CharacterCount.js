/* export CharacterCount */

/* globals $, jQuery */

/**
 * Admin UI: Character Count
 *
 * Takes `small.char-count` out of the page flow and sits it in a channel
 * along the bottom of the field, so max-length captions do not add an extra
 * row between inputs.
 */

const SELECTOR = '.field .char-count:not(.counting)';
const CLASS_COUNTING = 'counting';
const CLASS_WRAP = 'char-count-wrap';
const CLASS_EXCEEDED = 'max-length-exceeded';

class CharacterCount {

    /**
     * @param {Object} adminController
     * @return {CharacterCount}
     */
    constructor(adminController) {

        this.adminController = adminController;

        this.adminController
            .onRefreshUi((e, domElement) => {
                this.init(domElement);
            });

        return this;
    }

    // --------------------------------------------------------------------------

    /**
     * Bind any new counters in scope
     * @param {HTMLElement|Document} domElement
     * @returns {CharacterCount}
     */
    init(domElement) {

        let $scope = $(domElement && domElement.nodeType ? domElement : document);

        $scope
            .find(SELECTOR)
            .addBack(SELECTOR)
            .each((index, element) => {
                this.bind($(element));
            });

        return this;
    }

    // --------------------------------------------------------------------------

    /**
     * Wrap the control and keep the caption in sync
     * @param {jQuery} $counter
     * @returns {CharacterCount}
     */
    bind($counter) {

        let maxLength = parseInt($counter.data('max-length'), 10);
        let $field = $counter.closest('.field');
        let $input = $counter.closest('.input').find('.field-input').first();

        if (!$input.length || !maxLength) {
            $counter.addClass(CLASS_COUNTING);
            return this;
        }

        $input.wrap($('<span>', {class: CLASS_WRAP}));
        $input.parent().append($counter);

        let update = () => {
            let length = ($input.val() || '').length;
            $field.toggleClass(CLASS_EXCEEDED, length > maxLength);
            $counter.text(length + ' / ' + maxLength);
        };

        $input.on('input.charcount change.charcount', update);
        update();
        $counter.addClass(CLASS_COUNTING);

        return this;
    }
}

export default CharacterCount;
