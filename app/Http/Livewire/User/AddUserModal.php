<?php

namespace App\Http\Livewire\User;

use App\Helpers\ActivityLogger;
use App\Models\User;
use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;

class AddUserModal extends Component
{
    use WithFileUploads;

    public $user_id;
    public $name;
    public $email;
    public $role;
    public $avatar;
    public $saved_avatar;

    public $edit_mode = false;
    public $password;
    public $is_active = true;


    // protected $rules = [
    //     'name' => 'required|string',
    //     'email' => 'required|email',
    //     'role' => 'required|string',
    //     'avatar' => 'nullable|sometimes|image|max:1024',
    //     'password' => 'nullable|min:6',
    //     'is_active' => 'boolean'
    // ];

    protected $listeners = [
        'delete_user' => 'deleteUser',
        'update_user' => 'updateUser',
    ];

    public function rules()
    {
        return [
            // 'name' => 'required|string',
            'name' => 'required|string|unique:users,name,' . ($this->user_id ?? 'NULL'),
            'email' => 'email|unique:users,email,' . ($this->user_id ?? 'NULL'),
            'role' => 'required|string',
            'avatar' => 'nullable|sometimes|image|max:1024',
            'password' => 'nullable|min:6',
            'is_active' => 'boolean',
        ];
    }

    public function render()
    {
        $roles = Role::all();
        $user = Auth::user();

        if ($user->hasRole('administrator')) {
            $roles = Role::all();
        } else {
            $roles = Role::whereNotIn('name', ['administrator'])->get();
        }

        $roles_description = [
            'administrator' => 'All Access System',
            'leader' => 'Leader Of Team',
            'marketing' => 'Staff Of Marketing'
        ];

        foreach ($roles as $i => $role) {
            $roles[$i]->description = $roles_description[$role->name] ?? '';
        }

        if (!$this->edit_mode) {
            $this->reset();
        }

        return view('livewire.user.add-user-modal', compact('roles'));
    }

    public function submit()
    {
        $this->validate();

        DB::transaction(function () {
            $username   = strtolower(preg_replace('/\s+/', '', trim($this->name)));

            $data = [
                // 'name' => strtolower($this->name),
                'name' => $username,
                'email' => $this->email,
                'is_active' => $this->is_active,
                'profile_photo_path' => $this->avatar
                    ? $this->avatar->store('avatars', 'public')
                    : null,
            ];

            if (!$this->edit_mode) {
                if (User::where('email', $this->email)->exists()) {
                    $this->emit('error', __('Email already exists!'));
                    return;
                }

                $data['password'] = Hash::make($this->password ?: $this->email);

                $user = User::create(array_merge($data, [
                    'email' => $this->email,
                ]));

                $user->assignRole($this->role);

                $this->emit('success', __('New user created'));
                ActivityLogger::log("New user created: {$user->email}", method: 'POST', statusCode: 201);

            } else {
                $user = User::findOrFail($this->user_id);
                // $user = User::where('email', $this->email)->firstOrFail();

                if (!empty($this->password)) {
                    $data['password'] = Hash::make($this->password);
                }

                $user->update($data);

                $user->syncRoles($this->role);

                $this->emit('success', __('User updated'));
                ActivityLogger::log("User updated: {$user->email}", method: 'PUT', statusCode: 200);
            }
        });

        $this->reset();
    }


    // public function submit()
    // {
    //     // Validate the form input data
    //     $this->validate();

    //     DB::transaction(function () {
    //         // Prepare the data for creating a new user
    //         $data = [
    //             'name' => $this->name,
    //         ];

    //         if ($this->avatar) {
    //             $data['profile_photo_path'] = $this->avatar->store('avatars', 'public');
    //         } else {
    //             $data['profile_photo_path'] = null;
    //         }

    //         if (!$this->edit_mode) {
    //             $data['password'] = Hash::make($this->password ?: $this->email);
    //         } elseif (!empty($this->password)) {
    //             $data['password'] = Hash::make($this->password);
    //         }

    //         // Create a new user record in the database
    //         $user = User::updateOrCreate([
    //             'email' => $this->email,
    //         ], $data);

    //         if ($this->edit_mode) {
    //             // Assign selected role for user
    //             $user->syncRoles($this->role);

    //             // Emit a success event with a message
    //             $this->emit('success', __('User updated'));
    //             ActivityLogger::log("User updated: {$user->email}");
    //         } else {
    //             // Assign selected role for user
    //             $user->assignRole($this->role);

    //             // Send a password reset link to the user's email
    //             // Password::sendResetLink($user->only('email'));

    //             // Emit a success event with a message
    //             $this->emit('success', __('New user created'));
    //             ActivityLogger::log("New user created: {$user->email}");
    //         }
    //     });

    //     // Reset the form fields after successful submission
    //     $this->reset();
    // }

    public function deleteUser($id)
    {
        // Prevent deletion of current user
        if ($id == Auth::id()) {
            $this->emit('error', 'User cannot be deleted');
            return;
        }

        // Delete the user record with the specified ID
        User::destroy($id);

        ActivityLogger::log("User deleted with ID: {$id}", method: 'DELETE', statusCode: 200);
        // Emit a success event with a message
        $this->emit('success', 'User successfully deleted');
    }

    public function updateUser($id)
    {
        $this->edit_mode = true;

        $user = User::find($id);

        $this->user_id      = $user->id;
        $this->saved_avatar = $user->profile_photo_url;
        $this->name = $user->name;
        $this->email = $user->email;
        $this->role = $user->roles?->first()->name ?? '';
        $this->is_active    = (bool) $user->is_active;
        $this->password     = ''; // blank input on edit
    }

    public function hydrate()
    {
        $this->resetErrorBag();
        $this->resetValidation();
    }
}
