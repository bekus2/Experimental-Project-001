/**
 * Проект: ВайбКод
 * Файл: assets/js/app.js
 * Автор: Beck Sarbassov
 * Версия: 1.2.0
 * Дата выпуска: 2026-06-16
 * Последнее обновление: 2026-06-21
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

    function draftStorageKey(key) {
        return 'vibecode:draft:' + key;
    }

    function clearDrafts($scope) {
        $scope.find('[data-draft-key]').each(function () {
            try {
                localStorage.removeItem(draftStorageKey($(this).data('draft-key')));
            } catch (err) {
                // EN: Draft storage is optional and must not block posting.
                // RU: Хранилище черновиков необязательно и не должно мешать публикации.
            }
        });
    }

    /* ---------- Автосохранение черновиков ---------- */
    $('[data-draft-key]').each(function () {
        const $field = $(this);
        const key = draftStorageKey($field.data('draft-key'));
        const $status = $field.closest('.field').find('.draft-status');
        let timer = null;

        try {
            const saved = localStorage.getItem(key);
            if (saved && !$field.val()) {
                $field.val(saved);
                $status.text('Черновик восстановлен из этого браузера.');
            }
        } catch (err) {
            return;
        }

        $field.on('input', function () {
            clearTimeout(timer);
            timer = setTimeout(function () {
                try {
                    localStorage.setItem(key, $field.val());
                    $status.text('Черновик сохранен автоматически.');
                } catch (err) {
                    $status.text('Черновик не сохранен: браузер ограничил локальное хранилище.');
                }
            }, 420);
        });
    });

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
                topic_type: $form.find('[name="topic_type"]').val(),
                tags: $form.find('[name="tags"]').val(),
                title: $form.find('[name="title"]').val(),
                body: $form.find('[name="body"]').val()
            })
            .done(function (res) {
                if (res.ok) {
                    clearDrafts($form);
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
                    clearDrafts($form);
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
       Избранное, подписки, решения, жалобы и уведомления
       ======================================================== */
    $(document).on('click', '.topic-preference-btn', function () {
        const $btn = $(this);
        const type = $btn.data('type');
        const original = $btn.text();

        $btn.prop('disabled', true);
        post('toggle_topic_preference.php', {
            topic_id: $btn.data('topic-id'),
            type: type
        })
        .done(function (res) {
            if (!res.ok) {
                toast(res.error || 'Не удалось обновить тему.', 'err');
                return;
            }
            $btn.toggleClass('active', !!res.active);
            if (type === 'bookmark') {
                $btn.text(res.active ? 'В закладках' : 'В закладки');
            } else {
                $btn.text(res.active ? 'Подписка включена' : 'Подписаться');
            }
            toast(res.message || 'Готово.', 'ok');
        })
        .fail(function (xhr) {
            const err = (xhr.responseJSON && xhr.responseJSON.error) || 'Ошибка сети.';
            toast(err, 'err');
            $btn.text(original);
        })
        .always(function () {
            $btn.prop('disabled', false);
        });
    });

    $(document).on('click', '.solve-post-btn', function () {
        const $btn = $(this);
        $btn.prop('disabled', true);
        post('mark_solved.php', {
            topic_id: $btn.data('topic-id'),
            post_id: $btn.data('post-id'),
            action: $btn.data('action')
        })
        .done(function (res) {
            if (res.ok) {
                toast(res.message || 'Статус решения обновлен.', 'ok');
                setTimeout(() => window.location.reload(), 500);
            } else {
                toast(res.error || 'Не удалось обновить решение.', 'err');
                $btn.prop('disabled', false);
            }
        })
        .fail(function (xhr) {
            const err = (xhr.responseJSON && xhr.responseJSON.error) || 'Ошибка сети.';
            toast(err, 'err');
            $btn.prop('disabled', false);
        });
    });

    $(document).on('click', '.edit-post-btn', function () {
        const $post = $(this).closest('.post');
        $post.find('.post-content').hide();
        $post.find('.inline-edit-form').removeAttr('hidden').hide().slideDown(160);
        $(this).hide();
    });

    $(document).on('click', '.cancel-edit-btn', function () {
        const $post = $(this).closest('.post');
        $post.find('.inline-edit-form').slideUp(160, function () {
            $(this).attr('hidden', true);
        });
        $post.find('.post-content').show();
        $post.find('.edit-post-btn').show();
    });

    $(document).on('submit', '.inline-edit-form', function (e) {
        e.preventDefault();
        const $form = $(this);
        const $post = $form.closest('.post');
        const $btn = $form.find('button[type="submit"]');
        const $msg = $form.find('.form-msg');
        const original = $btn.html();

        $msg.removeClass('error success').hide();
        $btn.prop('disabled', true).html('<span class="spinner"></span>');
        post('edit_post.php', {
            post_id: $form.data('post-id'),
            body: $form.find('[name="body"]').val()
        })
        .done(function (res) {
            if (res.ok) {
                $post.find('.post-content').html(res.html).show();
                $form.attr('hidden', true).hide();
                $post.find('.edit-post-btn').show();
                toast(res.message || 'Сообщение обновлено.', 'ok');
            } else {
                $msg.addClass('error').text(res.error || 'Не удалось сохранить.').show();
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

    $(document).on('click', '.delete-post-btn', function () {
        if (!window.confirm('Удалить это сообщение? Его текст будет скрыт, но след останется в теме.')) {
            return;
        }
        const $btn = $(this);
        $btn.prop('disabled', true);
        post('delete_post.php', { post_id: $btn.data('post-id') })
        .done(function (res) {
            if (res.ok) {
                toast(res.message || 'Сообщение удалено.', 'ok');
                setTimeout(() => window.location.reload(), 500);
            } else {
                toast(res.error || 'Не удалось удалить сообщение.', 'err');
                $btn.prop('disabled', false);
            }
        })
        .fail(function (xhr) {
            const err = (xhr.responseJSON && xhr.responseJSON.error) || 'Ошибка сети.';
            toast(err, 'err');
            $btn.prop('disabled', false);
        });
    });

    $(document).on('click', '.report-post-btn', function () {
        const reason = window.prompt('Кратко опишите причину жалобы');
        if (!reason || !reason.trim()) return;

        const $btn = $(this);
        $btn.prop('disabled', true);
        post('report_post.php', {
            post_id: $btn.data('post-id'),
            reason: reason.trim()
        })
        .done(function (res) {
            if (res.ok) {
                toast(res.message || 'Жалоба отправлена модераторам.', 'ok');
                $btn.text('Жалоба отправлена');
            } else {
                toast(res.error || 'Не удалось отправить жалобу.', 'err');
                $btn.prop('disabled', false);
            }
        })
        .fail(function (xhr) {
            const err = (xhr.responseJSON && xhr.responseJSON.error) || 'Ошибка сети.';
            toast(err, 'err');
            $btn.prop('disabled', false);
        });
    });

    $(document).on('click', '.moderate-report-btn', function () {
        const $btn = $(this);
        $btn.prop('disabled', true);
        post('moderate_report.php', {
            report_id: $btn.data('report-id'),
            status: $btn.data('status')
        })
        .done(function (res) {
            if (res.ok) {
                toast(res.message || 'Жалоба обновлена.', 'ok');
                $btn.closest('.report-card').fadeOut(180, function () { $(this).remove(); });
            } else {
                toast(res.error || 'Не удалось обновить жалобу.', 'err');
                $btn.prop('disabled', false);
            }
        })
        .fail(function (xhr) {
            const err = (xhr.responseJSON && xhr.responseJSON.error) || 'Ошибка сети.';
            toast(err, 'err');
            $btn.prop('disabled', false);
        });
    });

    $(document).on('click', '.mark-notifications-read-btn, .mark-notification-read-btn', function () {
        const $btn = $(this);
        $btn.prop('disabled', true);
        post('mark_notifications_read.php', {
            notification_id: $btn.data('notification-id') || 0
        })
        .done(function (res) {
            if (res.ok) {
                toast(res.message || 'Уведомления обновлены.', 'ok');
                setTimeout(() => window.location.reload(), 350);
            } else {
                toast(res.error || 'Не удалось обновить уведомления.', 'err');
                $btn.prop('disabled', false);
            }
        })
        .fail(function (xhr) {
            const err = (xhr.responseJSON && xhr.responseJSON.error) || 'Ошибка сети.';
            toast(err, 'err');
            $btn.prop('disabled', false);
        });
    });

    $(document).on('click', '.quote-post-btn', function () {
        const $textarea = $('#reply-form textarea[name="body"]');
        if (!$textarea.length) {
            toast('Ответить можно после входа в тему.', 'err');
            return;
        }
        const quote = $(this).data('quote') || '';
        const current = $textarea.val();
        $textarea.val((current ? current + '\n\n' : '') + '> ' + quote).focus().trigger('input');
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
