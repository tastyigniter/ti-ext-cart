window.setInterval(function () {

    jQuery.ajax(location.href, {
        dataType: 'html'
    })
    .done(function (html) {
        html = jQuery.parseHTML(html);
        html.forEach(function (node) {
            if (node.tagName && node.tagName.toUpperCase() == 'MAIN') {
                var newEl, currentEl;
                if ((newEl = node.querySelector('#ti-order-status')) && (currentEl = document.querySelector('#ti-order-status'))) {
                    currentEl.innerHTML = newEl.innerHTML;
                }
            }
        });
    });

}, 100000);