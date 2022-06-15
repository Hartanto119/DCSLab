<?php

namespace App\Services\Impls;

use App\Actions\RandomGenerator;
use App\Enums\ActiveStatus;
use Exception;
use Illuminate\Container\Container;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

use App\Models\User;
use App\Models\Supplier;
use App\Models\SupplierProduct;
use App\Services\SupplierService;
use App\Services\UserService;
use App\Services\RoleService;
use App\Traits\CacheHelper;

class SupplierServiceImpl implements SupplierService
{
    use CacheHelper;

    public function __construct()
    {
        
    }
    
    public function create(
        int $company_id,
        string $code,
        string $name,
        string $payment_term_type,
        ?int $payment_term = null,
        ?string $contact = null,
        ?string $address = null,
        ?string $city = null,
        bool $taxable_enterprise,
        string $tax_id,
        ?string $remarks = null,
        int $status,
        array $poc,
        array $products
    ): ?Supplier
    {
        DB::beginTransaction();
        $timer_start = microtime(true);

        try {
            $usr = $this->createUserPOC($poc);

            $supplier = new Supplier();
            $supplier->company_id = $company_id;
            $supplier->code = $code;
            $supplier->name = $name;
            $supplier->payment_term_type = $payment_term_type;
            $supplier->payment_term = $payment_term;
            $supplier->contact = $contact;
            $supplier->address = $address;
            $supplier->city = $city;
            $supplier->taxable_enterprise = $taxable_enterprise;
            $supplier->tax_id = $tax_id;
            $supplier->remarks = $remarks;
            $supplier->status = $status;
            $supplier->user_id = $usr->id;

            $supplier->save();

            $sp = [];
            foreach($products as $p) {
                $spe = new SupplierProduct();
                $spe->company_id = $company_id;
                $spe->product_id = $p['product_id'];
                $spe->main_product = $p['main_product'];

                array_push($sp, $spe);
            }

            $supplier->supplierProducts()->saveMany($sp);

            DB::commit();

            $this->flushCache();

            return $supplier;
        } catch (Exception $e) {
            DB::rollBack();
            Log::debug('['.session()->getId().'-'.(is_null(auth()->user()) ? '':auth()->id()).'] '.__METHOD__.$e);
        } finally {
            $execution_time = microtime(true) - $timer_start;
            Log::channel('perfs')->info('['.session()->getId().'-'.(is_null(auth()->user()) ? '':auth()->id()).'] '.__METHOD__.' ('.number_format($execution_time, 1).'s)');
        }
        return Config::get('const.ERROR_RETURN_VALUE');
    }

    private function createUserPOC(array $poc): User
    {
        $timer_start = microtime(true);

        try {
            $container = Container::getInstance();
            $userService = $container->make(UserService::class);
            $roleService = $container->make(RoleService::class);
    
            $rolesId = $roleService->readBy('name', 'POS-supplier')->id;
    
            $profile = [
                'first_name' => $poc['name'],
                'status' => ActiveStatus::ACTIVE
            ];
    
            $usr = $userService->create($poc['name'], $poc['email'], '', [$rolesId], $profile);
    
            return $usr;
        } catch (Exception $e) {
            DB::rollBack();
            Log::debug('['.session()->getId().'-'.(is_null(auth()->user()) ? '':auth()->id()).'] '.__METHOD__.$e);
        } finally {
            $execution_time = microtime(true) - $timer_start;
            Log::channel('perfs')->info('['.session()->getId().'-'.' '.'] '.__METHOD__.' ('.number_format($execution_time, 1).'s)');
        }
        return Config::get('const.ERROR_RETURN_VALUE');
    }

    public function read(
        int $companyId, 
        string $search = '', 
        bool $paginate = true, 
        int $page = 1, 
        int $perPage = 10, 
        bool $useCache = true
    ): Paginator|Collection|null
    {
        $cacheKey = '';
        if ($useCache) {
            $cacheKey = 'read_'.$companyId.'-'.(empty($search) ? '[empty]':$search).'-'.$paginate.'-'.$page.'-'.$perPage;
            $cacheResult = $this->readFromCache($cacheKey);

            if (!is_null($cacheResult)) return $cacheResult;
        }

        $result = null;

        $timer_start = microtime(true);

        try {
            if (!$companyId) return null;

            if (empty($search)) {
                $suppliers = Supplier::with('user.profile', 'company', 'supplierProducts.product')->whereCompanyId($companyId)->latest();
            } else {
                $suppliers = Supplier::with('user.profile', 'company', 'supplierProducts.product')->whereCompanyId($companyId)
                    ->where('name', 'like', '%'.$search.'%')->latest();
            }
    
            if ($paginate) {
                $perPage = is_numeric($perPage) ? $perPage : Config::get('const.DEFAULT.PAGINATION_LIMIT');
                return $suppliers->paginate(abs($perPage));
            } else {
                return $suppliers->get();
            }

            if ($useCache) $this->saveToCache($cacheKey, $result);
            
            return $result;
        } catch (Exception $e) {
            Log::debug('['.session()->getId().'-'.(is_null(auth()->user()) ? '':auth()->id()).'] '.__METHOD__.$e);
            return Config::get('const.DEFAULT.ERROR_RETURN_VALUE');
        } finally {
            $execution_time = microtime(true) - $timer_start;
            Log::channel('perfs')->info('['.session()->getId().'-'.(is_null(auth()->user()) ? '':auth()->id()).'] '.__METHOD__.' ('.number_format($execution_time, 1).'s)'.($useCache ? ' (C)':' (DB)'));
        }
    }

    public function update(
        int $id,
        int $company_id,
        string $code,
        string $name,
        string $payment_term_type,
        ?int $payment_term = null,
        ?string $contact = null,
        ?string $address = null,
        ?string $city = null,
        bool $taxable_enterprise,
        string $tax_id,
        ?string $remarks = null,
        int $status,
        array $poc,
        array $products
    ): ?Supplier
    {
        DB::beginTransaction();
        $timer_start = microtime(true);

        try {
            $supplier = Supplier::find($id);

            if ($code == Config::get('const.DEFAULT.KEYWORDS.AUTO')) {
                $code = $this->generateUniqueCode($company_id);
            }

            $supplier->update([
                'code' => $code,
                'name' => $name,
                'payment_term_type' => $payment_term_type,
                'payment_term' => $payment_term,
                'contact' => $contact,
                'address' => $address,
                'city' => $city,
                'taxable_enterprise' => $taxable_enterprise,
                'tax_id' => $tax_id,
                'remarks' => $remarks,
                'status' => $status
            ]);

            $supplier->supplierProducts()->delete();

            $newSP = [];
            foreach($products as $product) {
                $newSPE = new SupplierProduct();
                $newSPE->company_id = $company_id;
                $newSPE->supplier_id =$supplier->id;
                $newSPE->product_id = $product['product_id'];
                $newSPE->main_product = $product['main_product'];

                array_push($newSP, $newSPE);
            }

            $supplier->supplierProducts()->saveMany($newSP);

            DB::commit();

            $this->flushCache();

            return $supplier->refresh();
        } catch (Exception $e) {
            DB::rollBack();
            Log::debug('['.session()->getId().'-'.(is_null(auth()->user()) ? '':auth()->id()).'] '.__METHOD__.$e);
        } finally {
            $execution_time = microtime(true) - $timer_start;
            Log::channel('perfs')->info('['.session()->getId().'-'.(is_null(auth()->user()) ? '':auth()->id()).'] '.__METHOD__.' ('.number_format($execution_time, 1).'s)');
        }
        return Config::get('const.ERROR_RETURN_VALUE');
    }

    public function delete(int $id): bool
    {
        DB::beginTransaction();
        $timer_start = microtime(true);

        $retval = false;

        try {
            $supplier = Supplier::find($id);

            if ($supplier) {
                $supplier->supplierProducts()->delete();
                $supplier->delete();
    
                $supplier->user()->with('profile')->first()->profile()->update([
                    'status' => 0
                ]);

                $retval = true;
            }
            
            DB::commit();

            $this->flushCache();

            return $retval;
        } catch (Exception $e) {
            DB::rollBack();
            Log::debug('['.session()->getId().'-'.(is_null(auth()->user()) ? '':auth()->id()).'] '.__METHOD__.$e);
            return $retval;
        } finally {
            $execution_time = microtime(true) - $timer_start;
            Log::channel('perfs')->info('['.session()->getId().'-'.(is_null(auth()->user()) ? '':auth()->id()).'] '.__METHOD__.' ('.number_format($execution_time, 1).'s)');
        }
    }

    public function generateUniqueCode(): string
    {
        $rand = new RandomGenerator();
        $code = $rand->generateAlphaNumeric(3).$rand->generateFixedLengthNumber(3);
        return $code;
    }

    public function isUniqueCode(string $code, int $companyId, ?int $exceptId = null): bool
    {
        $result = Supplier::whereCompanyId($companyId)->where('code', '=' , $code);

        if($exceptId)
            $result = $result->where('id', '<>', $exceptId);

        return $result->count() == 0 ? true:false;
    }
}
