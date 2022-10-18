/* export MatchHeight */

/* globals $, jQuery */
class MatchHeight {

    /**
     * Construct MatchHeight
     * @return {MatchHeight}
     */
    constructor(adminController) {

        adminController
            .onRefreshUi((e, domElement) => {
                this.matchHeight(domElement);
            });

        $(window)
            .on('resize', () => {
                    this.matchHeight();
                }
            );

        return this;
    }

    // --------------------------------------------------------------------------

    /**
     * Inits MatchHeight
     * @returns {MatchHeight}
     */
    matchHeight(domElement) {

        let heights = {};
        let elements = $('.match-height', domElement);

        //  Calculate the max height
        elements.each(function() {

            //  Reset the height
            $(this).height('');

            let group = $(this).data('height-group') || 'default';

            if (heights[group] === undefined) {
                heights[group] = 0;
            }

            if ($(this).height() > heights[group]) {
                heights[group] = $(this).height();
            }
        });

        //  Set the computed max height
        elements.each(function() {

            let group = $(this).data('height-group') || 'default';

            $(this).height(heights[group]);
        });

        return this;
    }
}

export default MatchHeight;
