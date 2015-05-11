$(function() {
    (function() {
        var csrfToken = $('meta[name="csrf-token"]').attr('content');
        var $inputCsrfToken = $('<input>', {
            type: 'hidden',
            name: 'csrf_token',
            value: csrfToken
        });
        $('form').append($inputCsrfToken);

        $.ajaxSetup({
            headers: {
                'X-CSRF-Token': csrfToken,
            }
        });
    })();

    // コメント送信
    (function() {
        var $commentForm = $('#comment-form');
        var $usernameInput = $('#comment-form .username');
        var $contentTextArea = $('#comment-form .content');
        var action = $commentForm.attr('action');

        $commentForm.on('submit', function() {
            var username = $usernameInput.val();
            var content = $contentTextArea.val();

            if (username === '' || content === '') {
                alert('お名前とコメントを入力してください。');
                return false;
            }

            $.ajax(action, {
                type: 'POST',
                data: {
                    username: username,
                    content: content,
                }
            }).done(function(data) {
                $usernameInput.val('');
                $contentTextArea.val('');
                $('#comment-send-modal').modal('show');
                $('.comments').append($('<div>', {
                    'class': 'alert alert-warning',
                    'text': '承認待ちのコメント'
                }));

            }).fail(function(data) {
                alert('コメントの送信に失敗しました。');
            });

            return false;
        });
    })();
});
