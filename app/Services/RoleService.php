<?php

namespace App\Services;

use App\Models\Role;
use Illuminate\Support\Collection;

interface RoleService
{
    public function create(
        string $name,
        string $display_name,
        string $description,
        array $permissions
    ): ?Role;

    public function read(array $relationship = [], array $exclude = []): ?Collection;

    public function readBy(string $key, string $value);

    public function update(
        int $id,
        string $name,
        string $display_name,
        string $description,
        array $inputtedPermissions
    ): ?Role;

    public function getAllPermissions();
}
