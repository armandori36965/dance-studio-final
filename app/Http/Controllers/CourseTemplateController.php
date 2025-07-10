<?php
namespace App\Http\Controllers;
use App\Models\CourseTemplate;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
class CourseTemplateController extends Controller {
    public function store(Request $request){
        $validated = $request->validate(['name' => 'required|string|max:255|unique:course_templates,name', 'price' => 'required|integer|min:0',]);
        $courseTemplate = CourseTemplate::create($validated);
        return response()->json(['message' => '課程已成功新增！', 'data' => $courseTemplate]); // 修改
    }
    public function update(Request $request, CourseTemplate $courseTemplate){
        $validated = $request->validate(['name' => ['required','string','max:255',Rule::unique('course_templates')->ignore($courseTemplate->id)],'price' => 'required|integer|min:0',]);
        $courseTemplate->update($validated);
        return response()->json(['message' => '課程已成功更新！', 'data' => $courseTemplate->fresh()]); // 修改
    }
    public function destroy(CourseTemplate $courseTemplate){
        if ($courseTemplate->courses()->exists()) { return response()->json(['message' => '無法刪除！尚有課程正在使用此模板。'], 422); }
        $courseTemplate->delete();
        return response()->json(['message' => '課程已成功刪除！']);
    }
}