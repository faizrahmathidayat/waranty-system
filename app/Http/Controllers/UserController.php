<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Login;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{

    public function index()
    {
        if (empty(Auth::User()->username)) {
            return redirect()->to('/login');
        }

        return view('user.index', [
            'title'     => 'User',
            'navbar'    => 'user',
            'barang'  => Login::OrderBy('user_id')->get()
        ]);
    }

    public function json()
    {
        $query = Login::select(
            'user_id',
            'name',
            'username',
            'role',
            'status'
        )->whereIn('status', ['Enabled', 'Disabled']);

        // Jika bukan Super Admin
        if (Auth::user()->role != 'Super Admin') {

            $query->whereIn('role', ['Admin', 'Staff']);
        }

        $query->orderBy('user_id', 'desc');

        return DataTables::of($query)->make(true);
    }

    public function store(Request $request)
    {
        // Cek username sudah digunakan atau belum
        $cek = Login::where('username', $request->username)->count();

        if ($cek > 0) {

            return response('must_unique');
        }

        Login::create([

            'name'      => $request->name,
            'username'  => $request->username,
            'password'  => Hash::make($request->password),
            'role'      => $request->role,
            'status'    => $request->status

        ]);

        return response('success');
    }

    public function show($id)
    {
        $user = Login::where('user_id', $id)->first();

        return response()->json($user);
    }

    public function update(Request $request)
    {
        $user = Login::where('user_id', $request->user_id)->first();

        if (!$user) {
            return response('not_found');
        }

        // Cek username dipakai user lain
        $cek = Login::where('username', $request->username_detail)
            ->where('user_id', '!=', $request->user_id)
            ->count();

        if ($cek > 0) {
            return response('must_unique');
        }

        $data = [
            'name'      => $request->name_detail,
            'username'  => $request->username_detail,
            'role'      => $request->role_detail,
            'status'    => $request->status_detail,
        ];

        // Password hanya diupdate jika diisi
        if (!empty($request->password_detail)) {
            $data['password'] = Hash::make($request->password_detail);
        }

        $user->update($data);

        return response('success');
    }

    public function destroy(Request $request)
    {
        $user = Login::where('user_id', $request->user_id_hapus)->first();

        if (!$user) {

            return response('NOT_FOUND');
        }

        // Tidak boleh menghapus akun sendiri
        if (session('user_id') == $user->user_id) {

            return response('SELF');
        }

        // Soft Delete (ubah status menjadi Deleted)
        $user->update([
            'status' => 'deleted'
        ]);

        return response('SUCCESS');
    }
}
