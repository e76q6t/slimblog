$(function() {
    // 記事投稿・編集画面
    (function() {
        var ENTER = 13;
        var keyDownCode = 0;
        var $postForm = $('#post-form');
        var $tagInput = $('#post-tag-input');
        var $tagsDisplay = $('#post-tags');
        var tags = {};

        $tagsDisplay.find('li').each(function(index) {
            var tagName = $(this).text();
            $hiddenInput = $('<input>', {
                class: 'post-tag-input-hidden',
                type: 'hidden',
                name: 'Tag[]',
                val: tagName,
            })
            $postForm.append($hiddenInput);
            tags[tagName] = $hiddenInput;
        });

        $tagInput.on('keydown', function(event) {
            keyDownCode = event.which;
            if (ENTER == keyDownCode) {
                event.preventDefault();
                return false;
            }
        });

        $tagInput.on('keyup', function(event) {
            if (ENTER == event.which && event.which == keyDownCode) {
                var tagName = $tagInput.val();
                if (tagName === '' || tagName in tags) {
                    return false;
                }
                var $tagElement = $('<li>', {
                    class: 'post-tag-delete',
                    html: '<span class="btn btn-primary">' + tagName + '</span>',
                });
                $tagsDisplay.append($tagElement);
                $hiddenInput = $('<input>', {
                    class: 'post-tag-input-hidden',
                    type: 'hidden',
                    name: 'Tag[]',
                    val: tagName,
                })
                $postForm.append($hiddenInput);
                $tagInput.val('');
                tags[tagName] = $hiddenInput;
            }
        });

        $(document).on('click', '.post-tag-delete', function(event) {
            var $this = $(this);
            var tagName = $this.text();
            tags[tagName].remove();
            delete tags[tagName];
            $this.remove();
        });
    })();

    // 記事一覧画面での記事削除
    $(document).on('click', '.post-delete', function(event) {
        if (!confirm('本当に削除してもよろしいですか？')) {
            return;
        }
        var $post = $(this).closest('.post');
        var postID = $post.data('post-id');
        $.ajax({
            type: 'DELETE',
            url: '/admin/posts/' + postID + '/delete',
        }).done(function() {
            $post.remove();
        }).fail(function() {
            alert('削除に失敗しました');
        });
    });

    // 記事投稿・編集画面の画像アップロード関連
    (function() {
        var $images = $('.image-modal .images');
        var $uploadImageButton = $('#upload-image-button');
        var $fileInput = $('#file-input');
        var $content = $('.post-content');

        var selectedImage = null;

        function fetch() {
            $images.html('');
            $.ajax('/admin/images', {
                type: 'GET',
            }).done(function(data) {
                data.images.forEach(function(filename) {
                    $images.append($('<img>', {
                        class: 'image',
                        src: '/img/uploads/thumbnails/' + filename,
                    }));
                });
            }).fail(function() {
                alert('画像の読み込みに失敗しました');
            });
        }

        $('#image-modal-button').on('click', function() {
            fetch();
        });

        $(document).on('click', '.image', function() {
            $images.find('.selected').removeClass('selected');
            var filename = this.src.split('/').pop();
            selectedImage = filename;
            $(this).addClass('selected');
        });

        $('#image-insert-button').on('click', function() {
            if (!selectedImage) {
                return;
            }
            var img = '<img class="img-responsive" src="/img/uploads/' + selectedImage + '">'
            var val = $content.val();
            var pos = $content.get(0).selectionStart;
            var f = val.substring(0, pos);
            var b = val.substring(pos, val.length);
            $content.val(f + img + b);
            selectedImage = null;
        });

        $('#image-delete-button').on('click', function() {
            if (!selectedImage || !confirm('本当に削除してもよろしいですか？')) {
                return;
            }
            $.ajax('/admin/images/delete', {
                type: 'post',
                data: {image: selectedImage}
            }).done(function() {
                fetch();
            }).fail(function() {
                alert('削除に失敗しました');
            });
        });

        $uploadImageButton.on('click', function() {
            $fileInput.click();
        });

        $fileInput.on('change', function() {
            var image = $fileInput.prop('files')[0];
            var fd = new FormData();
            fd.append('image', image);
            $.ajax('/admin/images', {
                type: 'POST',
                data: fd,
                processData: false,
                contentType: false,
            }).done(function() {
                fetch();
            }).fail(function() {
                alert('アップロードに失敗しました');
            });
            $fileInput.val('');
        });
    })();

    // コメントの承認・削除
    (function() {
        var $approveButton = $('.comment-approve-button');
        var $deleteButton = $('.comment-delete-button');

        $approveButton.on('click', function() {
            var $this = $(this);
            var $comment = $this.closest('.comment');
            var commentId = $comment.data('comment-id');
            $.ajax('/admin/comments/' + commentId + '/approve', {
                type: 'POST',
            }).done(function() {
                $comment.remove();
            }).fail(function() {
                alert('承認失敗');
            });
        });

        $deleteButton.on('click', function() {
            if (!confirm('本当に削除してもよろしいですか？')) {
                return false;
            }
            var $this = $(this);
            var $comment = $this.closest('.comment');
            var commentId = $comment.data('comment-id');
            $.ajax('/admin/comments/' + commentId + '/delete', {
                type: 'DELETE',
            }).done(function() {
                $comment.remove();
            }).fail(function() {
                alert('削除失敗');
            });
        });
    })();
});
