<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;

class UserController extends Controller
{
    public function index()
    {
        if (Auth::user()->role !== UserRole::Admin) {
            abort(403);
        }

        $users = User::paginate(10);
        return view('users.index', compact('users'));
    }

    public function create()
    {
        if (Auth::user()->role !== UserRole::Admin) {
            abort(403);
        }

        return view('users.create');
    }

    public function store(Request $request)
    {
        if (Auth::user()->role !== UserRole::Admin) {
            abort(403);
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'role' => ['required', 'string', 'in:admin,user'],
        ]);

        User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => UserRole::from($validated['role']),
            'is_active' => true,
        ]);

        return redirect()->route('users.index')->with('status', 'Użytkownik został utworzony.');
    }

    public function edit(User $user)
    {
        if (Auth::user()->role !== UserRole::Admin) {
            abort(403);
        }

        return view('users.edit', compact('user'));
    }

    public function update(Request $request, User $user)
    {
        if (Auth::user()->role !== UserRole::Admin) {
            abort(403);
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,' . $user->id],
            'role' => ['required', 'string', 'in:admin,user'],
            'password' => ['nullable', 'confirmed', Rules\Password::defaults()],
            'is_active' => ['boolean'],
        ]);

        $user->name = $validated['name'];
        $user->email = $validated['email'];
        $user->role = UserRole::from($validated['role']);
        if (isset($validated['is_active'])) {
            $user->is_active = $validated['is_active'];
        }

        if (!empty($validated['password'])) {
            $user->password = Hash::make($validated['password']);
        }

        $user->save();

        return redirect()->route('users.index')->with('status', 'Dane użytkownika zostały zaktualizowane.');
    }

    public function destroy(User $user)
    {
        if (Auth::user()->role !== UserRole::Admin) {
            abort(403);
        }

        // Nie pozwól adminowi dezaktywować samego siebie
        if ($user->id === Auth::id()) {
            return back()->with('error', 'Nie możesz dezaktywować własnego konta.');
        }

        $user->update(['is_active' => false]);

        return redirect()->route('users.index')->with('status', 'Użytkownik został dezaktywowany.');
    }
}
