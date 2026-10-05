<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Department;
use App\Models\LeaveRequest;
use App\Models\Position;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class AttendanceController extends Controller
{
    public function index(Request $request)
    {
        $request->validate(['date' => 'nullable|date_format:Y-m', 'name' => 'nullable|string|max:255', 'department' => 'nullable|integer|exists:departments,id', 'position' => 'nullable|integer|exists:positions,id']);
        $date = $request->input('date', now()->format('Y-m'));
        $start = CarbonImmutable::createFromFormat('!Y-m-d', $date.'-01');
        $end = $start->endOfMonth();
        $query = User::with(['department', 'position'])->leftJoin('departments', 'users.department_id', '=', 'departments.id')->select('users.*')->orderBy('departments.name')->orderBy('users.name');
        if (! $request->user()->isAdmin() && ! $request->user()->isHrSpecialist()) {
            $request->user()->department_id ? $query->where('users.department_id', $request->user()->department_id) : $query->where('users.id', $request->user()->id);
        }
        if ($request->filled('name')) {
            $query->where('users.name', 'like', '%'.$request->name.'%');
        }
        if ($request->filled('department')) {
            $query->where('users.department_id', $request->department);
        }
        if ($request->filled('position')) {
            $query->where('users.position_id', $request->position);
        }
        $departments = Department::orderBy('name')->get();
        $positions = Position::orderBy('name')->get();
        $users = $query->paginate(10)->withQueryString();
        $attendances = Attendance::whereIn('user_id', $users->getCollection()->modelKeys())->whereBetween('date', [$start->toDateString(), $end->toDateString()])->get()->groupBy(['user_id', 'date']);

        return view('attendances.index', compact('users', 'attendances', 'start', 'end', 'departments', 'positions'));
    }

    public function store(Request $request)
    {
        $data = $request->validate(['user_id' => ['required', Rule::exists('users', 'id')->whereNull('deleted_at')], 'date' => 'required|date_format:Y-m-d', 'status' => 'required|in:present,absent,vacation,sick_leave', 'comment' => 'nullable|string|max:255']);
        DB::transaction(function () use ($data) {
            User::whereKey($data['user_id'])->lockForUpdate()->firstOrFail();
            $leave = LeaveRequest::where('user_id', $data['user_id'])->where('status', 'approved')->where('date_start', '<=', $data['date'])->where('date_end', '>=', $data['date'])->first();
            if ($leave) {
                $expected = match ($leave->type) {
                    'vacation' => 'vacation','sick_leave' => 'sick_leave',default => 'present'
                };
                if ($expected !== $data['status']) {
                    throw ValidationException::withMessages(['status' => 'Отметка противоречит одобренной заявке.']);
                }
            }
            Attendance::updateOrCreate(['user_id' => $data['user_id'], 'date' => $data['date']], ['status' => $data['status'], 'comment' => $data['comment'] ?? null]);
        }, 3);

        return back()->with('success', 'Данные сохранены.');
    }

    public function update(Request $request, Attendance $attendance)
    {
        // Resource identity takes precedence over submitted identity.
        $request->merge(['user_id' => $attendance->user_id, 'date' => $attendance->date]);

        return $this->store($request);
    }
}
