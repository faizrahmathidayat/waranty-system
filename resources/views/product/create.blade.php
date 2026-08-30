<div class="modal fade" id="modal_tambah_product" data-backdrop="static" data-bs-keyboard="false" aria-labelledby="staticBackdropLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">

            <div class="modal-header bg-info">
                <h5 class="modal-title" id="staticBackdropLabel"><i class="nav-icon fas fa-box-open"></i> Tambah Product</h5>

                <button type="button"
                    class="close"
                    data-dismiss="modal"
                    id="close_modal_tambah_product"
                    aria-label="Close">

                    <span aria-hidden="true">&times;</span>

                </button>
            </div>

            <div class="modal-body">

                <table class="table">

                    <form method="post"
                        class="form-product"
                        id="form-product">

                        @csrf

                        <tr>

                            <td style="font-size:15px;font-weight:bold;">
                                Nama Product
                            </td>

                            <td>

                                <input type="text"
                                    id="nama_produk"
                                    name="nama_produk"
                                    class="form-control"
                                    autocomplete="off">

                                <div id="nama_produk_notif"
                                    class="invalid-feedback">

                                </div>

                            </td>

                        </tr>

                        <tr>

                            <td style="font-size:15px;font-weight:bold;">
                                Brand
                            </td>

                            <td>

                                <input type="text"
                                    id="brand"
                                    name="brand"
                                    class="form-control"
                                    autocomplete="off">

                                <div id="brand_notif"
                                    class="invalid-feedback">

                                </div>

                            </td>

                        </tr>

                        <tr>

                            <td style="font-size:15px;font-weight:bold;">
                                Jenis
                            </td>

                            <td>

                                <input type="text"
                                    id="jenis"
                                    name="jenis"
                                    class="form-control"
                                    autocomplete="off">

                                <div id="jenis_notif"
                                    class="invalid-feedback">

                                </div>

                            </td>

                        </tr>

                        <tr>

                            <td style="font-size:15px;font-weight:bold;">
                                Masa Garansi (Bulan)
                            </td>

                            <td>

                                <input type="number"
                                    id="masa_garansi_bulan"
                                    name="masa_garansi_bulan"
                                    class="form-control"
                                    autocomplete="off">

                                <div id="masa_garansi_bulan_notif"
                                    class="invalid-feedback">

                                </div>

                            </td>

                        </tr>

                        <tr>

                            <td style="font-size:15px;font-weight:bold;">
                                Product Type
                            </td>
                            <td><select id="id_product_type" name="id_product_type" class="form-control select2" style="width:100%"><option value="">-- Pilih Product Type --</option>@foreach($productTypes as $type)<option value="{{ $type->id_product_type }}">{{ $type->name }}</option>@endforeach</select></td>
                        </tr>
                        <tr><td style="font-size:15px;font-weight:bold;">Kode Product</td><td><input type="text" id="kode_produk" name="kode_produk" class="form-control"></td></tr>
                        <tr><td style="font-size:15px;font-weight:bold;">Harga Default</td><td><input type="number" min="0" step="0.01" id="harga_default" name="harga_default" class="form-control"></td></tr>
                        <tr><td style="font-size:15px;font-weight:bold;">Warranty Eligible</td><td><div class="custom-control custom-switch"><input type="checkbox" class="custom-control-input" id="is_warranty_eligible" name="is_warranty_eligible" value="1" checked><label class="custom-control-label" for="is_warranty_eligible">Eligible</label></div></td></tr>
                        <tr>

                            <td style="font-size:15px;font-weight:bold;">
                                Keterangan
                            </td>

                            <td>

                                <textarea
                                    id="keterangan"
                                    name="keterangan"
                                    class="form-control"
                                    style="height:150px;"
                                    autocomplete="off"></textarea>

                            </td>

                        </tr>

                    </form>

                </table>

            </div>

            <div class="modal-footer bg-light">

                <button type="reset"
                    class="btn btn-danger">

                    Reset

                </button>

                <button
                    type="button"
                    id="simpan_product"
                    onclick="SimpanProduct()"
                    class="btn btn-load btn-primary btn-md tombol-simpan-product"
                    name="simpan_product">

                    <i class="fas fa-save"></i>
                    <span>Simpan</span>

                </button>

            </div>

        </div>
    </div>
</div>
