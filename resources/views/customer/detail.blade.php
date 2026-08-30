 <div class="modal fade" id="modal_detail_customer" data-backdrop="static" data-bs-keyboard="false" aria-labelledby="staticBackdropLabel" aria-hidden="true">
   <div class="modal-dialog modal-lg">
     <div class="modal-content">
       <form method="post" class="form-edit-customer" id="form-user" action="">
         @csrf
         <div class="modal-header bg-success">
           <h5 class="modal-title " id="staticBackdropLabel"><i class="fa fa-users"></i> Detail Customer </h5>
           <button type="button" class="close" data-dismiss="modal" id="close_modal_detail_customer" aria-label="Close">
             <span aria-hidden="true">&times;</span>
           </button>
         </div>
         <div class="modal-body">
           <table class="table">
             <input type="hidden" name="id_customer" id="id_customer" value="">

             <tr>
               <td style="font-size: 15px; font-weight: bold;">Nama Customer</td>
               <td>
                 <input type="text" id="nama_customer_detail" name="nama_customer_detail" class="form-control" required="" autocomplete="off" value="">
                 <div id="nama_customer_detail_notif" class="invalid-feedback">
                 </div>
               </td>
             </tr>

             <tr>
               <td style="font-size: 15px; font-weight: bold;">No Handphone</td>
               <td>
                 <input type="number" id="no_hp_detail" name="no_hp_detail" class="form-control" required="" autocomplete="off" value="">
                 <div id="no_hp_detail_notif" class="invalid-feedback">
                 </div>
               </td>
             </tr>

             <tr>
               <td style="font-size: 15px; font-weight: bold;">Email</td>
               <td>
                 <input type="text" id="email_detail" name="email_detail" class="form-control" required="" autocomplete="off" value="">
                 <div id="email_detail_notif" class="invalid-feedback">
                 </div>
               </td>
             </tr>

             <tr>
               <td style="font-size: 15px; font-weight: bold;">Alamat</td>
               <td><textarea name="alamat_detail" id="alamat_detail" value="" class="form-control" style="height: 150px;" autocomplete="off"></textarea>
               </td>
             </tr>

             <tr>
               <td style="font-size: 15px; font-weight: bold;">Status</td>
               <td>
                 <input type="hidden" name="status" id="status_customer_value" value="enabled">
                 <div class="custom-control custom-switch">
                   <input type="checkbox" id="status_customer" class="custom-control-input" checked>
                   <label class="custom-control-label" for="status_customer" id="status_customer_label">Active</label>
                 </div>
               </td>
             </tr>

             <tr>
               <td colspan="2">
                 <label class="font-weight-bold mb-0">Vehicle</label>
                 <div id="vehicleRowsExistingDetail" class="mt-2"></div>
               </td>
             </tr>

             <tr>
               <td colspan="2">
                 <div class="d-flex justify-content-between align-items-center">
                   <label class="font-weight-bold mb-0">Tambah Vehicle Baru</label>
                   <button type="button" class="btn btn-sm btn-primary" id="addVehicleRowDetail" disabled>+ Tambah Vehicle</button>
                 </div>
                 <div id="vehicleRowsDetail" class="mt-2"></div>
               </td>
             </tr>

             <tr>
               <td colspan="2">
                 <label class="font-weight-bold mb-0">Building</label>
                 <div id="buildingRowsExistingDetail" class="mt-2"></div>
               </td>
             </tr>

             <tr>
               <td colspan="2">
                 <div class="d-flex justify-content-between align-items-center">
                   <label class="font-weight-bold mb-0">Tambah Building Baru</label>
                   <button type="button" class="btn btn-sm btn-primary" id="addBuildingRowDetail" disabled>+ Tambah Building</button>
                 </div>
                 <div id="buildingRowsDetail" class="mt-2"></div>
               </td>
             </tr>

           </table>

         </div>
         <div class="modal-footer bg-light">
           <button type="button" onclick="UpdateCustomer()" id="update_customer" class="btn btn-update-customer btn-success"><i class="fa fa-save"></i> Update</button>
       </form>
       <button type="button" id="edit_customer" class="btn btn-update-customer btn-warning"><i class="fa fa-edit"></i> Edit</button>

       <form method="POST" action="" class="form-hapus-customer">
         @csrf
         <input type="hidden" name="id_customer" id="id_customer" value="">
         <button type="button" class="btn btn-hapus-customer btn-danger" id="hapus_customer" value="Hapus" onclick="HapusCustomer()">Hapus</button>
       </form>
     </div>
   </div>
 </div>
 </div>
