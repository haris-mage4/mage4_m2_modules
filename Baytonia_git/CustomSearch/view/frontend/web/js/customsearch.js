'use strict';
require(["jquery", "domReady!"], function ($) {
    $(document).ready(function () {
        $('.minisearch .action.search').click(function (e) {
            e.preventDefault();
            var queryString = '';
            queryString = $('#search').val();
            window.location = '/searchcatalog?q=' + queryString;

        });
        $(document).on('keypress', function (e) {
            if (e.which == 13) {
                alert('You pressed enter!');
            }
        });

    });
    $(document).on("click", ".see-all span", function (e) {
        e.preventDefault();
        var queryString = '';
        queryString = $('#search').val();
        window.location = '/searchcatalog?q=' + queryString;
    });
    $(".minisearch").submit(function (event) {
        event.preventDefault();
        var queryString = '';
        queryString = $('#search').val();
        window.location = '/searchcatalog?q=' + queryString;

    });

});
