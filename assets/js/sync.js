jQuery(document).ready(function($) {
    $('#sync-form').submit(function(e) {
        e.preventDefault(); // Prevent form submission

        let form = $(this);
        let button = form.find('input[type="submit"]');
        button.val('Syncing...'); // Update button label
        let nonce = form.find('input[name="sync_old_media_nonce"]').val();
        button.prop('disabled', true);

        $.ajax({
            url: ajaxurl, // The WordPress AJAX URL
            type: 'POST',
            data: {
                action: 'sync_old_media',
                nonce: nonce// The AJAX action to be performed
            },
            success: function(response) {
                // Handle the AJAX response
                console.log(response);
                button.val('Sync'); // Restore button label
                button.prop('disabled', false);
            },
            error: function(xhr, status, error) {
                // Handle AJAX error
                console.log(error);
                button.val('Sync'); // Restore button label
                button.prop('disabled', false);
            }
        });
    });
});