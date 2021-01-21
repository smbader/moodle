// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Javascript controller for the "Grading" panel at the right of the page.
 *
 * @module     mod_assign/grading_slider
 * @class      GradingSlider
 * @copyright  2019 Steve Bader <smbader@ncsu.edu>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @since      3.6
 */
define(['jquery', 'mod_assign/grading_events', 'mod_assign/grading_panel',
        'mod_assign/grading_review_panel', 'mod_assign/grading_actions'],
       function($, GradingEvents, GradingPanel, GradingReviewPanel, GradingActions) {

    var GradingSlider = function(selector_dragbar, selector_main, selector_reviewpanel, selector_gradepanel) {
        this._dragbar = $(selector_dragbar);
        this._mainwindow = $(selector_main);
        this._reviewpanel = $(selector_reviewpanel);
        this._gradepanel = $(selector_gradepanel);

        this.registerEventListeners();
    };

    GradingSlider.prototype.registerEventListeners = function() {
        var docElement = $(document);

        var dragbar = $(this._dragbar);
        var main = $(this._mainwindow);
        var reviewpanel = $(this._reviewpanel);
        var gradepanel = $(this._gradepanel);

        dragbar.on('mousedown', function(e) {
            e.preventDefault();

            var ghostbar = $('<div>',
                {id:'ghostbar',
                 css: {
                    height: 'calc(100% - 145px)',
                    marginTop: '85px',
                    cursor: 'col-resize',
                    top: main.offset().top,
                    left: main.offset().left
                }
            }).appendTo('body');

            docElement.on('mousemove', function(e){
              ghostbar.css("left",e.pageX);
            });
        });

        docElement.on('mouseup', function(e){
            if ($('#ghostbar').length) {
                this.resetPanels();
                var mainWidth = e.pageX;

                if (e.pageX < 850) {
                    reviewpanel.addClass('wrapToolbar');
                    if (e.pageX < 550) {
                        mainWidth = 550;
                    }
                } else if (window.innerWidth - e.pageX < 250) {
                    mainWidth = window.innerWidth - 250;
                } else {
                    reviewpanel.removeClass('wrapToolbar');
                }

                var percentage = (mainWidth / window.innerWidth) * 100;
                var mainPercentage = 100-percentage;

                reviewpanel.css("right",mainPercentage + "%");
                gradepanel.css("left",percentage + "%");
                gradepanel.css("width",mainPercentage + "%");
                $('#ghostbar').remove();
                docElement.unbind('mousemove');
            }
        }.bind(this));

        docElement.on(GradingEvents.COLLAPSE_GRADE_PANEL, function() {
            this.resetAdjustments();
        }.bind(this));

        docElement.on(GradingEvents.COLLAPSE_REVIEW_PANEL, function() {
            this.resetAdjustments();
        }.bind(this));

        docElement.on(GradingEvents.EXPAND_GRADE_PANEL, function() {
            this.resetAdjustments();
        }.bind(this));
    };

    GradingSlider.prototype.resetPanels = function () {
        GradingReviewPanel.prototype.expandPanel();
        GradingPanel.prototype.expandPanel();
        GradingActions.prototype.resetLayoutButtons();
        GradingActions.prototype.getExpandAllPanelsButton().addClass('active');
    };

    GradingSlider.prototype.resetAdjustments = function() {
        var reviewpanel = $(this._reviewpanel);
        var gradepanel = $(this._gradepanel);

        reviewpanel.removeClass('wrapToolbar');
        reviewpanel.removeAttr('style');
        gradepanel.removeAttr('style');
        $('#ghostbar').remove();
    };

    return GradingSlider;
});
