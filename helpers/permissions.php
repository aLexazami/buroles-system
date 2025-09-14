<?php
function isSelfManagingStaff(int $userId, int $targetId, int $activeRoleId, int $originalRoleId): bool {
  return $activeRoleId === 1 && $originalRoleId === 1 && $userId === $targetId;
}

function isElevatedRole(int $originalRoleId): bool {
  return in_array($originalRoleId, [2, 99]); // Admin or Superadmin
}

function canCreateFolder(int $userId, int $targetId, int $activeRoleId, int $originalRoleId): bool {
  return isElevatedRole($originalRoleId) || isSelfManagingStaff($userId, $targetId, $activeRoleId, $originalRoleId);
}

function canViewUploadButton(int $userId, int $targetId, int $activeRoleId): bool {
  return $activeRoleId === 1 && $userId === $targetId;
}
?>