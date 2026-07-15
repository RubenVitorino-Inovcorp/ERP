<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Models\User;
use App\Traits\Searchable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    use Searchable;

    public function index(Request $request): Response
    {
        $query = User::with('roles');

        $users = $this->applyDataTable(
            query: $query,
            request: $request,
            searchableFields: ['name'],
            blindIndexFields: ['email' => 'email_index', 'phone' => 'phone_index'],
            sortableFields: ['name', 'created_at'],
            defaultSort: 'name',
            defaultDirection: 'asc'
        );

        return Inertia::render('Users/Index', [
            'users' => $users,
            'filters' => $this->getDataTableFilters($request),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Users/Create', [
            'roles' => Role::all(),
        ]);
    }

    public function store(StoreUserRequest $request): RedirectResponse
    {
        $data = $request->validated();

        $randomPassword = Str::random(12);
        $data['password'] = Hash::make($randomPassword);

        $user = User::create($data);

        if ($request->has('roles')) {
            $user->syncRoles($request->roles);
        }

        // TODO: Enviar email com a password

        return redirect()->route('users.index')->with('success', 'Utilizador criado com sucesso. (Password gerada automaticamente)');
    }

    public function show(User $user)
    {
        return redirect()->route('users.index');
    }

    public function edit(User $user): Response
    {
        $user->load('roles');

        return Inertia::render('Users/Edit', [
            'user' => $user,
            'roles' => Role::all(),
        ]);
    }

    public function update(UpdateUserRequest $request, User $user): RedirectResponse
    {
        $user->update($request->validated());

        if ($request->has('roles')) {
            $user->syncRoles($request->roles);
        } else {
            $user->syncRoles([]);
        }

        return redirect()->route('users.index')->with('success', 'Utilizador atualizado com sucesso.');
    }

    public function destroy(User $user): RedirectResponse
    {
        if (auth()->id() === $user->id) {
            return redirect()->route('users.index')->with('error', 'Não podes eliminar a tua própria conta.');
        }

        try {
            $user->delete();
        } catch (\Illuminate\Database\QueryException $e) {
            if ($e->getCode() == "23000") {
                return redirect()->route('users.index')->with('error', 'Não é possível eliminar este utilizador pois tem registos associados no sistema.');
            }
            return redirect()->route('users.index')->with('error', 'Ocorreu um erro ao eliminar o utilizador.');
        }

        return redirect()->route('users.index')->with('success', 'Utilizador eliminado com sucesso.');
    }
}
