<div class="modal fade" id="modal_detail_product" data-backdrop="static" data-bs-keyboard="false" aria-labelledby="staticBackdropLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">

            <form method="post" class="form-edit-product" id="form-product" action="">
                @csrf

                <div class="modal-header bg-success">
                    <h5 class="modal-title" id="staticBackdropLabel">
                        <i class="nav-icon fas fa-box-open"></i> Detail Product
                    </h5>

                    <button type="button"
                        class="close"
                        data-dismiss="modal"
                        id="close_modal_detail_product"
                        aria-label="Close">

                        <span aria-hidden="true">&times;</span>

                    </button>
                </div>

                <div class="modal-body">

                    <table class="table">

                        <input type="hidden"
                            name="id_product"
                            id="id_product"
                            value="">

                        <tr>

                            <td style="font-size:15px;font-weight:bold;">
                                Nama Product
                            </td>

                            <td>

                                <input type="text"
                                    id="nama_produk_detail"
                                    name="nama_produk_detail"
                                    class="form-control"
                                    autocomplete="off">

                                <div id="nama_produk_detail_notif"
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
                                    id="brand_detail"
                                    name="brand_detail"
                                    class="form-control"
                                    autocomplete="off">

                                <div id="brand_detail_notif"
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
                                    id="jenis_detail"
                                    name="jenis_detail"
                                    class="form-control"
                                    autocomplete="off">

                                <div id="jenis_detail_notif"
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
                                    id="masa_garansi_bulan_detail"
                                    name="masa_garansi_bulan_detail"
                                    class="form-control"
                                    autocomplete="off">

                                <div id="masa_garansi_bulan_detail_notif"
                                    class="invalid-feedback">
                                </div>

                            </td>

                        </tr>

                        <tr>

                            <td style="font-size:15px;font-weight:bold;">
                                Product Type
                            </td><td><select id="id_product_type_detail" name="id_product_type_detail" class="form-control select2" style="width:100%"><option value="">-- Pilih Product Type --</option>@foreach($productTypes as $type)<option value="{{ $type->id_product_type }}">{{ $type->name }}</option>@endforeach</select></td>
                        </tr>
                        <tr><td style="font-size:15px;font-weight:bold;">Kode Product</td><td><input type="text" id="kode_produk_detail" name="kode_produk_detail" class="form-control"></td></tr>
                        <tr><td style="font-size:15px;font-weight:bold;">Harga Default</td><td><input type="number" min="0" step="0.01" id="harga_default_detail" name="harga_default_detail" class="form-control"></td></tr>
                        <tr><td style="font-size:15px;font-weight:bold;">Warranty Eligible</td><td><div class="custom-control custom-switch"><input type="checkbox" class="custom-control-input" id="is_warranty_eligible_detail" name="is_warranty_eligible_detail" value="1"><label class="custom-control-label" for="is_warranty_eligible_detail">Eligible</label></div></td></tr>
                        <tr>

                            <td style="font-size:15px;font-weight:bold;">
                                Keterangan
                            </td>

                            <td>

                                <textarea
                                    id="keterangan_detail"
                                    name="keterangan_detail"
                                    class="form-control"
                                    style="height:150px;"
                                    autocomplete="off"></textarea>

                            </td>

                        </tr>

                        <tr>
                            <td style="font-size: 15px; font-weight: bold;">Status</td>
                            <td>
                                <div class="custom-control custom-radio custom-control-inline">
                                    <input type="radio" id="status_enabled" name="status" class="custom-control-input" value="enabled">
                                    <label class="custom-control-label" for="status_enabled">Enabled</label>
                                </div>

                                <div class="custom-control custom-radio custom-control-inline">
                                    <input type="radio" id="status_disabled" name="status" class="custom-control-input" value="disabled">
                                    <label class="custom-control-label" for="status_disabled">Disabled</label>
                                </div>
                            </td>
                        </tr>

                    </table>

                </div>

                <div class="modal-footer bg-light">

                    <button
                        type="button"
                        onclick="UpdateProduct()"
                        id="update_product"
                        class="btn btn-success">

                        <i class="fa fa-save"></i> Update

                    </button>

            </form>

            <button
                type="button"
                id="edit_product"
                class="btn btn-warning">

                <i class="fa fa-edit"></i> Edit

            </button>

            <form method="POST"
                action=""
                class="form-hapus-product">

                @csrf

                <input type="hidden"
                    name="id_product"
                    id="id_product_hapus"
                    value="">

                <button
                    type="button"
                    class="btn btn-danger"
                    id="hapus_product"
                    onclick="HapusProduct()">

                    <i class="fa fa-trash"></i> Hapus

                </button>

            </form>

        </div>

    </div>

</div>
