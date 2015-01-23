jQuery(document).ready(function($) {

    /**
     * Monitor state change of the All Services checkbox
     */
    $('#mode_all').change(function() {
        disable(this.checked);
    });

    /**
     * Set disabled state of the scriptMode checkboxes.
     *
     * @param  bool  state
     * @return void
     */
    function disable(state)
    {
        var checkboxes = [
            'mode_existing_links',
            'mode_merchant_names',
            'mode_cpm_campaigns',
            'mode_contextual'
        ];

        for (var i = 0; i < checkboxes.length; i++) {
            var checkbox = '#'+checkboxes[i];
            $(checkbox).prop('disabled', state);
        }
    }

    $('span.dashicons-editor-help').tooltip();
});
