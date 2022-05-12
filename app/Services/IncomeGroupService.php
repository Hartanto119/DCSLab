<?php

namespace App\Services;

use App\Models\IncomeGroup;

interface IncomeGroupService
{
    public function create(
        int $company_id,
        string $code,
        string $name,
        ): ?IncomeGroup;

    public function read(
        int $companyId,
        string $search = '',
        bool $paginate = true,
        int $page,
        int $perPage = 10,
        bool $useCache = true
    );

    public function update(
        int $id,
        int $company_id,
        string $code,
        string $name,
        ): ?IncomeGroup;

    public function delete(int $id): bool;

    public function generateUniqueCode(int $companyId): string;

    public function isUniqueCode(string $code, int $companyId, ?int $exceptId = null): bool;
}