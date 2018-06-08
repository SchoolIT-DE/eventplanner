require('../css/app.scss');

var $ = require('jquery');
require('bootstrap');
var Clipboard = require('clipboard');

// Make jQuery available
window.$ = $;

+function($) {
    'use strict';

    $(document).ready(function() {
        $('[data-trigger="submit"]').change(function(e) {
            var $this = $(this);
            $this.closest('form').submit();
        });

        new Clipboard('button[data-clipboard-target]');
    });
}(jQuery);