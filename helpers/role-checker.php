<?php

function isSuperAdmin(): bool {
    return isset($_SESSION['role_slug']) && $_SESSION['role_slug'] === 'super_admin';
}

function isAdmin(): bool {
    return isset($_SESSION['role_slug']) && $_SESSION['role_slug'] === 'admin';
}

function isStaff(): bool {
    return isset($_SESSION['role_slug']) && $_SESSION['role_slug'] === 'staff';
}

function hasRoleSlug(string $slug): bool {
    return isset($_SESSION['role_slug']) && $_SESSION['role_slug'] === strtolower($slug);
}

function hasRoleId(int $roleId): bool {
    return isset($_SESSION['active_role_id']) && $_SESSION['active_role_id'] === $roleId;
}

function canSwitchToRole(int $roleId): bool {
    return in_array($roleId, $_SESSION['available_roles'] ?? [], true);
}