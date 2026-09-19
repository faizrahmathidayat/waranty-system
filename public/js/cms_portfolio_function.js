$(function () {
    $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') } });

    var routes = window.CMS_PORTFOLIO_ROUTES;
    var isFormMode = window.CMS_PORTFOLIO_IS_CREATE || !!window.CMS_PORTFOLIO_EDIT_ID;
    var quill = null;

    if (isFormMode) {
        $('#cms-portfolio-list-panel').hide();
        $('#cms-portfolio-form-panel').show();
        quill = new Quill('#quill-body-editor', { theme: 'snow' });
    } else {
        initDataTable();
    }

    if (window.CMS_PORTFOLIO_EDIT_ID) {
        loadItemForEdit(window.CMS_PORTFOLIO_EDIT_ID);
    }

    $('#title').on('input', function () {
        if (!window.CMS_PORTFOLIO_EDIT_ID) {
            $('#slug').val(slugify($(this).val()));
        }
    });

    $('#form-portfolio').on('submit', function (e) {
        e.preventDefault();
        $('#body').val(quill.root.innerHTML);

        var formData = new FormData(this);
        var id = $('#portfolio_id').val();
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
                toastr.success('Proyek berhasil disimpan.');
                window.location.href = routes.index;
            },
            error: function (xhr) {
                if (xhr.status === 422) {
                    var errors = xhr.responseJSON.errors || {};
                    var firstMessage = Object.values(errors)[0];
                    toastr.error(firstMessage ? firstMessage[0] : 'Data tidak valid.');
                } else {
                    toastr.error('Gagal menyimpan proyek.');
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
        $('#table-portfolio').DataTable({
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
                            '<button type="button" class="btn btn-sm btn-danger btn-delete-item" data-id="' + id + '"><i class="fas fa-trash"></i></button>';
                    },
                },
            ],
        });
    }

    $(document).on('click', '.btn-delete-item', function () {
        var id = $(this).data('id');

        Swal.fire({
            title: 'Hapus Proyek',
            text: 'Yakin ingin menghapus proyek ini?',
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
                    toastr.success('Proyek dihapus.');
                    $('#table-portfolio').DataTable().ajax.reload();
                },
                error: function () {
                    toastr.error('Gagal menghapus proyek.');
                },
            });
        });
    });

    function loadItemForEdit(id) {
        $('#cms-portfolio-form-title').text('Edit Proyek');
        $('#portfolio_id').val(id);

        $.getJSON(routes.showTemplate.replace('__ID__', id), function (item) {
            $('#title').val(item.title);
            $('#slug').val(item.slug);
            $('#excerpt').val(item.excerpt);
            $('#category').val(item.category);
            $('#location').val(item.location);
            $('#status').val(item.status);
            $('#show_on_glosspro').prop('checked', !!item.show_on_glosspro);
            $('#show_on_lexent').prop('checked', !!item.show_on_lexent);
            quill.root.innerHTML = item.body || '';

            var $gallery = $('#cms-existing-gallery').empty();
            (item.media || []).forEach(function (media) {
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
