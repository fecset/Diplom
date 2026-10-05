<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class DepartmentController extends Controller
{
    public function index()
    {
        return view('dictionaries.index', ['items' => Department::orderBy('name')->paginate(20), 'kind' => 'departments', 'title' => 'Отделы']);
    }

    public function create()
    {
        return view('dictionaries.form', ['item' => new Department, 'kind' => 'departments', 'title' => 'Отделы']);
    }

    public function store(Request $request)
    {
        Department::create($request->validate(['name' => 'required|string|max:255|unique:departments,name']));

        return redirect()->route('admin.departments.index')->with('success', 'Запись добавлена.');
    }

    public function show(Department $department)
    {
        return redirect()->route('admin.departments.edit', $department);
    }

    public function edit(Department $department)
    {
        return view('dictionaries.form', ['item' => $department, 'kind' => 'departments', 'title' => 'Отделы']);
    }

    public function update(Request $request, Department $department)
    {
        $department->update($request->validate(['name' => ['required', 'string', 'max:255', Rule::unique('departments', 'name')->ignore($department->id)]]));

        return redirect()->route('admin.departments.index')->with('success', 'Запись обновлена.');
    }

    public function destroy(Department $department)
    {
        // Referenced dictionaries must remain available in archived personnel history too.
        abort_if(User::withTrashed()->where('department_id', $department->id)->exists(), 409, 'Запись используется сотрудниками.');
        $department->delete();

        return redirect()->route('admin.departments.index')->with('success', 'Запись удалена.');
    }
}
