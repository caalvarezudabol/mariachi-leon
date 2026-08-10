<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;
use App\Traits\Auditable;

class GestionUsuarios extends Component
{
    use WithPagination, Auditable;

    public $search = '';
    public $user_id = null;
    public $name = '';
    public $email = '';
    public $telefono = '';
    public $password = '';
    public $role = '';
    public $activo = true;
    public $modalOpen = false;
    public $isEdit = false;

    protected function rules()
    {
        return [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . ($this->user_id ?? 'NULL'),
            'telefono' => 'nullable|string|max:20',
            'password' => $this->isEdit ? 'nullable|min:6' : 'required|min:6',
            'role' => 'required',
        ];
    }

    protected $messages = [
        'name.required' => 'El nombre completo es obligatorio.',
        'email.required' => 'El correo electrónico es obligatorio.',
        'email.email' => 'Ingrese un correo electrónico válido.',
        'email.unique' => 'Este correo ya está registrado.',
        'password.required' => 'La contraseña es obligatoria.',
        'password.min' => 'La contraseña debe tener al menos 6 caracteres.',
        'role.required' => 'Debe seleccionar un rol para el usuario.',
    ];

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function abrirModal()
    {
        $this->reset(['user_id', 'name', 'email', 'telefono', 'password', 'role', 'isEdit']);
        $this->activo = true;
        $this->modalOpen = true;
    }

    public function editar($id)
    {
        $user = User::findOrFail($id);
        $this->user_id = $user->id;
        $this->name = $user->name;
        $this->email = $user->email;
        $this->telefono = $user->telefono;
        $this->activo = $user->activo;
        $this->role = $user->roles->pluck('name')->first() ?? '';
        $this->isEdit = true;
        $this->modalOpen = true;
    }

    public function guardar()
    {
        $this->validate();

        if ($this->isEdit) {
            $user = User::findOrFail($this->user_id);
            $data = [
                'name' => $this->name,
                'email' => $this->email,
                'telefono' => $this->telefono,
                'activo' => $this->activo,
            ];
            if (!empty($this->password)) {
                $data['password'] = Hash::make($this->password);
            }
            $user->update($data);
            $user->syncRoles([$this->role]);

            $this->registrarAuditoria('Administración', 'Editar Usuario', 'Se actualizó la información del usuario: ' . $user->email);
            session()->flash('success', 'Usuario actualizado correctamente.');
        } else {
            $user = User::create([
                'name' => $this->name,
                'email' => $this->email,
                'telefono' => $this->telefono,
                'activo' => $this->activo,
                'password' => Hash::make($this->password),
            ]);
            $user->assignRole($this->role);

            $this->registrarAuditoria('Administración', 'Crear Usuario', 'Se creó el usuario: ' . $user->email . ' con el rol: ' . $this->role);
            session()->flash('success', 'Usuario creado exitosamente.');
        }

        $this->modalOpen = false;
    }

    public function cambiarEstado($id)
    {
        $user = User::findOrFail($id);
        $user->activo = !$user->activo;
        $user->save();

        $estado = $user->activo ? 'activó' : 'desactivó';
        $this->registrarAuditoria('Administración', 'Cambiar Estado Usuario', "Se $estado al usuario: " . $user->email);
        session()->flash('success', "Usuario " . ($user->activo ? 'activado' : 'desactivado') . " correctamente.");
    }

    public function eliminar($id)
    {
        $user = User::findOrFail($id);
        $email = $user->email;
        $user->delete();

        $this->registrarAuditoria('Administración', 'Eliminar Usuario', 'Se eliminó (Soft Delete) al usuario: ' . $email);
        session()->flash('success', 'Usuario eliminado correctamente.');
    }

    public function render()
    {
        $usuarios = User::with('roles')
            ->where(function ($query) {
                $query->where('name', 'like', '%' . $this->search . '%')
                    ->orWhere('email', 'like', '%' . $this->search . '%');
            })
            ->latest()
            ->paginate(10);

        $roles = Role::all();

        return view('livewire.admin.gestion-usuarios', [
            'usuarios' => $usuarios,
            'roles' => $roles,
        ])->layout('components.layouts.app', ['title' => 'Gestión de Usuarios - Mariachi León']);
    }
}
