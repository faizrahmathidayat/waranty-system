<div class="modal fade" id="modal_tambah_user" data-backdrop="static" data-bs-keyboard="false" aria-labelledby="staticBackdropLabel" aria-hidden="true">

    <div class="modal-dialog modal-lg">

        <div class="modal-content">

            <div class="modal-header bg-info">

                <h4 class="modal-title">
                    <i class="fa fa-user"></i>
                    Tambah User
                </h4>

                <button type="button" class="close btn-danger" data-dismiss="modal">
                    <span>&times;</span>
                </button>

            </div>

            <form class="form-user">

                @csrf

                <div class="modal-body">

                    <div class="form-group">

                        <label>Nama Lengkap</label>

                        <input
                            type="text"
                            class="form-control"
                            id="name"
                            name="name"
                            autocomplete="off">

                        <div class="invalid-feedback" id="name_notif"></div>

                    </div>

                    <div class="form-group">

                        <label>Username</label>

                        <input
                            type="text"
                            class="form-control"
                            id="username"
                            name="username"
                            autocomplete="off">

                        <div class="invalid-feedback" id="username_notif"></div>

                    </div>

                    <div class="form-group">

                        <label>Password</label>

                        <input
                            type="password"
                            class="form-control"
                            id="password"
                            name="password">

                        <div class="invalid-feedback" id="password_notif"></div>

                    </div>

                    <div class="form-group">

                        <label>Role</label>

                        <!-- <select
                            class="form-control"
                            id="role"
                            name="role">

                            <option value="">-- Pilih Role --</option>
                            <option value="Super Admin">Super Admin</option>
                            <option value="Admin">Admin</option>
                            <option value="Staff">Staff</option>

                        </select> -->

                        <select class="form-control" id="role" name="role">

                            <option value="">-- Pilih Role --</option>

                            @if(Auth::user()->role == 'Super Admin')
                            <option value="Super Admin">Super Admin</option>
                            @endif

                            <option value="Admin">Admin</option>
                            <option value="Staff">Staff</option>

                        </select>

                        <div class="invalid-feedback" id="role_notif"></div>

                    </div>

                    <div class="form-group">

                        <label>Status</label>

                        <div>

                            <div class="custom-control custom-radio custom-control-inline">

                                <input
                                    type="radio"
                                    id="status_enabled"
                                    name="status"
                                    value="enabled"
                                    checked
                                    class="custom-control-input">

                                <label
                                    class="custom-control-label"
                                    for="status_enabled">

                                    Enabled

                                </label>

                            </div>

                            <div class="custom-control custom-radio custom-control-inline">

                                <input
                                    type="radio"
                                    id="status_disabled"
                                    name="status"
                                    value="disabled"
                                    class="custom-control-input">

                                <label
                                    class="custom-control-label"
                                    for="status_disabled">

                                    Disabled

                                </label>

                            </div>

                        </div>

                    </div>

                </div>

                <div class="modal-footer">

                    <!-- <button
                        type="button"
                        class="btn btn-secondary"
                        data-dismiss="modal">

                        Close

                    </button> -->

                    <button type="reset" class="btn btn-danger">Reset</button>

                    <button
                        type="button"
                        id="simpan_user"
                        class="btn btn-primary"
                        onclick="SimpanUser()">

                        <i class="fa fa-save"></i>

                        Simpan

                    </button>

                </div>

            </form>

        </div>

    </div>

</div>