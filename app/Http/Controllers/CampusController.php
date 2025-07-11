<?php
namespace App\Http\Controllers;
use App\Models\Campus;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
class CampusController extends Controller {
    public function store(Request $request){
        $validated = $request->validate(['name' => 'required|string|max:255|unique:campuses,name', 'color' => 'required|string|max:7']);
        $campus = Campus::create($validated);
        return response()->json(['message' => '校區已成功新增！', 'data' => $campus]);
    }
    public function update(Request $request, Campus $campus){
        $validated = $request->validate(['name' => ['required','string','max:255',Rule::unique('campuses')->ignore($campus->id)], 'color' => 'required|string|max:7']);
        $campus->update($validated);
        return response()->json(['message' => '校區已成功更新！', 'data' => $campus->fresh()]);
    }
    public function destroy(Campus $campus){
        if ($campus->locations()->exists()) { return response()->json(['message' => '無法刪除！尚有地點屬於此校區。'], 422); }
        $id = $campus->id;
        $campus->delete();
        // 【修正】統一回應格式，回傳被刪除的 ID
        return response()->json(['message' => '校區已成功刪除！', 'data' => ['id' => $id]]);
    }
}