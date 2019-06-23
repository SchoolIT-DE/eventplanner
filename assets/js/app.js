require('../css/app.scss');

require('bootstrap.native');
let Clipboard = require('clipboard');

/*
 * Polyfill for closest function (thanks, Mozilla! https://developer.mozilla.org/en-US/docs/Web/API/Element/closest#Polyfill)
 */
if (!Element.prototype.matches) {
    Element.prototype.matches = Element.prototype.msMatchesSelector ||
        Element.prototype.webkitMatchesSelector;
}

if (!Element.prototype.closest) {
    Element.prototype.closest = function(s) {
        let el = this;

        do {
            if (el.matches(s)) return el;
            el = el.parentElement || el.parentNode;
        } while (el !== null && el.nodeType === 1);
        return null;
    };
}

document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('[data-trigger="submit"]').forEach(function (el) {
        el.addEventListener('change', function (event) {
            this.closest('form').submit();
        });
    });

    new Clipboard('button[data-clipboard-target]');
});