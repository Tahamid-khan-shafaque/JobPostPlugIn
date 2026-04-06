jQuery(document).ready(function($) {
    var currentPage = 1;

    function fetchJobs(page) {
        $('.jpm-loading').show();
        var search = $('#jpm_search').val();
        var category = $('#jpm_category').val();
        var type = $('#jpm_type').val();
        var security = $('#jpm_security').val();
        var baseUrl = $('#jpm-job-list').data('baseurl');

        $.ajax({
            url: jpm_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'filter_jobs',
                jpm_security: security,
                paged: page,
                search: search,
                category: category,
                type: type,
                base_url: baseUrl
            },
            success: function(response) {
                $('#jpm-job-list').html(response);
                $('.jpm-loading').hide();
                currentPage = page;
                
                // Scroll to top of list
                $('html, body').animate({
                    scrollTop: $('#jpm-job-list').offset().top - 50
                }, 300);
            },
            error: function() {
                alert('We encountered an error. Please try again.');
                $('.jpm-loading').hide();
            }
        });
    }

    // Filter submit
    $('#jpm-filter-form').on('submit', function(e) {
        e.preventDefault();
        fetchJobs(1);
    });

    // Reset filter
    $('#jpm-reset-btn').on('click', function(e) {
        e.preventDefault();
        $('#jpm-filter-form')[0].reset();
        fetchJobs(1);
    });

    // Pagination click
    $(document).on('click', '.jpm-page-btn', function() {
        var page = $(this).data('page');
        if (page !== currentPage) {
            fetchJobs(page);
        }
    });

    // Submit Application form
    $(document).on('submit', '#jpm-application-form', function(e) {
        e.preventDefault();
        var $form = $(this);
        var $btn = $form.find('button[type="submit"]');
        var $response = $form.find('.jpm-app-response');
        
        $btn.prop('disabled', true).text('Submitting...');
        $response.hide().removeClass('jpm-success jpm-error');

        var formData = new FormData($form[0]);

        $.ajax({
            url: jpm_ajax.ajax_url,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(res) {
                $response.show();
                if (res.success) {
                    $response.addClass('jpm-success').text(res.data);
                    $form[0].reset();
                    $('.jpm-file-text').html('<strong>Click to browse</strong> or drag and drop a file').css('color', '#475569');
                } else {
                    $response.addClass('jpm-error').text(res.data);
                }
                $btn.prop('disabled', false).text('Submit');
            },
            error: function() {
                $response.addClass('jpm-error').text('An unexpected error occurred. Please try again.').show();
                $btn.prop('disabled', false).text('Submit');
            }
        });
    });

    // Custom File Upload Text
    $(document).on('change', '#jpm_app_cv', function() {
        var fileName = $(this).val().split('\\').pop();
        if (fileName) {
            $('.jpm-file-text').html('<strong>Selected:</strong> ' + fileName).css('color', '#111827');
        } else {
            $('.jpm-file-text').html('<strong>Click to browse</strong> or drag and drop a file').css('color', '#475569');
        }
    });

});
