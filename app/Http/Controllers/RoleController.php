<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreRoleRequest;
use App\Http\Requests\UpdateRoleRequest;
use App\Models\Role;
use App\Traits\Searchable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\Permission\Models\Permission;

class RoleController extends Controller
{
    use Searchable;

    public function index(Request $request): Response
    {
        $query = Role::withCount('users');

        $roles = $this->applyDataTable(
            query: $query,
            request: $request,
            searchableFields: ['name'],
            blindIndexFields: [],
            sortableFields: ['name', 'created_at'],
            defaultSort: 'name',
            defaultDirection: 'asc'
        );

        return Inertia::render('Roles/Index', [
            'roles' => $roles,
            'filters' => $this->getDataTableFilters($request),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Roles/Create', [
            'permissions' => Permission::all(),
        ]);
    }

    public function store(StoreRoleRequest $request): RedirectResponse
    {
        $role = Role::create([
            'name' => $request->name,
            'status' => $request->status ?? true,
        ]);

        if ($request->has('permissions')) {
            $permissions = collect($request->permissions)->map(function ($name) {
                return \Spatie\Permission\Models\Permission::firstOrCreate(['name' => $name]);
            })->pluck('name');
            $role->syncPermissions($permissions);
        }

        return redirect()->route('roles.index')->with('success', 'Grupo de permissões criado com sucesso.');
    }

    public function show(Role $role)
    {
        return redirect()->route('roles.index');
    }

    public function edit(Role $role): Response
    {
        $role->load('permissions');

        return Inertia::render('Roles/Edit', [
            'role' => $role,
            'permissions' => Permission::all(),
        ]);
    }

    public function update(UpdateRoleRequest $request, Role $role): RedirectResponse
    {
        $role->update([
            'name' => $request->name,
            'status' => $request->status ?? true,
        ]);

        if ($request->has('permissions')) {
            $permissions = collect($request->permissions)->map(function ($name) {
                return \Spatie\Permission\Models\Permission::firstOrCreate(['name' => $name]);
            })->pluck('name');
            $role->syncPermissions($permissions);
        } else {
            $role->syncPermissions([]);
        }

        return redirect()->route('roles.index')->with('success', 'Grupo de permissões atualizado com sucesso.');
    }

    public function destroy(Role $role): RedirectResponse
    {
        // Impede de eliminar grupos com utilizadores associados
        if ($role->users()->count() > 0) {
            return redirect()->route('roles.index')->with('error', 'Não é possível eliminar este grupo porque existem utilizadores associados.');
        }

        $role->delete();

        return redirect()->route('roles.index')->with('success', 'Grupo de permissões eliminado com sucesso.');
    }
}
