<div class="modal fade" id="modal_detail_warranty" data-backdrop="static" aria-hidden="true">
    <div class="modal-dialog modal-xl"><div class="modal-content">
        <form method="post" class="form-edit-warranty" id="form-edit-warranty">@csrf
            <div class="modal-header bg-success"><h5 class="modal-title"><i class="fas fa-shield-alt"></i> Detail Warranty <small id="detail_type_label" class="ml-2"></small></h5><button type="button" class="close" data-dismiss="modal"><span>&times;</span></button></div>
            <div class="modal-body">
                <input type="hidden" id="id_warranty" name="id_warranty">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group"><label><b>Kode Warranty</b></label><input type="text" class="form-control" id="kode_warranty_detail" readonly></div>
                        <div class="form-group"><label><b>PIN Warranty</b></label><input type="text" class="form-control" id="pin_warranty_detail" readonly></div>
                        <div class="form-group"><label><b>Customer</b></label><div id="customer_detail_read" class="form-control detail-read"></div><select class="form-control select2 detail-editable" id="id_customer_detail" name="id_customer_detail">@foreach($customers as $c)<option value="{{ $c->id_customer }}">{{ $c->nama_customer }}@if($c->status == 'disabled') (Disabled)@endif</option>@endforeach</select></div>
                        <div class="form-group"><label><b>No. Invoice</b></label><input class="form-control detail-editable" id="no_invoice_detail" name="no_invoice_detail"></div>
                        <div class="form-group"><label><b>Tanggal Pasang</b></label><input type="date" class="form-control detail-editable" id="tanggal_pasang_detail" name="tanggal_pasang_detail"></div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group"><label><b>Installer</b></label><input class="form-control detail-editable" id="installer_detail" name="installer_detail"></div>
                        <div class="form-group"><label><b>Status</b></label><div class="form-control border-0 px-0"><span id="status_badge" class="badge"></span></div></div>
                        <div class="form-group"><label><b>Created by</b></label><input class="form-control" id="create_by_detail" readonly></div>
                        <div class="form-group"><label><b>Catatan</b></label><textarea class="form-control detail-editable" id="catatan_detail" name="catatan_detail" rows="3"></textarea></div>
                    </div>
                </div>
                <div id="detail_vehicle" class="detail-type-section"><hr><h6>Data Kendaraan</h6><div class="row"><div class="col-md-4"><input class="form-control detail-editable" id="no_polisi_detail" name="no_polisi_detail" placeholder="No Polisi"></div><div class="col-md-4"><input class="form-control detail-editable" id="merk_mobil_detail" name="merk_mobil_detail" placeholder="Merk Mobil"></div><div class="col-md-4"><input class="form-control detail-editable" id="tipe_mobil_detail" name="tipe_mobil_detail" placeholder="Tipe Mobil"></div><div class="col-md-6 mt-2"><input class="form-control detail-editable" id="warna_mobil_detail" name="warna_mobil_detail" placeholder="Warna Mobil"></div><div class="col-md-6 mt-2"><input class="form-control detail-editable" id="tahun_mobil_detail" name="tahun_mobil_detail" placeholder="Tahun Mobil"></div></div></div>
                <div id="detail_building" class="detail-type-section"><hr><h6>Data Bangunan</h6><div class="row"><div class="col-md-6"><input class="form-control detail-editable" id="nama_bangunan_detail" name="nama_bangunan_detail" placeholder="Nama Bangunan"></div><div class="col-md-6"><textarea class="form-control detail-editable" id="alamat_bangunan_detail" name="alamat_bangunan_detail" placeholder="Alamat"></textarea></div></div></div>
                <hr><div class="d-flex align-items-center"><h6 class="mb-0">Warranty Items</h6><button type="button" id="add-detail-item" class="btn btn-sm btn-success ml-auto detail-edit-only"><i class="fa fa-plus"></i> Tambah Item</button></div>
                <div class="table-responsive mt-3"><table class="table table-bordered table-sm"><thead class="bg-light" id="detail-items-head"></thead><tbody id="detail-items"></tbody></table></div>
            </div>
            <div class="modal-footer bg-light"><button type="button" id="btn_show_qrcode" class="btn btn-info" onclick="ShowQRCode()"><i class="fas fa-qrcode"></i> QR Code</button><a href="#" target="_blank" id="btn_digital_warranty" class="btn btn-success"><i class="fas fa-id-card"></i> Digital Warranty</a><button type="button" onclick="UpdateWarranty()" id="btn_update_warranty" class="btn btn-success detail-edit-only"><i class="fas fa-save"></i> Update</button><button type="button" id="btn_edit_warranty" class="btn btn-warning"><i class="fas fa-edit"></i> Edit</button></form><form class="form-void-warranty">@csrf<input type="hidden" id="id_warranty_void" name="id_warranty_void"><button type="button" class="btn btn-danger" id="btn_void_warranty" onclick="VoidWarranty()"><i class="fas fa-ban"></i> Void</button></form></div>
    </div></div>
</div>
