(function () {
    'use strict';

    var interval = 10000;
    var refreshPending = false;
    var requestInProgress = false;
    var dirtyForms = new Set();
    var dirtyContentEditable = false;
    var scrollStorageKey = 'live-refresh-scroll:' + window.location.pathname + window.location.search;

    function currentPageText(body) {
        var clone = body.cloneNode(true);
        clone.querySelectorAll('script, style, noscript').forEach(function (element) {
            element.remove();
        });

        return (clone.textContent || '').replace(/\s+/g, ' ').trim();
    }

    function hasUnsavedChanges() {
        if (dirtyForms.size > 0 || dirtyContentEditable || document.querySelector('.modal.show')) {
            return true;
        }

        var activeElement = document.activeElement;
        if (activeElement && activeElement.closest('input, textarea, select, [contenteditable="true"]')) {
            return true;
        }

        return false;
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

    function checkForUpdates() {
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
    }

    document.addEventListener('input', function (event) {
        if (!event.isTrusted) {
            return;
        }

        var form = event.target.closest('form');
        if (form) {
            dirtyForms.add(form);
        }

        if (event.target.isContentEditable) {
            dirtyContentEditable = true;
        }
    }, true);

    document.addEventListener('change', function (event) {
        if (event.isTrusted) {
            var form = event.target.closest('form');
            if (form) {
                dirtyForms.add(form);
            }
        }
    }, true);

    document.addEventListener('reset', function (event) {
        var form = event.target;
        window.setTimeout(function () {
            dirtyForms.delete(form);
        }, 0);
    }, true);

    window.setInterval(checkForUpdates, interval);
    document.addEventListener('visibilitychange', checkForUpdates);
    window.addEventListener('focus', checkForUpdates);
})();
