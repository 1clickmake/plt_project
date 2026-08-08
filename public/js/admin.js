$(document).ready(function() {
    // Highlight current menu item based on ID set in header/view
    // or we can do it by URL if needed.
    // The id-based one is already implemented in views like $('#link-dashboard').addClass('active')

    // Mobile sidebar backdrop click (optional but nice)
    $(document).on('click', function(e) {
        if ($(window).width() < 992) {
            if (!$(e.target).closest('#sidebar, .mobile-toggle').length && $('#sidebar').hasClass('active')) {
                $('#sidebar').removeClass('active');
            }
        }
    });

    // Subtle animation for cards (only if they are initially meant to be visible)
    $('.glass-card:not([hidden]):not([style*="display: none"])').css('opacity', 0).fadeTo(500, 1);
});
