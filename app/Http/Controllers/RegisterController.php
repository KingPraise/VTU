<?php
namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class RegisterController extends Controller
{
    public function create()
    {
        return view('session.register');
    }

    public function store()
    {
        // Validate incoming user registration request
        $attributes = request()->validate([
            'name' => ['required', 'max:50'],
            'email' => ['required', 'email', 'max:50', Rule::unique('users', 'email')],
            'password' => ['required', 'min:5', 'max:20'],
            'agreement' => ['accepted'],
        ]);

        // Hash the password before saving it
        $attributes['password'] = bcrypt($attributes['password']);

        // Create the user
        $user = User::create($attributes);

        // Automatically assign the "user" role to the newly registered account
        $userRole = Role::where('name', 'user')->first(); // Make sure the "user" role exists
        $user->roles()->attach($userRole); // Attach the "user" role to the newly created user

        // Log in the user and redirect to the dashboard
        Auth::login($user);
        session()->flash('success', 'Your account has been created and assigned a User role.');

        return redirect('/dashboard');
    }
}