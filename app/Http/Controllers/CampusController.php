<?php
namespace App\Http\Controllers;
use App\Models\Campus;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
class CampusController extends Controller {
    public function store(Request $request){
        $validated = $request->validate(['name' => 'required|string|max:255|unique:campuses,name', 'color' => 'required|string|max:7']);
        $campus = Campus::create($validated);
        return response()->json(['message' => '校區已成功新增！', 'data' => $campus]); // 修改
    }
    public function update(Request $request, Campus $campus){
        $validated = $request->validate(['name' => ['required','string','max:255',Rule::unique('campuses')->ignore($campus->id)], 'color' => 'required|string|max:7']);
        $campus->update($validated);
        return response()->json(['message' => '校區已成功更新！', 'data' => $campus->fresh()]); // 修改
    }
    public function destroy(Campus $campus){
        if ($campus->locations()->exists()) { return response()->json(['message' => '無法刪除！尚有地點屬於此校區。'], 422); }
        $campus->delete();
        return response()->json(['message' => '校區已成功刪除！']);
    }
}