@extends('layout.layout')
@section('title',$title)
@section('content')
<section class="content" style="padding-top:17px"><div class="container-fluid"><div class="card"><div class="card-header"><button class="btn btn-primary" id="masterAdd"><i class="fas fa-plus"></i> Tambah {{ $title }}</button></div><div class="card-body"><div class="table-responsive"><table id="masterTable" class="table table-bordered table-striped" style="width:100%"><thead class="bg-secondary"><tr><th>No</th>@foreach($master['columns'] as $column)<th>{{ $column['label'] }}</th>@endforeach<th>Action</th></tr></thead></table></div></div></div></div></section>
<div class="modal fade" id="masterModal" data-backdrop="static"><div class="modal-dialog"><div class="modal-content"><form id="masterForm">@csrf<input type="hidden" id="masterId" name="{{ $master['primaryKey'] }}"><div class="modal-header bg-info"><h5 class="modal-title" id="masterTitle">{{ $title }}</h5><button type="button" class="close" data-dismiss="modal">&times;</button></div><div class="modal-body">@foreach($master['fields'] as $field)<div class="form-group"><label>{{ $field['label'] }}</label>@if($field['type']==='textarea')<textarea class="form-control" name="{{ $field['name'] }}" rows="3"></textarea>@elseif($field['type']==='order_category')<select class="form-control" name="{{ $field['name'] }}"><option value="">Pilih Kategori Order</option><option value="AUTOMOTIVE">Automotive</option><option value="BUILDING">Building</option></select>@elseif($field['type']==='select')<select class="form-control select2" name="{{ $field['name'] }}" style="width:100%"><option value="">Pilih {{ $field['label'] }}</option>@foreach($options[$field['options']] as $option)<option value="{{ $option->getKey() }}">{{ $option->nama_customer ?? $option->nama_produk }}</option>@endforeach</select>@elseif(in_array($field['type'],['active','status']))<div class="custom-control custom-switch"><input class="custom-control-input" type="checkbox" id="{{ $field['name'] }}" name="{{ $field['name'] }}" value="1" checked><label class="custom-control-label" for="{{ $field['name'] }}">Active</label></div>@else<input class="form-control" type="{{ $field['type'] }}" name="{{ $field['name'] }}" @if($field['type']==='number') step="0.01" @endif>@endif<div class="invalid-feedback"></div></div>@endforeach</div><div class="modal-footer"><button type="button" class="btn btn-danger" id="masterReset">Reset</button><button type="button" class="btn btn-danger d-none" id="masterCancel">Cancel</button><button type="button" class="btn btn-warning d-none" id="masterEdit">Edit</button><button type="submit" class="btn btn-primary" id="masterSave">Simpan</button></div></form></div></div></div>
<script>window.masterConfig=@json($master);</script>
<script>
$(document).on('submit', '#masterForm', function (event) {
    if (window.masterConfig?.route !== 'vehicle') return;
    var required = {id_customer:'Customer', no_polisi:'No Polisi', merk:'Merk', model:'Model/Tipe', warna:'Warna', tahun:'Tahun'};
    var invalid = false;
    Object.keys(required).forEach(function (name) {
        var field = $('#masterForm [name="'+name+'"]').filter(':enabled');
        if (field.length && !String(field.val() || '').trim()) {
            field.addClass('is-invalid').siblings('.invalid-feedback').text(required[name]+' harus diisi terlebih dahulu.');
            invalid = true;
        }
    });
    if (invalid) { event.preventDefault(); event.stopImmediatePropagation(); }
});
$(document).ajaxSuccess(function (event, xhr, settings) {
    if (String(settings.url || '').indexOf('/vehicle/update') !== -1) $('#masterModal').modal('hide');
});
</script>
@endsection
