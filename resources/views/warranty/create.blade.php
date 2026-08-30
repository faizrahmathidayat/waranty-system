<div class="modal fade" id="modal_tambah_warranty" data-backdrop="static" aria-hidden="true">
    <div class="modal-dialog modal-xl"><div class="modal-content">
        <div class="modal-header bg-info"><h5 class="modal-title"><i class="fas fa-shield-alt"></i> Tambah Warranty</h5><button type="button" class="close" data-dismiss="modal"><span>&times;</span></button></div>
        <form class="form-warranty" id="form-warranty">@csrf
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group"><label>Jenis Warranty</label><select class="form-control" id="warranty_type" name="warranty_type" required><option value="">Pilih Jenis Warranty</option>@foreach($warrantyTypes as $type)<option value="{{ $type->code }}">{{ $type->name }}</option>@endforeach</select></div>
                        <div class="form-group"><label>Customer</label><select class="form-control select2" id="id_customer" name="id_customer" required><option value="">Pilih Customer</option>@foreach($customers_enabled as $customer)<option value="{{ $customer->id_customer }}">{{ $customer->nama_customer }}</option>@endforeach</select></div>
                        <div class="form-group"><label>No. Invoice <small class="text-muted">(opsional)</small></label><input class="form-control" name="no_invoice"></div>
                        <div class="form-group"><label>Tanggal Pasang</label><input class="form-control" type="date" id="tanggal_pasang" name="tanggal_pasang" required></div>
                    </div>
                    <div class="col-md-6"><div class="form-group"><label>Installer</label><input class="form-control" name="installer"></div><div class="form-group"><label>Catatan</label><textarea class="form-control" name="catatan" rows="5"></textarea></div></div>
                </div>
                <div id="vehicle-fields" class="type-fields d-none"><hr><h6>Data Kendaraan</h6><div class="row"><div class="col-md-4"><input class="form-control" name="no_polisi" placeholder="No Polisi"></div><div class="col-md-4"><input class="form-control" name="merk_mobil" placeholder="Merk Mobil"></div><div class="col-md-4"><input class="form-control" name="tipe_mobil" placeholder="Tipe Mobil"></div><div class="col-md-6 mt-2"><input class="form-control" name="warna_mobil" placeholder="Warna"></div><div class="col-md-6 mt-2"><input class="form-control" name="tahun_mobil" placeholder="Tahun"></div></div></div>
                <div id="building-fields" class="type-fields d-none"><hr><h6>Data Bangunan</h6><div class="row"><div class="col-md-6"><input class="form-control" name="nama_bangunan" placeholder="Nama Bangunan"></div><div class="col-md-6"><textarea class="form-control" name="alamat_bangunan" placeholder="Alamat"></textarea></div></div></div>
                <hr><div class="d-flex align-items-center"><h6 class="mb-0">Warranty Item</h6><button type="button" id="add-warranty-item" class="btn btn-sm btn-success ml-auto"><i class="fa fa-plus"></i> Tambah Item</button></div>
                <div id="warranty-items" class="mt-3"></div>
            </div>
            <div class="modal-footer bg-light"><button type="reset" class="btn btn-secondary">Reset</button><button type="button" id="btn_save_warranty" class="btn btn-primary" onclick="SimpanWarranty()"><i class="fa fa-save"></i> Simpan</button></div>
        </form>
        <select id="product-options-template" class="d-none"><option value="">Pilih Product</option>@foreach($products_enabled as $product)<option value="{{ $product->id_product }}" data-garansi="{{ $product->masa_garansi_bulan }}">{{ $product->nama_produk }}</option>@endforeach</select>
    </div></div>
</div>
