/*
 * Welcome to your app's main JavaScript file!
 *
 * We recommend including the built version of this JavaScript file
 * (and its CSS file) in your base layout (base.html.twig).
 */

// any CSS you require will output into a single css file (app.css in this case)
require('../css/app.css');
require('bootstrap/dist/css/bootstrap.min.css');
require('select2/dist/css/select2.css');

const $ = require('jquery');
require('select2');
require('bootstrap');

const setTags = (e) => {
    const id = e.parentNode.id;

    $.ajax({
        type: 'GET',
        url: `/article/${id}/tags`,
        datatype: 'json',
    }).done(response => {
        const data = response['results'];
        data.forEach(element => {
            let option = new Option(element.text, element.id, true, true);
            e.append(option);
            $('.tags').trigger();
        });
    })
}

$(document).ready(() => {
    let idx = -1;
    let query = '';

    $('.article_regard').submit(function (e) {
        e.preventDefault();
        const $form = $(this);
        const id = $form.children('input[name=article_id]').val();
        $.ajax({
            type: $form.attr('method'),
            url: $form.attr('action'),
            async: true,
            datatype: 'json',
            success: response => {
                $(`#article${id}_rating`).text(response);
            }
        });
    });

    $('.tags').select2({
        placeholder: 'Tags',
        ajax: {
            url: '/tags',
            dataType: 'json'
        }
    });

    $('.article_tags').each(function() {
        setTags($(this)[0]);
    });

    $('.article_like').click(() => {
        toggleRegard(true);
    })

    $('input.autocomplete_input').after(`<ul class="autocomplete_options"></ul>`);

    $('input.autocomplete_input').on('input', function(e) {
        $.ajax({
            type: 'GET',
            url: `/admin/users/emails/?query=${query}`,
            datatype: 'json',
        }).done(data => {

            const $list = $('.autocomplete_options');

            query = $(this).val();
            const height = $(this).css('height');
            const width = $(this).css('width');

            $list.css('height', height);
            $list.css('top', height);
            $list.css('width', width);

            $list.empty();
            if (data.length == 0) {
                $list.append(`<li>Emails not found</li>`);
            } else {
                data.forEach(element => {
                    $list.append(`<li>${element}</li>`);
                });
            }
        });
    });

    $('.autocomplete_input').on('focusout', () => {
        $('.autocomplete_options').css('display', 'none');
    });

    $('.autocomplete_input').on('focus', () => {
        $('.autocomplete_options').css('display', 'block');
    });

    $('.autocomplete_input').on('keyup keypress', e => {
        const lis = $('.autocomplete_options').children();
        switch(event.keyCode) {
            case 40:
                idx++
                break;
            case 38:
                idx--;
                break;
        };
        if ([40, 38].indexOf(event.keyCode) != -1) {
            if (idx >= lis.length) {
                idx = -1;
            } else if (idx < -1) {
                lis = lis.length - 1;
            }
            if (idx == -1) {
                $('.autocomplete_input').val(query);
            } else {
                $('.autocomplete_input').val(lis[idx].innerHTML);
            }
        }
    });
})