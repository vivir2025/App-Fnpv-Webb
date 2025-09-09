<?php

namespace App\Helpers;

class RoleHelper
{
    /**
     * Verificar si el usuario actual es administrador
     */
    public static function isAdmin(): bool
    {
        $usuario = session('usuario', []);
        $rol = is_array($usuario) ? ($usuario['rol'] ?? '') : '';
        
        return in_array(strtolower($rol), ['admin', 'administrador']);
    }

    /**
     * Verificar si el usuario actual es jefe
     */
    public static function isJefe(): bool
    {
        $usuario = session('usuario', []);
        $rol = is_array($usuario) ? ($usuario['rol'] ?? '') : '';
        
        return strtolower($rol) === 'jefe';
    }

    /**
     * Verificar si el usuario tiene acceso a logs
     */
    public static function canAccessLogs(): bool
    {
        return self::isAdmin();
    }

    /**
     * Obtener el rol actual del usuario
     */
    public static function getCurrentRole(): string
    {
        $usuario = session('usuario', []);
        return is_array($usuario) ? ($usuario['rol'] ?? '') : '';
    }

    /**
     * Verificar si el usuario tiene un rol específico
     */
    public static function hasRole(string $role): bool
    {
        $currentRole = self::getCurrentRole();
        return strtolower($currentRole) === strtolower($role);
    }

    /**
     * Verificar si el usuario tiene alguno de los roles especificados
     */
    public static function hasAnyRole(array $roles): bool
    {
        $currentRole = strtolower(self::getCurrentRole());
        $roles = array_map('strtolower', $roles);
        
        return in_array($currentRole, $roles);
    }
}
