(function (window, document, $) {
    'use strict';

    if (!$) return;

    var pendingButton = null;
    var pendingAt = 0;
    var requestButtons = new WeakMap();
    var actionPattern = /\b(simpan|menyimpan|save|update|memperbarui)\b/i;

    function isActionButton(element) {
        if (!element) return false;

        var button = element.closest('button, input[type="submit"], input[type="button"]');
        if (!button || button.disabled) return false;

        var label = button.tagName === 'INPUT' ? button.value : button.textContent;
        return actionPattern.test((label || '').trim()) ? button : false;
    }

    function remember(button) {
        pendingButton = button;
        pendingAt = Date.now();
        button.dataset.pendingOriginalHtml = button.innerHTML;
        button.dataset.pendingOriginalDisabled = button.disabled ? '1' : '0';
    }

    function startLoading(button) {
        if (!button || button.dataset.requestLoading === '1') return;

        button.dataset.requestLoading = '1';
        button.dataset.originalHtml = button.dataset.pendingOriginalHtml || button.innerHTML;
        button.dataset.originalDisabled = button.dataset.pendingOriginalDisabled || (button.disabled ? '1' : '0');
        delete button.dataset.pendingOriginalHtml;
        delete button.dataset.pendingOriginalDisabled;
        button.disabled = true;
        button.setAttribute('aria-busy', 'true');
        button.innerHTML = '<span class="spinner-border spinner-border-sm mr-1" role="status" aria-hidden="true"></span> Memproses...';
    }

    function stopLoading(button) {
        if (!button || button.dataset.requestLoading !== '1') return;

        button.innerHTML = button.dataset.originalHtml || 'Simpan';
        button.disabled = button.dataset.originalDisabled === '1';
        button.removeAttribute('aria-busy');
        delete button.dataset.requestLoading;
        delete button.dataset.originalHtml;
        delete button.dataset.originalDisabled;
    }

    // Capture sebelum onclick milik modul berjalan, supaya request AJAX dapat
    // dipasangkan dengan tombol yang benar.
    document.addEventListener('click', function (event) {
        var button = isActionButton(event.target);
        if (button) remember(button);
    }, true);

    document.addEventListener('submit', function (event) {
        var button = isActionButton(event.submitter);
        if (!button && event.target) {
            button = Array.prototype.find.call(
                event.target.querySelectorAll('button, input[type="submit"]'),
                function (candidate) { return !!isActionButton(candidate); }
            );
        }
        if (button) remember(button);
    }, true);

    $(document).ajaxSend(function (_event, xhr, settings) {
        var method = String(settings.type || settings.method || 'GET').toUpperCase();
        var button = pendingButton;

        if (!/^(POST|PUT|PATCH)$/.test(method)) return;
        if (!button || Date.now() - pendingAt > 2000 || !document.contains(button)) return;

        pendingButton = null;
        startLoading(button);
        requestButtons.set(xhr, button);
    });

    $(document).ajaxComplete(function (_event, xhr) {
        var button = requestButtons.get(xhr);
        if (!button) return;

        stopLoading(button);
        requestButtons.delete(xhr);
    });

    // Dapat dipakai oleh kode baru/non-AJAX bila diperlukan.
    window.ButtonLoading = {
        start: startLoading,
        stop: stopLoading
    };
})(window, document, window.jQuery);
