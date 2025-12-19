<?php

namespace App\Livewire\Superadmin;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Manajemen Role Pengguna')]
class RoleManagement extends Component
{
    public string $search = '';

    public array $manageableRoles = ['alumni', 'staf', 'dekan'];

    public string $createName = '';

    public string $createEmail = '';

    public string $createRole = 'alumni';

    public string $createPassword = '';

    public ?int $editingUserId = null;

    public string $editName = '';

    public string $editEmail = '';

    public string $editRole = 'alumni';

    public string $editPassword = '';

    public ?int $pendingDeleteId = null;

    public ?string $pendingDeleteName = null;

    public function getManageableUsersProperty()
    {
        return User::query()
            ->whereKeyNot(auth()->id())
            ->whereIn('role', $this->manageableRoles)
            ->when($this->search !== '', function ($query) {
                $query->where(function ($sub) {
                    $sub->where('name', 'like', '%'.$this->search.'%')
                        ->orWhere('email', 'like', '%'.$this->search.'%');
                });
            })
            ->orderBy('name')
            ->get();
    }

    public function createUser(): void
    {
        $data = Validator::make([
            'name' => $this->createName,
            'email' => $this->createEmail,
            'role' => $this->createRole,
            'password' => $this->createPassword,
        ], [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'role' => ['required', Rule::in($this->manageableRoles)],
            'password' => ['nullable', 'string', Password::defaults()],
        ])->validate();

        $password = $data['password'] !== '' ? $data['password'] : Str::password(12);

        $user = User::query()->create([
            'name' => $data['name'],
            'email' => $data['email'],
            'role' => $data['role'],
            'password' => Hash::make($password),
        ]);

        session()->flash('roleStatus', 'Pengguna '.$user->name.' dibuat dengan password: '.$password);

        $this->resetCreateForm();
    }

    public function editUser(int $userId): void
    {
        $user = User::query()
            ->whereKey($userId)
            ->whereKeyNot(auth()->id())
            ->whereIn('role', $this->manageableRoles)
            ->firstOrFail();

        $this->editingUserId = $user->id;
        $this->editName = $user->name;
        $this->editEmail = $user->email;
        $this->editRole = $user->role;
        $this->editPassword = '';
    }

    public function updateUser(): void
    {
        abort_if($this->editingUserId === null, 400);

        $user = User::query()
            ->whereKey($this->editingUserId)
            ->whereIn('role', $this->manageableRoles)
            ->firstOrFail();

        $data = Validator::make([
            'name' => $this->editName,
            'email' => $this->editEmail,
            'role' => $this->editRole,
            'password' => $this->editPassword,
        ], [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'role' => ['required', Rule::in($this->manageableRoles)],
            'password' => ['nullable', 'string', Password::defaults()],
        ])->validate();

        $updates = [
            'name' => $data['name'],
            'email' => $data['email'],
            'role' => $data['role'],
        ];

        if ($data['password'] !== '') {
            $updates['password'] = Hash::make($data['password']);
        }

        $user->forceFill($updates)->save();

        session()->flash('roleStatus', 'Data '.$user->name.' berhasil diperbarui.');

        $this->cancelEdit();
    }

    public function cancelEdit(): void
    {
        $this->editingUserId = null;
        $this->editName = '';
        $this->editEmail = '';
        $this->editRole = $this->manageableRoles[0] ?? 'alumni';
        $this->editPassword = '';
    }

    public function confirmDelete(int $userId): void
    {
        $user = User::query()
            ->whereKey($userId)
            ->whereKeyNot(auth()->id())
            ->whereIn('role', $this->manageableRoles)
            ->firstOrFail();

        $this->pendingDeleteId = $user->id;
        $this->pendingDeleteName = $user->name;
    }

    public function cancelDelete(): void
    {
        $this->pendingDeleteId = null;
        $this->pendingDeleteName = null;
    }

    public function deleteUser(): void
    {
        if ($this->pendingDeleteId === null) {
            return;
        }

        $user = User::query()
            ->whereKey($this->pendingDeleteId)
            ->whereIn('role', $this->manageableRoles)
            ->first();

        if (! $user) {
            $this->cancelDelete();

            return;
        }

        $name = $user->name;
        $user->delete();

        if ($this->editingUserId === $user->id) {
            $this->cancelEdit();
        }

        session()->flash('roleStatus', 'Pengguna '.$name.' berhasil dihapus.');

        $this->cancelDelete();
    }

    protected function resetCreateForm(): void
    {
        $this->createName = '';
        $this->createEmail = '';
        $this->createRole = $this->manageableRoles[0] ?? 'alumni';
        $this->createPassword = '';
    }

    public function render()
    {
        return view('livewire.superadmin.role-management');
    }
}
