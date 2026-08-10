<?php

namespace App\Livewire\Auth;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use App\Traits\Auditable;

class Login extends Component
{
    use Auditable;

    public $email = '';
    public $password = '';
    public $remember = false;

    protected $rules = [
        'email' => 'required|email',
        'password' => 'required|min:4',
    ];

    protected $messages = [
        'email.required' => 'El correo electrónico es obligatorio.',
        'email.email' => 'Ingrese un correo electrónico válido.',
        'password.required' => 'La contraseña es obligatoria.',
    ];

    public function login()
    {
        $this->validate();

        if (Auth::attempt(['email' => $this->email, 'password' => $this->password, 'activo' => true], $this->remember)) {
            session()->regenerate();
            $this->registrarAuditoria('Seguridad', 'Inicio de Sesión', 'El usuario ' . $this->email . ' inició sesión exitosamente.');
            return redirect()->intended(route('admin.dashboard'));
        }

        $this->addError('email', 'Las credenciales proporcionadas no son correctas o el usuario está inactivo.');
    }

    public function render()
    {
        return view('livewire.auth.login')->layout('components.layouts.web', ['title' => 'Iniciar Sesión - Mariachi León']);
    }
}
