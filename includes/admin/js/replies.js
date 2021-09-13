jQuery(document).ready(function() {

    var psf_topic_id = jQuery('#psf_topic_id');

    psf_topic_id.suggest(
        psf_topic_id.data('ajax-url'), {
            onSelect: function() {
                var value = this.value;
                psf_topic_id.val(value.substr(0, value.indexOf(' ')));
            }
        }
    );
});