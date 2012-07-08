/* CampusBoard client behaviour (jQuery 1.12) */
(function ($) {
    'use strict';

    function escapeHtml(s) {
        return $('<div/>').text(s == null ? '' : String(s)).html();
    }

    function money(cents) {
        return cents === 0 ? 'Free' : '$' + (cents / 100).toFixed(2);
    }

    function renderCard(item) {
        return '<li class="card"><a href="/listings/' + item.id + '">' +
            '<span class="price">' + money(+item.price_cents) + '</span>' +
            '<strong>' + escapeHtml(item.title) + '</strong>' +
            '<span class="meta">' + escapeHtml(item.category) + ' &middot; ' + escapeHtml(item.seller) + '</span>' +
            '</a></li>';
    }

    // Live search: debounce keystrokes, cancel stale requests, keep the URL shareable.
    var $form = $('#search');
    if ($form.length) {
        var timer = null;
        var pending = null;

        var run = function () {
            var params = $form.serialize();
            if (pending) { pending.abort(); }
            pending = $.getJSON('/api/listings?' + params)
                .done(function (data) {
                    $('#results').html($.map(data.items, renderCard).join(''));
                    $('#result-count').text(data.total + ' listing' + (data.total === 1 ? '' : 's'));
                    $('.pager').remove();
                    if (window.history && history.replaceState) {
                        history.replaceState(null, '', '/?' + params);
                    }
                })
                .always(function () { pending = null; });
        };

        $form.on('input', 'input[name=q]', function () {
            clearTimeout(timer);
            timer = setTimeout(run, 250);
        });
        $form.on('change', 'select', run);
    }

    // Character counter on the description field.
    var $body = $('#listing-form textarea[name=body]');
    if ($body.length) {
        var max = +$body.data('maxlength');
        var update = function () {
            var left = max - $body.val().length;
            $('#body-count').text(left + ' characters left').toggleClass('error', left < 0);
        };
        $body.on('input', update);
        update();
    }

    $(document).on('click', '[data-confirm]', function (e) {
        if (!window.confirm($(this).data('confirm'))) { e.preventDefault(); }
    });
})(jQuery);
