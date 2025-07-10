<?php
namespace App\Http\Controllers;
use App\Models\Teacher;
use Illuminate\Http\Request;
class TeacherController extends Controller {
    public function store(Request $request){
        $validated = $request->validate(['name' => 'required|string|max:255','phone_number' => 'required|string|max:255',]);
        $validated['rates'] = null;
        $teacher = Teacher::create($validated);
        return response()->json(['message' => '老師已成功新增！', 'data' => $teacher]); // 修改
    }
    public function update(Request $request, Teacher $teacher){
        $validated = $request->validate(['name' => 'required|string|max:255','phone_number' => 'required|string|max:255',]);
        $teacher->update($validated);
        return response()->json(['message' => '老師資料已成功更新！', 'data' => $teacher->fresh()]); // 修改
    }
    public function destroy(Teacher $teacher){
        if ($teacher->courses()->exists()) { return response()->json(['message' => '無法刪除！尚有課程指派給此老師。'], 422); }
        $teacher->delete();
        return response()->json(['message' => '老師已成功刪除！']);
    }
}