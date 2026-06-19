/**
 * Проект: ВайбКод
 * Файл: assets/js/app.js
 * Автор: Beck Sarbassov
 * Версия: 1.1.0
 * Дата выпуска: 2026-06-16
 * Последнее обновление: 2026-06-19
 * Авторские права: © Beck Sarbassov. Все права защищены.
 *
 * EN: Controls AJAX forms, topic moderation, profile settings, likes, and editor helpers.
 * RU: Управляет AJAX-формами, модерацией тем, настройками профиля, лайками и редактором.
 */
(function ($) {
    'use strict';

    const CSRF = $('meta[name="csrf-token"]').attr('content') || '';
    const BASE = (function () {
        // assets/js/app.js -> на два уровня вверх корень
        const src = $('script[src*="assets/js/app.js"]').attr('src') || '';
        return src.replace(/assets\/js\/app\.js.*$/, '');
    })();

    function api(path) { return BASE + 'api/' + path; }

    /* ---------- Toast-уведомления ---------- */
    function toast(message, type) {
        const $t = $('<div class="toast"></div>').addClass(type || 'ok').text(message);
        $('#toast-host').append($t);
        // запуск анимации
        requestAnimationFrame(() => $t.addClass('show'));
        setTimeout(() => {
            $t.removeClass('show');
            setTimeout(() => $t.remove(), 350);
        }, 3200);
    }
    window.vibeToast = toast;

    /* ---------- Утилита: AJAX POST с CSRF ---------- */
    function post(path, data) {
        return $.ajax({
            url: api(path),
            method: 'POST',
            dataType: 'json',
            data: $.extend({ csrf_token: CSRF }, data),
            headers: { 'X-CSRF-Token': CSRF }
        });
    }

    /* ========================================================
       Формы авторизации / регистрации (AJAX)
       ======================================================== */
    function handleAuthForm(formSel, endpoint) {
        const $form = $(formSel);
        if (!$form.length) return;

        $form.on('submit', function (e) {
            e.preventDefault();
            const $btn = $form.find('button[type="submit"]');
            const $msg = $form.find('.form-msg');
            const original = $btn.html();

            $msg.removeClass('error success').hide();
            $btn.prop('disabled', true).html('<span class="spinner"></span>');

            post(endpoint, $form.serialize().split('&').reduce((acc, pair) => {
                const [k, v] = pair.split('=');
                acc[decodeURIComponent(k)] = decodeURIComponent((v || '').replace(/\+/g, ' '));
                return acc;
            }, {}))
            .done(function (res) {
                if (res.ok) {
                    $msg.addClass('success').text(res.message || 'Готово!').show();
                    toast(res.message || 'Добро пожаловать!', 'ok');
                    setTimeout(() => { window.location.href = res.redirect || BASE + 'index.php'; }, 600);
                } else {
                    $msg.addClass('error').text(res.error || 'Что-то пошло не так').show();
                    $btn.prop('disabled', false).html(original);
                }
            })
            .fail(function (xhr) {
                const err = (xhr.responseJSON && xhr.responseJSON.error) || 'Ошибка сети. Попробуйте ещё раз.';
                $msg.addClass('error').text(err).show();
                $btn.prop('disabled', false).html(original);
            });
        });
    }

    handleAuthForm('#login-form', 'login.php');
    handleAuthForm('#register-form', 'register.php');

    /* ========================================================
       Настройки профиля
       ======================================================== */
    (function () {
        const $form = $('#profile-settings-form');
        if (!$form.length) return;

        const $bio = $form.find('[name="bio"]');
        const $counter = $('#bio-counter');
        const $color = $form.find('[name="avatar_color"]');
        const $preview = $('.profile-avatar-preview');

        function updatePreview() {
            const color = $color.val();
            $preview.css('--clr', color);
            $counter.text(($bio.val() || '').length);
        }

        $bio.on('input', updatePreview);
        $color.on('input change', updatePreview);
        $('.color-swatch').on('click', function () {
            $color.val($(this).data('color')).trigger('change');
        });

        $form.on('submit', function (e) {
            e.preventDefault();
            const $btn = $form.find('button[type="submit"]');
            const $msg = $form.find('.form-msg');
            const original = $btn.html();

            $msg.removeClass('error success').hide();
            $btn.prop('disabled', true).html('<span class="spinner"></span>');

            post('update_profile.php', {
                bio: $bio.val(),
                avatar_color: $color.val()
            })
            .done(function (res) {
                if (res.ok) {
                    $msg.addClass('success').text(res.message || 'Профиль обновлен.').show();
                    toast(res.message || 'Профиль обновлен.', 'ok');
                    setTimeout(() => { window.location.href = res.redirect || BASE + 'index.php'; }, 700);
                } else {
                    $msg.addClass('error').text(res.error || 'Не удалось сохранить профиль.').show();
                    $btn.prop('disabled', false).html(original);
                }
            })
            .fail(function (xhr) {
                const err = (xhr.responseJSON && xhr.responseJSON.error) || 'Ошибка сети.';
                $msg.addClass('error').text(err).show();
                $btn.prop('disabled', false).html(original);
            });
        });
    })();

    (function () {
        const $form = $('#password-settings-form');
        if (!$form.length) return;

        $form.on('submit', function (e) {
            e.preventDefault();
            const $btn = $form.find('button[type="submit"]');
            const $msg = $form.find('.form-msg');
            const original = $btn.html();

            $msg.removeClass('error success').hide();
            $btn.prop('disabled', true).html('<span class="spinner"></span>');

            post('change_password.php', {
                current_password: $form.find('[name="current_password"]').val(),
                new_password: $form.find('[name="new_password"]').val(),
                confirm_password: $form.find('[name="confirm_password"]').val()
            })
            .done(function (res) {
                if (res.ok) {
                    $msg.addClass('success').text(res.message || 'Пароль обновлен.').show();
                    toast(res.message || 'Пароль обновлен.', 'ok');
                    $form.find('input[type="password"]').val('');
                } else {
                    $msg.addClass('error').text(res.error || 'Не удалось обновить пароль.').show();
                }
            })
            .fail(function (xhr) {
                const err = (xhr.responseJSON && xhr.responseJSON.error) || 'Ошибка сети.';
                $msg.addClass('error').text(err).show();
            })
            .always(function () {
                $btn.prop('disabled', false).html(original);
            });
        });
    })();

    /* ========================================================
       Модерация темы: закрепление и закрытие
       ======================================================== */
    $(document).on('click', '.moderate-topic-btn', function () {
        const $btn = $(this);
        const original = $btn.html();

        $btn.prop('disabled', true).html('<span class="spinner"></span>');
        post('moderate_topic.php', {
            topic_id: $btn.data('topic-id'),
            action: $btn.data('action')
        })
        .done(function (res) {
            if (res.ok) {
                toast(res.message || 'Тема обновлена.', 'ok');
                setTimeout(() => { window.location.reload(); }, 650);
            } else {
                toast(res.error || 'Не удалось обновить тему.', 'err');
                $btn.prop('disabled', false).html(original);
            }
        })
        .fail(function (xhr) {
            const err = (xhr.responseJSON && xhr.responseJSON.error) || 'Ошибка сети.';
            toast(err, 'err');
            $btn.prop('disabled', false).html(original);
        });
    });

    /* ========================================================
       Создание темы (AJAX)
       ======================================================== */
    (function () {
        const $form = $('#new-topic-form');
        if (!$form.length) return;

        $form.on('submit', function (e) {
            e.preventDefault();
            const $btn = $form.find('button[type="submit"]');
            const $msg = $form.find('.form-msg');
            const original = $btn.html();

            $msg.removeClass('error success').hide();
            $btn.prop('disabled', true).html('<span class="spinner"></span> Публикуем…');

            post('create_topic.php', {
                category_id: $form.find('[name="category_id"]').val(),
                title: $form.find('[name="title"]').val(),
                body: $form.find('[name="body"]').val()
            })
            .done(function (res) {
                if (res.ok) {
                    toast('Тема создана 🎉', 'ok');
                    setTimeout(() => { window.location.href = res.redirect; }, 500);
                } else {
                    $msg.addClass('error').text(res.error).show();
                    $btn.prop('disabled', false).html(original);
                }
            })
            .fail(function (xhr) {
                const err = (xhr.responseJSON && xhr.responseJSON.error) || 'Ошибка сети.';
                $msg.addClass('error').text(err).show();
                $btn.prop('disabled', false).html(original);
            });
        });
    })();

    /* ========================================================
       Ответ в теме (AJAX, без перезагрузки)
       ======================================================== */
    (function () {
        const $form = $('#reply-form');
        if (!$form.length) return;

        $form.on('submit', function (e) {
            e.preventDefault();
            const $btn = $form.find('button[type="submit"]');
            const $msg = $form.find('.form-msg');
            const $body = $form.find('[name="body"]');
            const original = $btn.html();

            if (!$body.val().trim()) {
                $msg.addClass('error').text('Сообщение не может быть пустым').show();
                return;
            }

            $msg.removeClass('error success').hide();
            $btn.prop('disabled', true).html('<span class="spinner"></span> Отправка…');

            post('create_post.php', {
                topic_id: $form.find('[name="topic_id"]').val(),
                body: $body.val()
            })
            .done(function (res) {
                if (res.ok && res.html) {
                    const $node = $(res.html).hide();
                    $('#post-list').append($node);
                    $node.fadeIn(350);
                    $body.val('');
                    toast('Ответ опубликован ✨', 'ok');
                    $('html, body').animate({ scrollTop: $node.offset().top - 80 }, 450);
                } else {
                    $msg.addClass('error').text(res.error || 'Не удалось отправить').show();
                }
            })
            .fail(function (xhr) {
                const err = (xhr.responseJSON && xhr.responseJSON.error) || 'Ошибка сети.';
                $msg.addClass('error').text(err).show();
            })
            .always(function () {
                $btn.prop('disabled', false).html(original);
            });
        });
    })();

    /* ========================================================
       Лайки (делегирование, AJAX-тоггл)
       ======================================================== */
    $(document).on('click', '.like-btn', function () {
        const $btn = $(this);
        const postId = $btn.data('post');

        if ($('body').attr('data-logged-in') !== '1') {
            toast('Войдите, чтобы лайкать', 'err');
            setTimeout(() => { window.location.href = BASE + 'login.php'; }, 900);
            return;
        }

        $btn.addClass('pulse');
        setTimeout(() => $btn.removeClass('pulse'), 360);

        post('like.php', { post_id: postId })
        .done(function (res) {
            if (res.ok) {
                $btn.toggleClass('liked', res.liked);
                $btn.find('.like-count').text(res.count);
            } else {
                toast(res.error || 'Ошибка', 'err');
            }
        })
        .fail(function () { toast('Ошибка сети', 'err'); });
    });

    /* ========================================================
       Мини-тулбар markdown в текстовых полях
       ======================================================== */
    $(document).on('click', '.editor-toolbar button', function (e) {
        e.preventDefault();
        const wrap = $(this).data('wrap');
        const $ta = $(this).closest('form').find('textarea[name="body"]')[0];
        if (!$ta) return;

        const start = $ta.selectionStart, end = $ta.selectionEnd;
        const sel = $ta.value.substring(start, end) || 'текст';
        let insert;
        if (wrap === 'code') {
            insert = '```\n' + sel + '\n```';
        } else {
            insert = wrap + sel + wrap;
        }
        $ta.value = $ta.value.substring(0, start) + insert + $ta.value.substring(end);
        $ta.focus();
    });

    /* ========================================================
       Живой поиск на странице поиска (debounce + AJAX)
       ======================================================== */
    (function () {
        const $input = $('#live-search');
        const $results = $('#search-results');
        if (!$input.length) return;

        let timer = null;

        function run(q) {
            if (q.trim().length < 2) {
                $results.html('<div class="empty"><div class="ico">🔎</div><p>Введите минимум 2 символа</p></div>');
                return;
            }
            $results.html('<div class="empty"><span class="spinner"></span></div>');
            $.getJSON(api('search.php'), { q: q })
                .done(function (res) {
                    if (!res.ok) { $results.html('<div class="empty"><p>' + (res.error || 'Ошибка') + '</p></div>'); return; }
                    if (!res.results.length) {
                        $results.html('<div class="empty"><div class="ico">🤷</div><p>Ничего не найдено по «' + $('<i>').text(q).html() + '»</p></div>');
                        return;
                    }
                    const html = res.results.map(function (r) {
                        return '<div class="topic-row"><div class="topic-main">' +
                            '<p class="topic-title"><a href="' + BASE + 'topic.php?id=' + r.id + '">' + r.title + '</a> ' +
                            '<span class="cat-pill">' + r.category + '</span></p>' +
                            '<p class="topic-sub"><span>' + r.snippet + '</span></p></div>' +
                            '<div class="topic-stats"><div><span class="num">' + r.replies + '</span><span class="lbl">ответов</span></div></div>' +
                            '</div>';
                    }).join('');
                    $results.html('<div class="topic-list">' + html + '</div>');
                })
                .fail(function () { $results.html('<div class="empty"><p>Ошибка сети</p></div>'); });
        }

        $input.on('input', function () {
            const q = $(this).val();
            clearTimeout(timer);
            timer = setTimeout(() => run(q), 280);
        });

        if ($input.val().trim()) run($input.val());
    })();

})(jQuery);
