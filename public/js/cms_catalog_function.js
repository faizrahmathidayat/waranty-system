$(function () {
    $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') } });

    var routes = window.CMS_CATALOG_ROUTES;
    var isFormMode = window.CMS_CATALOG_IS_CREATE || !!window.CMS_CATALOG_EDIT_ID;
    var quill = null;
    var highlightIndex = 0;

    if (isFormMode) {
        $('#cms-catalog-list-panel').hide();
        $('#cms-catalog-form-panel').show();
        quill = new Quill('#quill-body-editor', { theme: 'snow' });
        addHighlightRow();
    } else {
        initDataTable();
    }

    if (window.CMS_CATALOG_EDIT_ID) {
        loadItemForEdit(window.CMS_CATALOG_EDIT_ID);
    }

    $('#title').on('input', function () {
        if (!window.CMS_CATALOG_EDIT_ID) {
            $('#slug').val(slugify($(this).val()));
        }
    });

    $('#btn-add-highlight').on('click', function () {
        addHighlightRow();
    });

    $(document).on('click', '.remove-highlight-row', function () {
        $(this).closest('.cms-highlight-row').remove();
    });

    $('#form-catalog').on('submit', function (e) {
        e.preventDefault();
        $('#body').val(quill.root.innerHTML);

        var formData = new FormData(this);
        var id = $('#catalog_id').val();
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
                toastr.success('Item katalog berhasil disimpan.');
                window.location.href = routes.index;
            },
            error: function (xhr) {
                if (xhr.status === 422) {
                    var errors = xhr.responseJSON.errors || {};
                    var firstMessage = Object.values(errors)[0];
                    toastr.error(firstMessage ? firstMessage[0] : 'Data tidak valid.');
                } else {
                    toastr.error('Gagal menyimpan item katalog.');
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

    function addHighlightRow(label, value) {
        var index = highlightIndex++;
        var $row = $(
            '<div class="cms-highlight-row">' +
                '<input type="text" class="form-control" name="spec_highlights[' + index + '][label]" placeholder="Label (mis. VLT)" value="' + (label || '') + '">' +
                '<input type="text" class="form-control" name="spec_highlights[' + index + '][value]" placeholder="Nilai (mis. 20%)" value="' + (value || '') + '">' +
                '<i class="fas fa-times remove-highlight-row"></i>' +
            '</div>'
        );
        $('#cms-highlight-rows').append($row);
    }

    function initDataTable() {
        $('#table-catalog').DataTable({
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
            title: 'Hapus Item Katalog',
            text: 'Yakin ingin menghapus item ini?',
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
                    toastr.success('Item katalog dihapus.');
                    $('#table-catalog').DataTable().ajax.reload();
                },
                error: function () {
                    toastr.error('Gagal menghapus item katalog.');
                },
            });
        });
    });

    function loadItemForEdit(id) {
        $('#cms-catalog-form-title').text('Edit Item Katalog');
        $('#catalog_id').val(id);

        $.getJSON(routes.showTemplate.replace('__ID__', id), function (item) {
            $('#title').val(item.title);
            $('#slug').val(item.slug);
            $('#excerpt').val(item.excerpt);
            $('#category').val(item.category);
            $('#status').val(item.status);
            $('#show_on_glosspro').prop('checked', !!item.show_on_glosspro);
            $('#show_on_lexent').prop('checked', !!item.show_on_lexent);
            quill.root.innerHTML = item.body || '';

            $('#cms-highlight-rows').empty();
            var highlights = item.spec_highlights || [];
            if (highlights.length === 0) {
                addHighlightRow();
            } else {
                highlights.forEach(function (row) {
                    addHighlightRow(row.label, row.value);
                });
            }

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
