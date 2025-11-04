<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;

class PermissionController extends Controller
{
    public function index(Request $request)
    {
        $query = Permission::where('guard_name','admin')->orderBy('name');
        if ($q = $request->query('q')) $query->where('name','like',"%{$q}%");
        $permissions = $query->paginate(30);
        return view('admin.permissions.index', compact('permissions'));
    }

    public function create()
    {
        return view('admin.permissions.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:150|unique:permissions,name,NULL,id,guard_name,admin',
            'description' => 'nullable|string|max:255',
        ]);

        Permission::create(['name'=>$request->name,'guard_name'=>'admin']);
        return redirect()->route('admin.permissions.index')->with('success','Permission oluşturuldu.');
    }

    public function edit(Permission $permission)
    {
        abort_if($permission->guard_name!=='admin',404);
        return view('admin.permissions.edit', compact('permission'));
    }

    public function update(Request $request, Permission $permission)
    {
        abort_if($permission->guard_name!=='admin',404);

        $request->validate([
            'name' => 'required|string|max:150|unique:permissions,name,'.$permission->id.',id,guard_name,admin',
            'description' => 'nullable|string|max:255',
        ]);

        $permission->name = $request->name;
        $permission->save();
        return redirect()->route('admin.permissions.index')->with('success','Permission güncellendi.');
    }

    public function destroy(Permission $permission)
    {
        abort_if($permission->guard_name!=='admin',404);

        $permission->delete();
        return redirect()->route('admin.permissions.index')->with('success','Permission silindi.');
    }
}
