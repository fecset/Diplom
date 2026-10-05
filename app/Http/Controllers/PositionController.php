<?php

namespace App\Http\Controllers;

use App\Models\Position;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PositionController extends Controller
{
    public function index()
    {
        return view('dictionaries.index', ['items' => Position::orderBy('name')->paginate(20), 'kind' => 'positions', 'title' => 'Должности']);
    }

    public function create()
    {
        return view('dictionaries.form', ['item' => new Position, 'kind' => 'positions', 'title' => 'Должности']);
    }

    public function store(Request $request)
    {
        Position::create($request->validate(['name' => 'required|string|max:255|unique:positions,name']));

        return redirect()->route('admin.positions.index')->with('success', 'Запись добавлена.');
    }

    public function show(Position $position)
    {
        return redirect()->route('admin.positions.edit', $position);
    }

    public function edit(Position $position)
    {
        return view('dictionaries.form', ['item' => $position, 'kind' => 'positions', 'title' => 'Должности']);
    }

    public function update(Request $request, Position $position)
    {
        $position->update($request->validate(['name' => ['required', 'string', 'max:255', Rule::unique('positions', 'name')->ignore($position->id)]]));

        return redirect()->route('admin.positions.index')->with('success', 'Запись обновлена.');
    }

    public function destroy(Position $position)
    {
        // Referenced dictionaries must remain available in archived personnel history too.
        abort_if(User::withTrashed()->where('position_id', $position->id)->exists(), 409, 'Запись используется сотрудниками.');
        $position->delete();

        return redirect()->route('admin.positions.index')->with('success', 'Запись удалена.');
    }
}
