(function () {
    'use strict';

    var interval = 10000;
    var refreshPending = false;
    var requestInProgress = false;
    var scrollStorageKey = 'live-refresh-scroll:' + window.location.pathname + window.location.search;

    function currentPageText(body) {
        var clone = body.cloneNode(true);
        clone.querySelectorAll('script, style, noscript').forEach(function (element) {
            element.remove();
        });

        return (clone.textContent || '').replace(/\s+/g, ' ').trim();
    }

    function hasUnsavedChanges() {
        if (document.querySelector('.modal.show, [contenteditable="true"]:focus')) {
            return true;
        }

        var activeElement = document.activeElement;
        if (activeElement && activeElement.closest('input, textarea, select, [contenteditable="true"]')) {
            return true;
        }

        return Array.prototype.some.call(document.forms, function (form) {
            return Array.prototype.some.call(form.elements, function (field) {
                if (field.disabled || field.type === 'button' || field.type === 'submit' || field.type === 'reset' || field.type === 'hidden') {
                    return false;
                }

                if (field.type === 'checkbox' || field.type === 'radio') {
                    return field.checked !== field.defaultChecked;
                }

                if (field.tagName === 'SELECT') {
                    return Array.prototype.some.call(field.options, function (option) {
                        return option.selected !== option.defaultSelected;
                    });
                }

                return field.value !== field.defaultValue;
            });
        });
    }

    function reloadWhenSafe() {
        if (!refreshPending || hasUnsavedChanges() || window.matchMedia('print').matches) {
            return;
        }

        try {
            sessionStorage.setItem(scrollStorageKey, String(window.scrollY));
        } catch (error) {
            console.warn('Could not preserve the page scroll position:', error);
        }

        window.location.reload();
    }

    var savedScroll = null;
    try {
        savedScroll = sessionStorage.getItem(scrollStorageKey);
    } catch (error) {
        console.warn('Could not restore the page scroll position:', error);
    }

    if (savedScroll !== null) {
        try {
            sessionStorage.removeItem(scrollStorageKey);
        } catch (error) {
            console.warn('Could not clear the saved page scroll position:', error);
        }

        window.requestAnimationFrame(function () {
            window.scrollTo(0, Number(savedScroll));
        });
    }

    window.setInterval(function () {
        if (document.visibilityState !== 'visible') {
            return;
        }

        if (refreshPending) {
            reloadWhenSafe();
            return;
        }

        if (requestInProgress) {
            return;
        }

        requestInProgress = true;
        fetch(window.location.href, {
            cache: 'no-store',
            credentials: 'same-origin',
            headers: {
                'Accept': 'text/html',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
            .then(function (response) {
                if (!response.ok) {
                    throw new Error('Live refresh returned HTTP ' + response.status);
                }

                return response.text();
            })
            .then(function (html) {
                var refreshedDocument = new DOMParser().parseFromString(html, 'text/html');
                if (currentPageText(refreshedDocument.body) !== currentPageText(document.body)) {
                    refreshPending = true;
                    reloadWhenSafe();
                }
            })
            .catch(function (error) {
                console.warn('Live refresh failed:', error);
            })
            .then(function () {
                requestInProgress = false;
            });
    }, interval);
})();
