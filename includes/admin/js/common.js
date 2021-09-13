jQuery(document).ready(function() {

    var psf_author_id = jQuery('#psf_author_id');

    psf_author_id.suggest(
        psf_author_id.data('ajax-url'), {
            onSelect: function() {
                var value = this.value;
                psf_author_id.val(value.substr(0, value.indexOf(' ')));
            }
        }
    );
});