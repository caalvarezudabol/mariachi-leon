<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Traits\Auditable;

class GestionRoles extends Component
{
    use Auditable;

    public $role_id = null;
    public $name = '';
    public $selectedPermissions = [];
    public $modalOpen = false;
    public $isEdit = false;

    protected function rules()
    {
        return [
            'name' => 'required|string|max:255|unique:roles,name,' . ($this->role_id ?? 'NULL'),
            'selectedPermissions' => 'array',
        ];
    }

    protected $messages = [
        'name.required' => 'El nombre del rol es obligatorio.',
        'name.unique' => 'Este nombre de rol ya existe.',
    ];

    public function abrirModal()
    {
        $this->reset(['role_id', 'name', 'selectedPermissions', 'isEdit']);
        $this->modalOpen = true;
    }

    public function editar($id)
    {
        $role = Role::findOrFail($id);
        $this->role_id = $role->id;
        $this->name = $role->name;
        $this->selectedPermissions = $role->permissions->pluck('name')->toArray();
        $this->isEdit = true;
        $this->modalOpen = true;
    }

    public function guardar()
    {
        $this->validate();

        if ($this->isEdit) {
            $role = Role::findOrFail($this->role_id);
            $role->update(['name' => $this->name]);
            $role->syncPermissions($this->selectedPermissions);

            $this->registrarAuditoria('Administración', 'Editar Rol', 'Se actualizó el rol: ' . $role->name);
            session()->flash('success', 'Rol actualizado correctamente.');
        } else {
            $role = Role::create(['name' => $this->name, 'guard_name' => 'web']);
            $role->syncPermissions($this->selectedPermissions);

            $this->registrarAuditoria('Administración', 'Crear Rol', 'Se creó el rol: ' . $role->name);
            session()->flash('success', 'Rol creado exitosamente.');
        }

        $this->modalOpen = false;
    }

    public function render()
    {
        $roles = Role::with('permissions')->get();
        $permissions = Permission::all();

        return view('livewire.admin.gestion-roles', [
            'roles' => $roles,
            'permissions' => $permissions,
        ])->layout('components.layouts.app', ['title' => 'Gestión de Roles & Permisos - Mariachi León']);
    }
}
