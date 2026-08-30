<div class="modal fade" id="modal_tambah_customer" data-backdrop="static" data-bs-keyboard="false" aria-labelledby="staticBackdropLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">


      <div class="modal-header bg-info">
        <h5 class="modal-title" id="staticBackdropLabel"> <i class="fa fa-users"></i> Tambah Customer </h5>
        <button type="button" class="close" data-dismiss="modal" id="close_modal_tambah_user" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <form method="post" class="form-customer" id="form-customer">
          @csrf
          <table class="table">
            <tr>
              <td style="font-size: 15px; font-weight: bold;">Nama Customer</td>
              <td><input type="text" id="nama_customer" name="nama_customer" class="form-control" required="" autocomplete="off" value="">
                <div id="nama_customer_notif" class="invalid-feedback">

                </div>
              </td>
            </tr>

            <tr>
              <td style="font-size: 15px; font-weight: bold;">Nomor Handphone</td>
              <td><input type="number" id="no_hp" name="no_hp" class="form-control" required="" autocomplete="off" value="">
                <div id="no_hp_notif" class="invalid-feedback">
                </div>
              </td>
            </tr>

            <tr>
              <td style="font-size: 15px; font-weight: bold;">Email</td>
              <td><input type="text" id="email" name="email" class="form-control" required="" autocomplete="off" value="">
                <div id="email_notif" class="invalid-feedback">
                </div>
              </td>
            </tr>

            <tr>
              <td style="font-size: 15px; font-weight: bold;">Alamat</td>
              <td><textarea name="alamat" id="alamat" value="" class="form-control" style="height: 150px;" autocomplete="off"></textarea>

              </td>
            </tr>

            <tr>
              <td colspan="2">
                <div class="d-flex justify-content-between align-items-center">
                  <label class="font-weight-bold mb-0">Vehicle Baru</label>
                  <button type="button" class="btn btn-sm btn-primary" id="addVehicleRow">+ Tambah Vehicle</button>
                </div>
                <div id="vehicleRows" class="mt-2"></div>
              </td>
            </tr>

            <tr>
              <td colspan="2">
                <div class="d-flex justify-content-between align-items-center">
                  <label class="font-weight-bold mb-0">Building Baru</label>
                  <button type="button" class="btn btn-sm btn-primary" id="addBuildingRow">+ Tambah Building</button>
                </div>
                <div id="buildingRows" class="mt-2"></div>
              </td>
            </tr>
        </table>

      </div>
      <div class="modal-footer bg-light">
        <button type="reset" class="btn btn-danger">Reset</button>
        </form>
        <button type="button"
          id="simpan_customer"
          onclick="SimpanCustomer()"
          class="btn btn-load btn-primary btn-md tombol-simpan-customer"
          name="simpan_customer">

          <i class="fa fa-save"></i> Simpan

        </button>
      </div>
    </div>
  </div>
</div>