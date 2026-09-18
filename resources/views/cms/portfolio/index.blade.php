@extends('layout.layout')

@section('title', $title)

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/quill@1.3.7/dist/quill.snow.css">
<style>
    .cms-gallery { display: flex; flex-wrap: wrap; gap: 10px; margin-top: 10px; }
    .cms-gallery-item { position: relative; width: 120px; }
    .cms-gallery-item img { width: 120px; height: 90px; object-fit: cover; border-radius: 4px; border: 1px solid #dee2e6; }
    .cms-gallery-item .remove-image { position: absolute; top: -8px; right: -8px; background: #dc3545; color: #fff; border-radius: 50%; width: 22px; height: 22px; line-height: 22px; text-align: center; cursor: pointer; font-size: 12px; }
    #quill-body-editor { background: #fff; min-height: 260px; }
    .cms-cover-thumb { width: 50px; height: 38px; object-fit: cover; border-radius: 3px; }
</style>
@endpush

@section('content')
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6"><h1>Portofolio</h1></div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">

        {{-- List view --}}
        <div id="cms-portfolio-list-panel" class="card">
            <div class="card-header">
                <a href="{{ route('cms.portfolio.create') }}" class="btn btn-primary btn-sm"><i class="fas fa-plus"></i> Tambah Proyek</a>
            </div>
            <div class="card-body">
                <table id="table-portfolio" class="table table-bordered table-striped" style="width:100%">
                    <thead>
                        <tr>
                            <th>Cover</th>
                            <th>Judul</th>
                            <th>Kategori</th>
                            <th>Status</th>
                            <th>Situs</th>
                            <th>Tanggal Terbit</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>

        {{-- Create/Edit form --}}
        <div id="cms-portfolio-form-panel" class="card" style="display:none;">
            <div class="card-header">
                <h3 class="card-title" id="cms-portfolio-form-title">Tambah Proyek</h3>
            </div>
            <form id="form-portfolio">
                @csrf
                <input type="hidden" id="portfolio_id" name="portfolio_id">
                <div class="card-body">
                    <div class="form-group">
                        <label>Judul</label>
                        <input type="text" class="form-control" id="title" name="title" required>
                    </div>
                    <div class="form-group">
                        <label>Slug</label>
                        <input type="text" class="form-control" id="slug" name="slug" required>
                        <small class="form-text text-muted">Terisi otomatis dari judul, bisa diubah manual.</small>
                    </div>
                    <div class="form-group">
                        <label>Ringkasan (opsional)</label>
                        <textarea class="form-control" id="excerpt" name="excerpt" rows="2" maxlength="500"></textarea>
                    </div>
                    <div class="form-group">
                        <label>Isi</label>
                        <div id="quill-body-editor"></div>
                        <textarea name="body" id="body" style="display:none;"></textarea>
                    </div>
                    <div class="form-group">
                        <label>Kategori (opsional)</label>
                        <input type="text" class="form-control" id="category" name="category">
                    </div>
                    <div class="form-group">
                        <label>Lokasi (opsional)</label>
                        <input type="text" class="form-control" id="location" name="location" placeholder="mis. Jakarta">
                    </div>
                    <div class="form-group">
                        <label>Tampilkan di situs</label><br>
                        <div class="custom-control custom-checkbox custom-control-inline">
                            <input type="checkbox" class="custom-control-input" id="show_on_glosspro" name="show_on_glosspro" value="1">
                            <label class="custom-control-label" for="show_on_glosspro">GlossPro (compro-1)</label>
                        </div>
                        <div class="custom-control custom-checkbox custom-control-inline">
                            <input type="checkbox" class="custom-control-input" id="show_on_lexent" name="show_on_lexent" value="1">
                            <label class="custom-control-label" for="show_on_lexent">LEXENT (compro-2)</label>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Status</label>
                        <select class="form-control" id="status" name="status">
                            <option value="draft">Draft</option>
                            <option value="published">Published</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Gambar</label>
                        <div id="cms-existing-gallery" class="cms-gallery"></div>
                        <input type="file" class="form-control-file mt-2" id="images" name="images[]" multiple accept="image/*">
                        <small class="form-text text-muted">Bisa pilih lebih dari satu gambar. Otomatis diubah ke WebP dan dikompres saat disimpan.</small>
                    </div>
                </div>
                <div class="card-footer">
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Simpan</button>
                    <a href="{{ route('cms.portfolio.index') }}" class="btn btn-default">Batal</a>
                </div>
            </form>
        </div>

    </div>
</section>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/quill@1.3.7/dist/quill.min.js"></script>
<script>
    window.CMS_PORTFOLIO_ROUTES = {
        data: "{{ route('cms.portfolio.data') }}",
        store: "{{ route('cms.portfolio.store') }}",
        index: "{{ route('cms.portfolio.index') }}",
        showTemplate: "{{ route('cms.portfolio.show', ['portfolioItem' => '__ID__']) }}",
        updateTemplate: "{{ route('cms.portfolio.update', ['portfolioItem' => '__ID__']) }}",
        destroyTemplate: "{{ route('cms.portfolio.destroy', ['portfolioItem' => '__ID__']) }}",
        mediaDestroyTemplate: "{{ route('cms.portfolio.media.destroy', ['media' => '__ID__']) }}",
        editTemplate: "{{ route('cms.portfolio.edit', ['portfolioItem' => '__ID__']) }}",
        createUrl: "{{ route('cms.portfolio.create') }}",
    };
    window.CMS_PORTFOLIO_EDIT_ID = @json(request()->routeIs('cms.portfolio.edit') ? request()->route('portfolioItem') : null);
    window.CMS_PORTFOLIO_IS_CREATE = @json(request()->routeIs('cms.portfolio.create'));
</script>
<script src="js/cms_portfolio_function.js?v={{ filemtime(public_path('js/cms_portfolio_function.js')) }}"></script>
@endpush
