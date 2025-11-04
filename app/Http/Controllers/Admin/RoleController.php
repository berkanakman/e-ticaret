<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleController extends Controller
{
    public function index(Request $request)
    {
        $query = Role::where('guard_name','admin')->orderBy('id','desc');
        if ($q = $request->query('q')) $query->where('name','like',"%{$q}%");
        $roles = $query->paginate(20);
        return view('admin.roles.index', compact('roles'));
    }

    public function create()
    {
        $permissions = Permission::where('guard_name','admin')->orderBy('name')->get();
        return view('admin.roles.create', compact('permissions'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:50|unique:roles,name,NULL,id,guard_name,admin',
            'permissions' => 'nullable|array',
            'permissions.*' => 'exists:permissions,id',
        ]);

        $role = Role::create(['name'=>$request->name,'guard_name'=>'admin']);

        if ($request->filled('permissions')) {
            $permissionNames = Permission::whereIn('id', $request->permissions)->where('guard_name','admin')->pluck('name')->toArray();
            $role->syncPermissions($permissionNames);
        }

        return redirect()->route('admin.roles.index')->with('success','Rol oluşturuldu.');
    }

    public function edit(Role $role)
    {
        abort_if($role->guard_name!=='admin',404);
        $permissions = Permission::where('guard_name','admin')->orderBy('name')->get();
        $rolePermissionIds = $role->permissions()->pluck('id')->toArray();
        return view('admin.roles.edit', compact('role','permissions','rolePermissionIds'));
    }

    public function update(Request $request, Role $role)
    {
        abort_if($role->guard_name!=='admin',404);

        $request->validate([
            'name' => 'required|string|max:50|unique:roles,name,'.$role->id.',id,guard_name,admin',
            'permissions' => 'nullable|array',
            'permissions.*' => 'exists:permissions,id',
        ]);

        $role->name = $request->name;
        $role->save();

        $permissionNames = [];
        if ($request->filled('permissions')) {
            $permissionNames = Permission::whereIn('id', $request->permissions)->where('guard_name','admin')->pluck('name')->toArray();
        }
        $role->syncPermissions($permissionNames);

        return redirect()->route('admin.roles.index')->with('success','Rol güncellendi.');
    }

    public function destroy(Role $role)
    {
        abort_if($role->guard_name!=='admin',404);

        $protected = ['superadmin'];
        if (in_array($role->name, $protected, true)) {
            return redirect()->route('admin.roles.index')->with('error','Bu rol silinemez.');
        }

        $hasAssigned = DB::table('model_has_roles')->where('role_id', $role->id)->where('model_type','App\Models\Admin')->exists();
        if ($hasAssigned) {
            return redirect()->route('admin.roles.index')->with('error','Bu role atanan kullanıcılar var. Önce kullanıcıları değiştirin.');
        }

        $role->delete();
        return redirect()->route('admin.roles.index')->with('success','Rol silindi.');
    }
}
