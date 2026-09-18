$(function () {
    var routes = window.CMS_ARTICLE_ROUTES;
    var isFormMode = window.CMS_ARTICLE_IS_CREATE || !!window.CMS_ARTICLE_EDIT_ID;
    var quill = null;

    if (isFormMode) {
        $('#cms-article-list-panel').hide();
        $('#cms-article-form-panel').show();
        quill = new Quill('#quill-body-editor', { theme: 'snow' });
    } else {
        initDataTable();
    }

    if (window.CMS_ARTICLE_EDIT_ID) {
        loadArticleForEdit(window.CMS_ARTICLE_EDIT_ID);
    }

    $('#title').on('input', function () {
        if (!window.CMS_ARTICLE_EDIT_ID) {
            $('#slug').val(slugify($(this).val()));
        }
    });

    $('#form-article').on('submit', function (e) {
        e.preventDefault();
        $('#body').val(quill.root.innerHTML);

        var formData = new FormData(this);
        var id = $('#article_id').val();
        var url = id ? routes.updateTemplate.replace('__ID__', id) : routes.store;

        if (id) {
            formData.append('_method', 'PUT');
        }

        $.ajax({
            url: url,
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function () {
                toastr.success('Artikel berhasil disimpan.');
                window.location.href = routes.index;
            },
            error: function (xhr) {
                if (xhr.status === 422) {
                    var errors = xhr.responseJSON.errors || {};
                    var firstMessage = Object.values(errors)[0];
                    toastr.error(firstMessage ? firstMessage[0] : 'Data tidak valid.');
                } else {
                    toastr.error('Gagal menyimpan artikel.');
                }
            },
        });
    });

    $(document).on('click', '.remove-existing-image', function () {
        var mediaId = $(this).data('id');
        var $item = $(this).closest('.cms-gallery-item');

        $.ajax({
            url: routes.mediaDestroyTemplate.replace('__ID__', mediaId),
            method: 'DELETE',
            success: function () {
                $item.remove();
                toastr.success('Gambar dihapus.');
            },
            error: function () {
                toastr.error('Gagal menghapus gambar.');
            },
        });
    });

    function initDataTable() {
        $('#table-articles').DataTable({
            processing: true,
            serverSide: true,
            ajax: routes.data,
            columns: [
                { data: 'cover', orderable: false, render: function (url) { return url ? '<img src="' + url + '" class="cms-cover-thumb">' : '-'; } },
                { data: 'title' },
                { data: 'category', defaultContent: '-' },
                { data: 'status' },
                { data: 'sites' },
                { data: 'published_at', defaultContent: '-' },
                {
                    data: 'id', orderable: false,
                    render: function (id) {
                        var editUrl = routes.editTemplate.replace('__ID__', id);
                        return '<a href="' + editUrl + '" class="btn btn-sm btn-info"><i class="fas fa-edit"></i></a> ' +
                            '<button type="button" class="btn btn-sm btn-danger btn-delete-article" data-id="' + id + '"><i class="fas fa-trash"></i></button>';
                    },
                },
            ],
        });
    }

    $(document).on('click', '.btn-delete-article', function () {
        var id = $(this).data('id');

        Swal.fire({
            title: 'Hapus Artikel',
            text: 'Yakin ingin menghapus artikel ini?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Ya, hapus',
        }).then(function (result) {
            if (!result.isConfirmed) {
                return;
            }

            $.ajax({
                url: routes.destroyTemplate.replace('__ID__', id),
                method: 'DELETE',
                success: function () {
                    toastr.success('Artikel dihapus.');
                    $('#table-articles').DataTable().ajax.reload();
                },
                error: function () {
                    toastr.error('Gagal menghapus artikel.');
                },
            });
        });
    });

    function loadArticleForEdit(id) {
        $('#cms-article-form-title').text('Edit Artikel');
        $('#article_id').val(id);

        $.getJSON(routes.showTemplate.replace('__ID__', id), function (article) {
            $('#title').val(article.title);
            $('#slug').val(article.slug);
            $('#excerpt').val(article.excerpt);
            $('#category').val(article.category);
            $('#status').val(article.status);
            $('#show_on_glosspro').prop('checked', !!article.show_on_glosspro);
            $('#show_on_lexent').prop('checked', !!article.show_on_lexent);
            quill.root.innerHTML = article.body || '';

            var $gallery = $('#cms-existing-gallery').empty();
            (article.media || []).forEach(function (media) {
                $gallery.append(
                    '<div class="cms-gallery-item">' +
                        '<img src="' + media.thumbnail_url + '">' +
                        '<span class="remove-image remove-existing-image" data-id="' + media.id + '">&times;</span>' +
                    '</div>'
                );
            });
        });
    }

    function slugify(text) {
        return text
            .toString()
            .toLowerCase()
            .trim()
            .replace(/[^a-z0-9]+/g, '-')
            .replace(/(^-|-$)/g, '');
    }
});
