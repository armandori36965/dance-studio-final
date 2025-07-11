<?php

namespace App\Http\Controllers;

use App\Models\Campus;
use App\Models\Course;
use App\Models\CourseTemplate;
use App\Models\Location;
use App\Models\SchoolEvent;
use App\Models\Teacher;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class ScheduleController extends Controller
{
    /**
     * 顯示行事曆主畫面
     */
    public function index(Request $request)
    {
        // --- 1. 日期計算 ---
        $currentDate = Carbon::parse($request->query('month', 'now'))->startOfMonth();
        $prevMonth = $currentDate->copy()->subMonth()->format('Y-m');
        $nextMonth = $currentDate->copy()->addMonth()->format('Y-m');
        $gridStartDate = $currentDate->copy()->startOfWeek(Carbon::SUNDAY);
        $gridEndDate = $currentDate->copy()->endOfMonth()->endOfWeek(Carbon::SATURDAY);

        // --- 2. 查詢課程資料 ---
        $courseQuery = Course::with(['courseTemplate', 'location.campus', 'teacher'])
            ->whereBetween('start_time', [$gridStartDate, $gridEndDate]);

        if ($request->filled('campus_id')) {
            $courseQuery->whereHas('location', function ($query) use ($request) {
                $query->where('campus_id', $request->input('campus_id'));
            });
        }
        if ($request->filled('course_template_id')) {
            $courseQuery->where('course_template_id', $request->input('course_template_id'));
        }

        $courses = $courseQuery->orderBy('start_time')->get();
        $groupedCourses = $courses->groupBy(fn($course) => $course->start_time->format('Y-m-d'));

        // --- 3. 查詢校務事件資料 ---
        $schoolEventQuery = SchoolEvent::with('location')
            ->where(function($query) use ($gridStartDate, $gridEndDate) {
                $query->whereBetween('start_date', [$gridStartDate, $gridEndDate])
                      ->orWhereBetween('end_date', [$gridStartDate, $gridEndDate])
                      ->orWhere(fn($q) => $q->where('start_date', '<', $gridStartDate)->where('end_date', '>', $gridEndDate));
            });
        
        if ($request->filled('campus_id')) {
            $schoolEventQuery->whereHas('location', function ($query) use ($request) {
                $query->where('campus_id', $request->input('campus_id'));
            });
        }
        $schoolEvents = $schoolEventQuery->get();
        
        // 將校務事件按日期分組
        $groupedEvents = collect();
        foreach ($schoolEvents as $event) {
            $period = Carbon::parse($event->start_date)->daysUntil($event->end_date->addDay());
            foreach ($period as $date) {
                $dateString = $date->format('Y-m-d');
                if (!isset($groupedEvents[$dateString])) {
                    $groupedEvents[$dateString] = collect();
                }
                $groupedEvents[$dateString]->push($event);
            }
        }

        // --- 4. 準備所有給彈出視窗用的資料 ---
        return view('schedule', [
            // 日期相關
            'currentDate' => $currentDate,
            'prevMonth' => $prevMonth,
            'nextMonth' => $nextMonth,
            'gridStartDate' => $gridStartDate,
            'gridEndDate' => $gridEndDate,
            // 課程與事件資料
            'groupedCourses' => $groupedCourses,
            'groupedEvents' => $groupedEvents,
            // 所有管理用的資料
            'campuses' => Campus::orderBy('name')->get(),
            'locations' => Location::with('campus')->orderBy('name')->get(),
            'courseTemplates' => CourseTemplate::orderBy('name')->get(),
            'teachers' => Teacher::orderBy('name')->get(),
        ]);
    }

    /**
     * 儲存新課程
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'course_template_id' => 'required|exists:course_templates,id',
            'location_id' => 'required|exists:locations,id',
            'teacher_id' => 'required|exists:teachers,id',
            'course_date' => 'required|date',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
        ]);

        $startTime = Carbon::parse($validated['course_date'] . ' ' . $validated['start_time']);
        $endTime = Carbon::parse($validated['course_date'] . ' ' . $validated['end_time']);

        $course = Course::create([
            'course_template_id' => $validated['course_template_id'],
            'location_id' => $validated['location_id'],
            'teacher_id' => $validated['teacher_id'],
            'start_time' => $startTime,
            'end_time' => $endTime,
        ]);

        // 回傳包含所有關聯資料的 JSON，方便前端更新畫面
        return response()->json([
            'message' => '課程已成功新增！',
            'data' => $course->load(['courseTemplate', 'location.campus', 'teacher'])
        ]);
    }

    /**
     * 更新現有課程
     */
    public function update(Request $request, Course $course)
    {
        $validated = $request->validate([
            'course_template_id' => 'required|exists:course_templates,id',
            'location_id' => 'required|exists:locations,id',
            'teacher_id' => 'required|exists:teachers,id',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
        ]);

        // 更新時間時，保持日期不變
        $startTime = Carbon::parse($course->start_time->format('Y-m-d') . ' ' . $validated['start_time']);
        $endTime = Carbon::parse($course->start_time->format('Y-m-d') . ' ' . $validated['end_time']);

        $course->update([
            'course_template_id' => $validated['course_template_id'],
            'location_id' => $validated['location_id'],
            'teacher_id' => $validated['teacher_id'],
            'start_time' => $startTime,
            'end_time' => $endTime,
        ]);

        return response()->json([
            'message' => '課程已成功更新！',
            'data' => $course->fresh()->load(['courseTemplate', 'location.campus', 'teacher'])
        ]);
    }

    /**
     * 刪除課程
     */
    public function destroy(Course $course)
    {
        $courseId = $course->id;
        $course->delete();
        return response()->json([
            'message' => '課程已成功刪除！',
            'data' => ['id' => $courseId] // 回傳被刪除的ID
        ]);
    }
}
