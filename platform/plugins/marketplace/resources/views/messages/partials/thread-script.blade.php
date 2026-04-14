<script>
    'use strict';

    window.addEventListener('load', function () {
        var $wrapper = $('[data-bb-toggle="marketplace-thread-wrapper"]');

        if (! $wrapper.length) {
            return;
        }

        var isRefreshing = false;

        var scrollToBottom = function () {
            var $container = $wrapper.find('[data-bb-toggle="marketplace-thread-messages"]');

            if ($container.length) {
                $container.scrollTop($container[0].scrollHeight);
            }
        };

        var refreshThread = function () {
            if (document.visibilityState === 'hidden' || isRefreshing) {
                return;
            }

            isRefreshing = true;

            $.ajax({
                url: $wrapper.data('fetch-url'),
                method: 'GET',
                success: function (response) {
                    if (response.data && response.data.html) {
                        $wrapper.find('[data-bb-toggle="marketplace-thread-messages"]').replaceWith(response.data.html);
                        scrollToBottom();
                    }
                },
                complete: function () {
                    isRefreshing = false;
                }
            });
        };

        scrollToBottom();

        $(document).on('submit', '[data-bb-toggle="marketplace-thread-form"]', function (e) {
            e.preventDefault();

            var $form = $(e.currentTarget);
            var $button = $form.find('button[type="submit"]');
            var loadingClass = $button.data('bb-loading') || 'button-loading';

            $.ajax({
                url: $form.prop('action'),
                method: $form.prop('method'),
                data: $form.serialize(),
                beforeSend: function () {
                    $button.prop('disabled', true).addClass(loadingClass);
                },
                success: function (response) {
                    if (response.error) {
                        if (typeof Theme !== 'undefined') {
                            Theme.showError(response.message);
                        }

                        return;
                    }

                    $form[0].reset();

                    if (response.data && response.data.html) {
                        $wrapper.find('[data-bb-toggle="marketplace-thread-messages"]').replaceWith(response.data.html);
                        scrollToBottom();
                    }

                    if (typeof Theme !== 'undefined' && response.message) {
                        Theme.showSuccess(response.message);
                    }
                },
                error: function (response) {
                    if (typeof Theme !== 'undefined') {
                        Theme.handleError(response);
                    }
                },
                complete: function () {
                    $button.prop('disabled', false).removeClass(loadingClass);
                }
            });
        });

        window.setInterval(refreshThread, 15000);
    });
</script>
