<?php

namespace App\Http\Controllers;

use App\Models\Position;
use App\Models\Department;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PositionController extends Controller
{
    public function index(Request $request)
    {
        $departmentId = $request->query('department_id');
        $departments = Department::orderBy('name')->get();

        $query = Position::with('department');
        if ($departmentId) {
            $query->where('department_id', $departmentId);
        }
        $positions = $query->paginate(10);

        return Inertia::render('Positions/index', [
            'positions' => $positions,
            'departments' => $departments,
            'filters' => [
                'department_id' => $departmentId,
            ],
        ]);
    }

    public function show(Position $position)
    {
        $position->load('department');
        return Inertia::render('Positions/show', [
            'position' => $position,
        ]);
    }

    public function create()
    {
        $departments = Department::orderBy('name')->get();
        return Inertia::render('Positions/create', [
            'departments' => $departments,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'department_id' => 'required|exists:departments,id',
            'min_salary' => 'nullable|numeric',
            'max_salary' => 'nullable|numeric',
            'description' => 'nullable|string',
        ]);

        \App\Models\Position::create($validated);

        return redirect()->route('positions.index')->with('success', 'Position created successfully.');
    }

    public function edit(Position $position)
    {
        $departments = Department::orderBy('name')->get();
        return Inertia::render('Positions/edit', [
            'position' => $position,
            'departments' => $departments,
        ]);
    }

    public function update(Request $request, Position $position)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'department_id' => 'required|exists:departments,id',
            'min_salary' => 'nullable|numeric',
            'max_salary' => 'nullable|numeric',
            'description' => 'nullable|string',
        ]);

        $position->update($validated);

        return redirect()->route('positions.index')->with('success', 'Position updated successfully.');
    }

    public function destroy(Position $position)
    {
        $position->delete();
        return redirect()->route('positions.index')->with('success', 'Position deleted successfully.');
    }
}
