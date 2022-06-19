<?php

namespace App\Http\Controllers;

use App\Services\RoleService;
use App\Services\UserService;
use App\Services\EmployeeService;
use Vinkla\Hashids\Facades\Hashids;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\EmployeeRequest;
use App\Http\Resources\EmployeeResource;
use App\Services\CompanyService;

class EmployeeController extends BaseController
{
    private $employeeService;
    private $companyService;
    
    public function __construct(EmployeeService $employeeService, CompanyService $companyService)
    {
        parent::__construct();

        $this->middleware('auth');
        $this->employeeService = $employeeService;
        $this->companyService = $companyService;
    }

    public function read(EmployeeRequest $employeeRequest)
    {
        $request = $employeeRequest->validated();

        $search = $request['search'];
        $paginate = $request['paginate'];
        $page = array_key_exists('page', $request) ? abs($request['page']) : 1;
        $perPage = array_key_exists('perPage', $request) ? abs($request['perPage']) : 10;

        $companyId = $request['company_id'];

        $result = $this->employeeService->read(
            companyId: $companyId,
            search: $search,
            paginate: $paginate,
            page: $page,
            perPage: $perPage
        );

        if (is_null($result)) {
            return response()->error();
        } else {
            $response = EmployeeResource::collection($result);

            return $response;
        }
    }

    public function store(EmployeeRequest $employeeRequest)
    {   
        $request = $employeeRequest->validated();

        $company_id = $request['company_id'];

        $code = $request['code'];
        if ($code == config('const.DEFAULT.KEYWORDS.AUTO')) {
            do {
                $code = $this->employeeService->generateUniqueCode($company_id);
            } while (!$this->employeeService->isUniqueCode($code, $company_id));
        } else {
            if (!$this->employeeService->isUniqueCode($code, $company_id)) {
                return response()->error([
                    'code' => [trans('rules.unique_code')]
                ], 422);
            }
        }

        $name = $request['name'];
        $email = $request['email'];
        $address = $request['address'];
        $city = $request['city'];
        $postal_code = $request['postal_code'];
        $country = $request['country'];
        $tax_id = $request['tax_id'];
        $ic_num = $request['ic_num'];

        if (isset($request['img_path']) || !empty($request['img_path'])) {
            $img_path = $request['img_path'];
            $image = $request['img_path'];
            $filename = time().".".$image->getClientOriginalExtension();
            
            $file = $image->storePubliclyAs('usr', $filename, 'public');
            $profile['img_path'] = $file;
        } else {
            $img_path = null;
        }

        $join_date = $request['join_date'];
        $remarks = $request['remarks'];
        $status = $request['status'];

        $accesses = [];
        if (!empty($request['accessBranchIds'])) {
            for ($i = 0; $i < count($request['accessBranchIds']); $i++) {
                array_push($accesses, array(
                    'branch_id' => Hashids::decode($request['accessBranchIds'][$i])[0]
                ));
            }
        }

        $result = $this->employeeService->create(
            company_id: $company_id,
            code: $code, 
            name: $name,
            email: $email,
            address: $address,
            city: $city,
            postal_code: $postal_code,
            country: $country,
            tax_id: $tax_id,
            ic_num: $ic_num,
            img_path: $img_path,
            join_date: $join_date,
            remarks: $remarks,
            status: $status,
            accesses: $accesses
        );
        return is_null($result) ? response()->error():response()->success();
    }

    public function update($id, EmployeeRequest $employeeRequest)
    {
        $request = $employeeRequest->validated();
        
        $company_id = $request['company_id'];

        $code = $request['code'];
        if ($code == config('const.DEFAULT.KEYWORDS.AUTO')) {
            do {
                $code = $this->employeeService->generateUniqueCode($company_id);
            } while (!$this->employeeService->isUniqueCode($code, $company_id, $id));
        } else {
            if (!$this->employeeService->isUniqueCode($code, $company_id, $id)) {
                return response()->error([
                    'code' => [trans('rules.unique_code')]
                ], 422);
            }
        }

        $name = $request['name'];
        $address = $request['address'];
        $city = $request['city'];
        $postal_code = $request['postal_code'];
        $country = $request['country'];
        $tax_id = $request['tax_id'];
        $ic_num = $request['ic_num'];
        
        if (isset($request['img_path']) || !empty($request['img_path'])) {
            $img_path = $request['img_path'];
            $image = $request['img_path'];
            $filename = time().".".$image->getClientOriginalExtension();
            
            $file = $image->storePubliclyAs('usr', $filename, 'public');
            $profile['img_path'] = $file;
        } else {
            $img_path = null;
        }

        $remarks = $request['remarks'];
        $status = $request['status'];

        $accesses = [];
        if (!empty($request['accessBranchIds'])) {
            for ($i = 0; $i < count($request['accessBranchIds']); $i++) {
                array_push($accesses, array(
                    'branch_id' => Hashids::decode($request['accessBranchIds'][$i])[0]
                ));
            }
        }

        $result = $this->employeeService->update(
            id: $id,
            code: $code, 
            name: $name,
            address: $address,
            city: $city,
            postal_code: $postal_code,
            country: $country,
            tax_id: $tax_id,
            ic_num: $ic_num,
            img_path: $img_path,
            join_date: null,
            remarks: $remarks,
            status: $status,
            accesses: $accesses
        );
        return is_null($result) ? response()->error():response()->success();
    }

    public function delete($id)
    {
        $result = $this->employeeService->delete($id);

        return !$result ? response()->error():response()->success();
    }
}