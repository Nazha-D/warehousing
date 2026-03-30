<?php

namespace App\Http\Controllers;

use App\Models\TaxationGroup;
use App\Models\TaxRate;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Traits\ApiResponseTrait;
use Illuminate\Validation\Rule;

class TaxationGroupController extends Controller
{
    use ApiResponseTrait;

    /**
     * عرض كل Taxation Groups
     */
    public function index(Request $request)
    {
        $this->authorize('viewAny', TaxationGroup::class);

        $companyId = auth()->user()->company_id;

        $query = TaxationGroup::where('company_id', $companyId)
            ->orderBy('name');

        if ($request->filled('search')) {
            $query->filter($request->search);
        }

        $groups = $query->get();

        $message = 'Taxation Groups fetched successfully';

        return $this->successResponse($groups, $message, 200);
    }

    /**
     * عرض Taxation Group محدد
     */
    public function show(TaxationGroup $taxationGroup)
    {
        $this->authorize('view', $taxationGroup);

        $message = 'Taxation Group fetched successfully';

        return $this->successResponse($taxationGroup, $message, 200);
    }

    /**
     * إنشاء Taxation Group جديد
     */
    public function store(Request $request)
    {
        $this->authorize('create', TaxationGroup::class);

        $companyId = auth()->user()->company_id;

        $request->validate([
            'code' => [
                'required',
                'string',
                'max:50',
                Rule::unique('taxation_groups')->where(function ($query) use ($companyId) {
                    return $query->where('company_id', $companyId);
                }),
            ],
            'name' => 'required|string|max:255',
            'active' => 'boolean',
        ]);

        DB::beginTransaction();
        try {
            $group = TaxationGroup::create([
                'company_id' => $companyId,
                'code'       => $request->code,
                'name'       => $request->name,
                'active'     => $request->active ?? true,
            ]);

            DB::commit();

            $message = 'Taxation Group created successfully';

            return $this->successResponse($group, $message, 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->errorResponse($e->getMessage(), 500);
        }
    }

    /**
     * تعديل Taxation Group
     */
    public function update(Request $request, TaxationGroup $taxationGroup)
    {
        $this->authorize('update', $taxationGroup);

        $companyId = auth()->user()->company_id;

        $request->validate([
            'code' => [
                'sometimes',
                'required',
                'string',
                'max:50',
                Rule::unique('taxation_groups')
                    ->where(function ($query) use ($companyId) {
                        return $query->where('company_id', $companyId);
                    })
                    ->ignore($taxationGroup->id),
            ],
            'name' => 'sometimes|required|string|max:255',
            'active' => 'sometimes|boolean',
        ]);

        DB::beginTransaction();
        try {
            $taxationGroup->update(
                $request->only(['code', 'name', 'active'])
            );

            DB::commit();

            $message = 'Taxation Group updated successfully';

            return $this->successResponse($taxationGroup, $message, 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->errorResponse($e->getMessage(), 500);
        }
    }

    /**
     * حذف Taxation Group
     */
    public function destroy(TaxationGroup $taxationGroup)
    {
        $this->authorize('delete', $taxationGroup);

        DB::beginTransaction();
        try {
            $taxationGroup->delete(); // soft delete

            DB::commit();

            $message = 'Taxation Group deleted successfully';

            return $this->successResponse(null, $message, 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->errorResponse($e->getMessage(), 500);
        }
    }
    public function addRate(Request $request)
    {
        DB::beginTransaction();
        try {
            $this->authorize('create', TaxRate::class);
            $user = auth()->user();
            $request->validate([
                'startDate' => [
                    'required',
                    'date',

                ],
                'taxationGroupId' => ['nullable',
                    Rule::exists('taxation_groups', 'id')->where(function ($query) {
                        $query->where('company_id', auth()->user()->company_id);
                    }),],
                'taxRate' => ['required', 'numeric']
            ]);
            $taxRate = TaxRate::create([

                'taxation_group_id' => $request->taxationGroupId,
                'tax_rate' => $request->taxRate,
                'start_date' => $request->startDate
            ]);

            $message = 'Taxation rate added successfully';
            DB::commit();
            return  $this->successResponse($taxRate,$message,200);
        }

        catch (AuthorizationException $exception) {
            return $this->errorResponse('Unauthorized',401,[]);
        }
        catch (\Exception $exception)
        {
            DB::rollBack();
            return $this->errorResponse($exception->getMessage(),500,[]);

        }

    }
    public function updateRate(Request $request,TaxRate $taxRate)
    {
        DB::beginTransaction();
        try {
            $this->authorize('update', $taxRate);
            $user = auth()->user();
            $request->validate([
                'startDate' => [
                    'nullable',
                    'date',

                ],
                'taxationGroupId' => ['nullable',
                    Rule::exists('taxation_groups', 'id')->where(function ($query) {
                        $query->where('company_id', auth()->user()->company_id);
                    }),],
                'taxRate' => ['nullable', 'numeric']
            ]);
            $taxRate->update([

                'taxation_group_id' => $request->taxationGroupId ?? $taxRate->taxation_group_id,
                'tax_rate' => $request->taxRate ?? $taxRate->tax_rate ,
                'start_date' => $request->startDate ?? $taxRate->start_date
            ]);

            $message = 'Taxation rate updated successfully';
            DB::commit();
            return  $this->successResponse($taxRate,$message,200);
        }

        catch (AuthorizationException $exception) {
            return $this->errorResponse('Unauthorized',401,[]);
        }
        catch (\Exception $exception)
        {
            DB::rollBack();
            return $this->errorResponse($exception->getMessage(),500,[]);

        }

    }


}
