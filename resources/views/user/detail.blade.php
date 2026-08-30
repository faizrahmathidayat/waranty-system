<div class="modal fade" id="modal_detail_user" data-backdrop="static" data-bs-keyboard="false" aria-labelledby="staticBackdropLabel" aria-hidden="true">

    <div class="modal-dialog modal-lg">

        <div class="modal-content">

            <div class="modal-header bg-success"">

                <h4 class=" modal-title">
                <i class="fa fa-user"></i>
                Detail User
                </h4>

                <button type="button" class="close" data-dismiss="modal" id="close_modal_detail_user">
                    <span>&times;</span>
                </button>

            </div>

            <form class="form-edit-user">

                @csrf

                <input type="hidden" name="user_id" id="user_id">

                <div class="modal-body">

                    <div class="form-group">

                        <label>Nama Lengkap</label>

                        <input
                            type="text"
                            class="form-control"
                            id="name_detail"
                            name="name_detail">

                        <div class="invalid-feedback" id="name_detail_notif"></div>

                    </div>

                    <div class="form-group">

                        <label>Username</label>

                        <input
                            type="text"
                            class="form-control"
                            id="username_detail"
                            name="username_detail">

                        <div class="invalid-feedback" id="username_detail_notif"></div>

                    </div>

                    <div class="form-group">

                        <label>Password</label>

                        <input
                            type="password"
                            class="form-control"
                            id="password_detail"
                            name="password_detail">

                        <small class="text-muted">
                            Kosongkan password jika tidak ingin diubah.
                        </small>

                        <div class="invalid-feedback" id="password_detail_notif"></div>

                    </div>

                    <div class="form-group">

                        <label>Role</label>

                        <!-- <select
                            class="form-control"
                            id="role_detail"
                            name="role_detail">

                            <option value="Super Admin">Super Admin</option>
                            <option value="Admin">Admin</option>
                            <option value="Staff">Staff</option>

                        </select> -->

                        <select class="form-control" id="role_detail" name="role_detail">

                            @if(Auth::user()->role == 'Super Admin')
                            <option value="Super Admin">Super Admin</option>
                            @endif

                            <option value="Admin">Admin</option>
                            <option value="Staff">Staff</option>

                        </select>

                    </div>

                    <div class="form-group">

                        <label>Status</label>

                        <div>

                            <div class="custom-control custom-radio custom-control-inline">

                                <input
                                    type="radio"
                                    id="status_enabled_detail"
                                    name="status_detail"
                                    value="enabled"
                                    class="custom-control-input">

                                <label
                                    class="custom-control-label"
                                    for="status_enabled_detail">

                                    Enabled

                                </label>

                            </div>

                            <div class="custom-control custom-radio custom-control-inline">

                                <input
                                    type="radio"
                                    id="status_disabled_detail"
                                    name="status_detail"
                                    value="disabled"
                                    class="custom-control-input">

                                <label
                                    class="custom-control-label"
                                    for="status_disabled_detail">

                                    Disabled

                                </label>

                            </div>

                        </div>

                    </div>

                </div>

            </form>

            <form class="form-hapus-user">

                @csrf

                <input type="hidden"
                    name="user_id_hapus"
                    id="user_id_hapus">

            </form>

            <div class="modal-footer">

                <button
                    type="button"
                    class="btn btn-warning"
                    id="edit_user">

                    <i class="fa fa-edit"></i>

                    Edit

                </button>

                <button
                    type="button"
                    class="btn btn-success"
                    id="update_user"
                    onclick="UpdateUser()"
                    style="display:none;">

                    <i class="fa fa-save"></i>

                    Update

                </button>

                <button
                    type="button"
                    class="btn btn-danger"
                    id="hapus_user"
                    onclick="HapusUser()">

                    <i class="fa fa-trash"></i>

                    Hapus

                </button>

                <!-- <button
                    type="button"
                    class="btn btn-secondary"
                    id="close_modal_detail_user"
                    data-dismiss="modal">

                    Close

                </button> -->

            </div>

        </div>

    </div>

</div>