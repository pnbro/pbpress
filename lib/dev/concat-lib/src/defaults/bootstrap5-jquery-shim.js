/**
 * Bootstrap 5 jQuery Compatibility Shim
 * 
 * Bridges Bootstrap 3-style jQuery API calls to Bootstrap 5's native API.
 * This includes:
 * 
 * 1. $.fn.modal() → bootstrap.Modal (show/hide/toggle/dispose)
 * 2. $.fn.tab() → bootstrap.Tab (show)
 * 3. $.fn.dropdown() → bootstrap.Dropdown (toggle)
 * 4. $.fn.collapse() → bootstrap.Collapse (show/hide/toggle)
 * 5. $.fn.tooltip() → bootstrap.Tooltip
 * 6. jQuery.now = Date.now (removed in jQuery 4, needed by Summernote)
 * 7. $.fn.modal.Constructor.VERSION (needed by bootstrap-select)
 * 8. $.support.transition (removed in jQuery 4, BS3 plugins need it)
 */
(function ($) {

    // ========================================================
    // jQuery 4.x Polyfills (removed APIs that plugins need)
    // ========================================================

    // jQuery.now was removed in jQuery 4.x, Summernote and other plugins need it
    if (typeof $.now !== 'function') {
        $.now = Date.now;
    }

    // $.support.transition was removed - provide a stub
    if (!$.support) $.support = {};
    if (!$.support.transition) {
        $.support.transition = { end: 'transitionend' };
    }

    // ========================================================
    // Bootstrap 5 Check
    // ========================================================
    if (typeof bootstrap === 'undefined') {
        console.warn('[BS5 Shim] Bootstrap 5 not found, shim skipped');
        return;
    }

    // ========================================================
    // $.fn.modal — BS3 jQuery API → BS5 native bootstrap.Modal
    // ========================================================
    if (!$.fn.modal && bootstrap.Modal) {
        $.fn.modal = function (option) {
            return this.each(function () {
                var el = this;
                var instance = bootstrap.Modal.getInstance(el);

                if (typeof option === 'object' || typeof option === 'undefined') {
                    var options = option || {};
                    if (!instance) {
                        instance = new bootstrap.Modal(el, {
                            backdrop: options.backdrop !== undefined ? options.backdrop : true,
                            keyboard: options.keyboard !== undefined ? options.keyboard : true,
                            focus: options.focus !== undefined ? options.focus : true
                        });
                    }
                    if (options.show !== false) {
                        instance.show();
                    }
                } else if (typeof option === 'string') {
                    if (!instance && option === 'show') {
                        instance = new bootstrap.Modal(el);
                    }
                    if (instance && typeof instance[option] === 'function') {
                        instance[option]();
                    }
                }
            });
        };
        $.fn.modal.Constructor = bootstrap.Modal;
    }

    // ========================================================
    // $.fn.tab — BS3 jQuery API → BS5 native bootstrap.Tab
    // ========================================================
    if (!$.fn.tab && bootstrap.Tab) {
        $.fn.tab = function (option) {
            return this.each(function () {
                var el = this;
                var instance = bootstrap.Tab.getInstance(el);

                if (typeof option === 'string') {
                    if (!instance) {
                        instance = new bootstrap.Tab(el);
                    }
                    if (typeof instance[option] === 'function') {
                        instance[option]();
                    }
                } else {
                    if (!instance) {
                        instance = new bootstrap.Tab(el);
                    }
                    instance.show();
                }
            });
        };
        $.fn.tab.Constructor = bootstrap.Tab;
    }

    // ========================================================
    // $.fn.dropdown — BS3 jQuery API → BS5 native bootstrap.Dropdown
    // ========================================================
    if (!$.fn.dropdown && bootstrap.Dropdown) {
        $.fn.dropdown = function (option) {
            return this.each(function () {
                var el = this;
                var instance = bootstrap.Dropdown.getInstance(el);

                if (typeof option === 'string') {
                    if (!instance) {
                        instance = new bootstrap.Dropdown(el);
                    }
                    if (typeof instance[option] === 'function') {
                        instance[option]();
                    }
                } else {
                    if (!instance) {
                        instance = new bootstrap.Dropdown(el);
                    }
                    instance.toggle();
                }
            });
        };
        $.fn.dropdown.Constructor = bootstrap.Dropdown;
    }

    // ========================================================
    // $.fn.collapse — BS3 jQuery API → BS5 native bootstrap.Collapse
    // ========================================================
    if (!$.fn.collapse && bootstrap.Collapse) {
        $.fn.collapse = function (option) {
            return this.each(function () {
                var el = this;
                var instance = bootstrap.Collapse.getInstance(el);

                if (typeof option === 'object' || typeof option === 'undefined') {
                    var options = option || {};
                    if (!instance) {
                        instance = new bootstrap.Collapse(el, {
                            toggle: options.toggle !== undefined ? options.toggle : true,
                            parent: options.parent || false
                        });
                    }
                } else if (typeof option === 'string') {
                    if (!instance) {
                        instance = new bootstrap.Collapse(el, { toggle: false });
                    }
                    if (typeof instance[option] === 'function') {
                        instance[option]();
                    }
                }
            });
        };
        $.fn.collapse.Constructor = bootstrap.Collapse;
    }

    // ========================================================
    // $.fn.tooltip — BS3 jQuery API → BS5 native bootstrap.Tooltip
    // ========================================================
    if (!$.fn.tooltip && bootstrap.Tooltip) {
        $.fn.tooltip = function (option) {
            return this.each(function () {
                var el = this;
                var instance = bootstrap.Tooltip.getInstance(el);

                if (typeof option === 'object' || typeof option === 'undefined') {
                    if (!instance) {
                        instance = new bootstrap.Tooltip(el, option || {});
                    }
                } else if (typeof option === 'string') {
                    if (instance && typeof instance[option] === 'function') {
                        instance[option]();
                    }
                }
            });
        };
        $.fn.tooltip.Constructor = bootstrap.Tooltip;
    }

    // ========================================================
    // $.fn.popover — BS3 jQuery API → BS5 native bootstrap.Popover
    // ========================================================
    if (!$.fn.popover && bootstrap.Popover) {
        $.fn.popover = function (option) {
            return this.each(function () {
                var el = this;
                var instance = bootstrap.Popover.getInstance(el);

                if (typeof option === 'object' || typeof option === 'undefined') {
                    if (!instance) {
                        instance = new bootstrap.Popover(el, option || {});
                    }
                } else if (typeof option === 'string') {
                    if (instance && typeof instance[option] === 'function') {
                        instance[option]();
                    }
                }
            });
        };
        $.fn.popover.Constructor = bootstrap.Popover;
    }

})(jQuery);
