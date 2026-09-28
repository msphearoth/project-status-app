<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        $this->authorize('viewAny', User::class);

        $users = User::withCount(['createdProjects', 'assignedProjects'])
            ->orderBy('name')
            ->paginate(15);

        return view('users.index', [
            'users' => $users,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        $this->authorize('create', User::class);

        return view('users.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreUserRequest $request): RedirectResponse
    {
        User::create($request->validated());

        return redirect()->route('users.index')
            ->with('status', __('User created successfully.'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(User $user): View
    {
        $this->authorize('update', $user);

        return view('users.edit', [
            'user' => $user,
            'isLastAdmin' => $this->isLastAdmin($user),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateUserRequest $request, User $user): RedirectResponse
    {
        if ($request->validated('role') !== UserRole::Admin->value && $this->isLastAdmin($user)) {
            return back()->withErrors([
                'role' => __('You cannot remove the last remaining admin.'),
            ]);
        }

        $user->update($request->safe()->except('password'));

        if ($request->validated('password')) {
            $user->update(['password' => $request->validated('password')]);
        }

        return redirect()->route('users.index')
            ->with('status', __('User updated successfully.'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user): RedirectResponse
    {
        $this->authorize('delete', $user);

        try {
            $user->delete();
        } catch (QueryException) {
            return back()->with('error', __('This user cannot be deleted because they have created projects or have assignment history. Reassign or delete those projects first.'));
        }

        return redirect()->route('users.index')
            ->with('status', __('User deleted successfully.'));
    }

    private function isLastAdmin(User $user): bool
    {
        return $user->role === UserRole::Admin
            && User::where('role', UserRole::Admin)->count() <= 1;
    }
}
