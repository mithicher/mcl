<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Enums\IndustryType;
use Illuminate\Support\Arr;
use App\Enums\UserType;
use Inertia\Inertia;
use App\Company;
use Illuminate\Support\Facades\Gate;

class CompaniesController extends Controller
{
    public function index()
    {
        $companies = Company::query()
            ->filter(request()->only('search'))
            ->role()
            ->orderByDesc('created_at')
            ->simplePaginate(6);

        $filters = request()->all('search');

        return Inertia::render('Companies/Index', [
            'companies' => $companies,
            'filters' => $filters,
            'can' => [
                'create' => Gate::allows('create-company')
            ]
        ]);
    }

    public function create()
    {
        $industries = IndustryType::toSelectArray();

        return Inertia::render('Companies/Create', [
            'industries' => $industries
        ]);
    }

    public function store(Request $request)
    {
        $rules = [
            'company_logo' => 'required|image|mimes:jpeg,png,jpg|max:1024',
            'company_name' => ['required'],
            'company_industry' => ['required']
        ];

        if (!auth()->user()->isAdmin()) {
            $rules['company_website'] = ['required', 'url'];
            $rules['company_description'] = ['required'];
            $rules['company_no_of_employees'] = ['required'];
            $rules['company_benefits'] = ['required'];
        }

        $input = $this->validate($request, $rules, [
            'company_logo.max' => 'Image size must me less than 1 MB',
            'company_logo.dimensions' => 'Image must be atleast 200x200px as a png or jpeg file'
        ]);

        $companyCreated = Company::create($request->only([
            'company_name',
            'company_website',
            'company_description',
            'company_industry',
            'company_no_of_employees',
            'company_benefits'
        ]) + [
            'user_id' => auth()->user()->id,
            'company_logo' => $request->file('company_logo') ? $request->file('company_logo')->store('company', 'public') : null
        ]);

        session()->flash('success', 'Company details successfully saved.');

        return redirect()->route('admin.companies.all');
    }

    public function edit($uuid, Request $request)
    {
        $company = Company::findByUuidOrFail($uuid);

        $company['can'] = [
            'delete' => Gate::allows('delete-company', $company),
            'edit' => Gate::allows('update-company', $company)
        ];

        $industries = IndustryType::toSelectArray();

        return Inertia::render('Companies/Edit', compact('company', 'industries'));
    }

    public function update($uuid, Request $request)
    {
        $company = Company::findByUuidOrFail($uuid);

        $rules = [
            'company_logo' => 'required|image|mimes:jpeg,png,jpg|max:1024',
            'company_name' => ['required'],
            'company_industry' => ['required']
        ];

        if (!auth()->user()->isAdmin()) {
            $rules['company_website'] = ['required', 'url'];
            $rules['company_description'] = ['required'];
            $rules['company_no_of_employees'] = ['required'];
            $rules['company_benefits'] = ['required'];
        }

        $input = $this->validate($request, $rules, [
            'company_logo.max' => 'Image size must me less than 1 MB',
            'company_logo.dimensions' => 'Image must be atleast 200x200px as a png or jpeg file'
        ]);

        // dd($input);

        if ($request->file('company_logo')) {
            $company->update([
                'company_logo' => $request->file('company_logo')->store('company', 'public')
            ]);
        }
        $data = $request->only([
            'company_name',
            'company_website',
            'company_description',
            'company_no_of_employees',
            'company_benefits'
        ]);

        if ($request->has('company_industry')) {
            $data['company_industry'] = (int) $request->company_industry;
        }

        $company->update($data);

        session()->flash('success', 'Company Details Updated.');
        return back();
    }

    public function deleteLogo($uuid)
    {
        $company = Company::findByUuidOrFail($uuid);

        // remove from the storage first
        $exists = Storage::disk('public')->exists($company->company_logo);
        if ($exists) {
            Storage::delete($company->company_logo);
        }

        $company->company_logo = null;
        $company->save();

        session()->flash('success', 'Company logo successfully deleted.');

        return back();
    }
}
