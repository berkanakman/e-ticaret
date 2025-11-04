<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;

class AdminController extends Controller
{
    public function index(Request $request)
    {
        $query = Admin::with('roles')->orderBy('id', 'desc');

        if ($q = $request->query('q')) {
            $query->where('username', 'like', "%{$q}%")->orWhere('email','like',"%{$q}%");
        }

        $admins = $query->paginate(20);
        return view('admin.admins.index', compact('admins'));
    }

    public function create()
    {
        $roles = Role::where('guard_name','admin')->pluck('name','id');
        return view('admin.admins.create', compact('roles'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'username' => 'required|string|max:255|unique:admins,username',
            'email'    => 'required|email|max:255|unique:admins,email',
            'password' => 'required|string|min:6|confirmed',
            'roles'    => 'nullable|array',
            'roles.*'  => 'exists:roles,id',
            'is_active'=> 'sometimes|boolean',
        ]);

        $admin = Admin::create([
            'username' => $request->username,
            'email'    => $request->email,
            'password' => $request->password,
            'is_active'=> $request->boolean('is_active'),
        ]);

        if ($request->filled('roles')) {
            $roleNames = Role::whereIn('id', $request->roles)->where('guard_name','admin')->pluck('name')->toArray();
            $admin->syncRoles($roleNames);
        }

        return redirect()->route('admin.admins.index')->with('success','Admin oluşturuldu.');
    }

    public function edit(Admin $admin)
    {
        $roles = Role::where('guard_name','admin')->pluck('name','id');
        $adminRoleIds = $admin->roles()->pluck('id')->toArray();
        return view('admin.admins.edit', compact('admin','roles','adminRoleIds'));
    }

    public function update(Request $request, Admin $admin)
    {
        $request->validate([
            'username' => 'required|string|max:255|unique:admins,username,'.$admin->id,
            'email'    => 'required|email|max:255|unique:admins,email,'.$admin->id,
            'password' => 'nullable|string|min:6|confirmed',
            'roles'    => 'nullable|array',
            'roles.*'  => 'exists:roles,id',
            'is_active'=> 'sometimes|boolean',
        ]);

        $data = [
            'username' => $request->username,
            'email'    => $request->email,
            'is_active'=> $request->boolean('is_active'),
        ];

        if ($request->filled('password')) {
            $data['password'] = $request->password;
        }

        $admin->update($data);

        $roleNames = [];
        if ($request->filled('roles')) {
            $roleNames = Role::whereIn('id', $request->roles)->where('guard_name','admin')->pluck('name')->toArray();
        }
        $admin->syncRoles($roleNames);

        return redirect()->route('admin.admins.index')->with('success','Admin güncellendi.');
    }

    public function destroy(Admin $admin)
    {
        if (auth()->guard('admin')->id() === $admin->id) {
            return redirect()->route('admin.admins.index')->with('error','Kendi hesabınızı silemezsiniz.');
        }

        $admin->delete();
        return redirect()->route('admin.admins.index')->with('success','Admin silindi.');
    }
}
