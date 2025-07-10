<?php
namespace App\Http\Controllers;
use App\Models\Course; use App\Models\CourseTemplate; use App\Models\Location; use App\Models\SchoolEvent; use App\Models\Teacher; use App\Models\Campus; use Illuminate\Http\Request; use Illuminate\Support\Carbon;

class ScheduleController extends Controller
{
    public function index(Request $request) {
        $currentDate = Carbon::parse($request->query('month', 'now'))->startOfMonth();
        $prevMonth = $currentDate->copy()->subMonth()->format('Y-m');
        $nextMonth = $currentDate->copy()->addMonth()->format('Y-m');
        $gridStartDate = $currentDate->copy()->startOfWeek(Carbon::SUNDAY);
        $gridEndDate = $currentDate->copy()->endOfMonth()->endOfWeek(Carbon::SATURDAY);
        $courseQuery = Course::with(['courseTemplate', 'location.campus', 'teacher'])->whereBetween('start_time', [$gridStartDate, $gridEndDate]);
        if ($request->filled('campus_id')) { $courseQuery->whereHas('location', function ($query) use ($request) { $query->where('campus_id', $request->input('campus_id')); }); }
        if ($request->filled('course_template_id')) { $courseQuery->where('course_template_id', $request->input('course_template_id')); }
        $courses = $courseQuery->orderBy('start_time')->get();
        // 【修正】改用所有PHP版本都相容的 function 寫法
        $groupedCourses = $courses->groupBy(function($course) {
            return $course->start_time->format('Y-m-d');
        });
        $schoolEventQuery = SchoolEvent::with('location')->where(function($query) use ($gridStartDate, $gridEndDate) {
            $query->whereBetween('start_date', [$gridStartDate, $gridEndDate])->orWhereBetween('end_date', [$gridStartDate, $gridEndDate])->orWhere(function($q) use ($gridStartDate, $gridEndDate) {
                $q->where('start_date', '<', $gridStartDate)->where('end_date', '>', $gridEndDate);
            });
        });
        if ($request->filled('campus_id')) { $schoolEventQuery->whereHas('location', function ($query) use ($request) { $query->where('campus_id', $request->input('campus_id')); }); }
        $schoolEvents = $schoolEventQuery->get();
        $groupedEvents = collect();
        foreach ($schoolEvents as $event) {
            $period = Carbon::parse($event->start_date)->daysUntil($event->end_date->addDay());
            foreach ($period as $date) {
                $dateString = $date->format('Y-m-d');
                if (!isset($groupedEvents[$dateString])) { $groupedEvents[$dateString] = collect(); }
                $groupedEvents[$dateString]->push($event);
            }
        }
        return view('schedule', [
            'currentDate' => $currentDate, 'prevMonth' => $prevMonth, 'nextMonth' => $nextMonth,
            'gridStartDate' => $gridStartDate, 'gridEndDate' => $gridEndDate,
            'groupedCourses' => $groupedCourses, 'groupedEvents' => $groupedEvents,
            'campuses' => Campus::all(), 'locations' => Location::with('campus')->get(),
            'courseTemplates' => CourseTemplate::all(), 'teachers' => Teacher::all(),
        ]);
    }

    // 【修正】讓所有方法都回傳 JSON，與前端對齊
    public function store(Request $request) {
        $validated = $request->validate([ 'course_template_id' => 'required|exists:course_templates,id', 'location_id' => 'required|exists:locations,id', 'teacher_id' => 'required|exists:teachers,id', 'course_date' => 'required|date', 'start_time' => 'required|date_format:H:i', 'end_time' => 'required|date_format:H:i|after:start_time', ]);
        $startTime = Carbon::parse($validated['course_date'] . ' ' . $validated['start_time']);
        $endTime = Carbon::parse($validated['course_date'] . ' ' . $validated['end_time']);
        Course::create(['course_template_id' => $validated['course_template_id'], 'location_id' => $validated['location_id'], 'teacher_id' => $validated['teacher_id'], 'start_time' => $startTime, 'end_time' => $endTime,]);
        return response()->json(['message' => '課程已成功新增！']);
    }
    public function update(Request $request, Course $course) {
        $validated = $request->validate([ 'course_template_id' => 'required|exists:course_templates,id', 'location_id' => 'required|exists:locations,id', 'teacher_id' => 'required|exists:teachers,id', 'start_time' => 'required|date_format:H:i', 'end_time' => 'required|date_format:H:i|after:start_time', ]);
        $startTime = Carbon::parse($course->start_time->format('Y-m-d') . ' ' . $validated['start_time']);
        $endTime = Carbon::parse($course->start_time->format('Y-m-d') . ' ' . $validated['end_time']);
        $course->update(['course_template_id' => $validated['course_template_id'], 'location_id' => $validated['location_id'], 'teacher_id' => $validated['teacher_id'], 'start_time' => $startTime, 'end_time' => $endTime,]);
        return response()->json(['message' => '課程已成功更新！']);
    }
    public function destroy(Course $course) {
        $course->delete();
        return response()->json(['message' => '課程已成功刪除！']);
    }
}