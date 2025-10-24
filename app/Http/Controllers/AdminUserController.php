<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AdminUserController extends Controller
{

    // Show the form for creating a new user
    public function create()
    {
        // Fetch all roles from the database to display in the select dropdown
        // Fetch all roles from the database to display in the select dropdown
        $roles = Role::all();
        return view('users.create', compact('roles')); // Pass the roles variable to the view
    }

    // Store a newly created user in the database
    public function store(Request $request)
    {
        // Validate the incoming request data
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6|confirmed', // Make sure 'password_confirmation' is present in the form
            'roles' => 'required|array',
            'roles.*' => 'exists:roles,id',
        ]);

        // Create the user
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password),
        ]);

        // Assign roles to the user
        $user->roles()->sync($request->roles);

        // Redirect back with success message
        return redirect()->route('users.index')->with('success', 'User created successfully.');
    }

    // Display a list of all users
    public function index()
    {
        $users = User::latest()->paginate(10); // Paginate users
        return view('users.index', compact('users'));
    }

    // Show form to edit a user
    public function edit($id)
    {
        $user = User::findOrFail($id); // Find the user by ID
        return view('users.edit', compact('user'));
    }

    // Update user information
    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id); // Find the user by ID

        // Validate incoming request data
        $request->validate([
            'name' => 'required|max:50',
            'email' => 'required|email|max:50|unique:users,email,' . $user->id,
            'phone' => 'nullable|max:15',
            'location' => 'nullable|max:100',
            'password' => 'nullable|min:6', // Optional password update
        ]);

        // Update user information
        $user->name = $request->input('name');
        $user->email = $request->input('email');
        $user->phone = $request->input('phone');
        $user->location = $request->input('location');

        // Update password if provided
        if ($request->filled('password')) {
            $user->password = Hash::make($request->input('password'));
        }

        $user->save(); // Save the changes to the database

        return redirect()->route('users.index')->with('success', 'User updated successfully.');
    }

    // Delete a user
    public function destroy($id)
    {
        $user = User::findOrFail($id); // Find the user by ID
        $user->delete(); // Delete the user

        return redirect()->route('users.index')->with('success', 'User deleted successfully.');
    }
}