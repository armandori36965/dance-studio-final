<?php
namespace App\Http\Controllers;
use App\Models\CourseTemplate;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CourseTemplateController extends Controller {
    public function store(Request $request){
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:course_templates,name', 
            'price' => 'required|integer|min:0',
        ]);
        $courseTemplate = CourseTemplate::create($validated);
        
        // 【最終修正】回傳乾淨的陣列，避免序列化問題
        $data = [
            'id' => $courseTemplate->id,
            'name' => $courseTemplate->name,
            'price' => $courseTemplate->price,
        ];

        return response()->json(['message' => '課程已成功新增！', 'data' => $data]);
    }

    // 【最終修正】將變數名稱從 $courseTemplate 改為 $course_template
    public function update(Request $request, CourseTemplate $course_template){
        $validated = $request->validate([
            'name' => ['required','string','max:255',Rule::unique('course_templates')->ignore($course_template->id)],
            'price' => 'required|integer|min:0',
        ]);
        $course_template->update($validated);

        // 【最終修正】回傳乾淨的陣列
        $data = [
            'id' => $course_template->id,
            'name' => $course_template->name,
            'price' => $course_template->price,
        ];

        return response()->json(['message' => '課程已成功更新！', 'data' => $data]);
    }

    // 【最終修正】將變數名稱從 $courseTemplate 改為 $course_template
    public function destroy(CourseTemplate $course_template){
        if ($course_template->courses()->exists()) { 
            return response()->json(['message' => '無法刪除！尚有課程正在使用此模板。'], 422); 
        }
        
        // 【最終修正】先儲存 ID，再刪除
        $deletedId = $course_template->id;
        $course_template->delete();
        
        // 【最終修正】回傳包含 ID 的安全資料
        return response()->json(['message' => '課程已成功刪除！', 'data' => ['id' => $deletedId]]);
    }
}