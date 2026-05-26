<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use Illuminate\Http\Request;
use App\Http\Requests\GetCustomersRequest;
use App\Http\Requests\StoreCustomerRequest;
use App\Helpers\ApiResponse;
use App\Http\Resources\PaginatedResource;
use App\Http\Resources\CustomerResource;
use Illuminate\Http\Response;
use App\Http\Requests\UpdateCustomerRequest;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Routing\Controllers\HasMiddleware;
use Spatie\Permission\Middleware\PermissionMiddleware;

class CustomerController extends Controller implements HasMiddleware
{

    public static function middleware()
    {
        return [
            new Middleware(PermissionMiddleware::using('view_customers'), only: ['index', 'show', 'options']),
            new Middleware(PermissionMiddleware::using('create_customers'), only: ['store']),
            new Middleware(PermissionMiddleware::using('edit_customers'), only: ['update']),
            new Middleware(PermissionMiddleware::using('delete_customers'), only: ['destroy']),


        ];
    }



    /**
     * Display a listing of the resource.
     */
    public function index(GetCustomersRequest $request)
    {
        $customers = Customer::search($request->search)->latest()
            ->paginate($request->input('limit', 15));

        return ApiResponse::success(
            new PaginatedResource($customers, CustomerResource::class),
            'Customers retrieved successfully.'
        );
    }



    public function options(GetCustomersRequest $request)

    {
        $customers = Customer::select('id', 'name')
            ->search($request->search)
            ->latest()
            ->get();

        return ApiResponse::success(
            CustomerResource::collection($customers),
            'Customers options retrieved successfully.'
        );
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreCustomerRequest $request)
    {
        $customer = Customer::create($request->validated());

        return ApiResponse::success(
            new CustomerResource($customer),
            'Customer created successfully.',
            Response::HTTP_CREATED
        );
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $customer = Customer::find($id);

        if (!$customer) {
            return ApiResponse::error(
                'Customer not found.',
                Response::HTTP_NOT_FOUND
            );
        }

        return ApiResponse::success(
            new CustomerResource($customer),
            'Customer retrieved successfully.'
        );
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateCustomerRequest $request, string $id)
    {
        $customer = Customer::find($id);

        if (!$customer) {
            return ApiResponse::error(
                'Customer not found.',
                Response::HTTP_NOT_FOUND
            );
        }

        $customer->update($request->validated());

        return ApiResponse::success(
            new CustomerResource($customer),
            'Customer updated successfully.'
        );
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $customer = Customer::find($id);

        if (!$customer) {
            return ApiResponse::error(
                'Customer not found.',
                Response::HTTP_NOT_FOUND
            );
        }

        $customer->delete();

        return ApiResponse::success(
            null,
            'Customer deleted successfully.'
        );
    }
}
