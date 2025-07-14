<?php
namespace App\Http\Controllers;
use App\Models\Campus;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CampusController extends Controller {
    public function store(Request $request){
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:campuses,name', 
            'color' => 'required|string|max:7'
        ]);
        $campus = Campus::create($validated);
        
        // 修正：確保即使是新校區，也回傳一個空的 locations 陣列
        $campus->load('locations'); 

        return response()->json(['message' => '校區已成功新增！', 'data' => $campus]);
    }

    public function update(Request $request, Campus $campus){
        $validated = $request->validate([
            'name' => ['required','string','max:255',Rule::unique('campuses')->ignore($campus->id)], 
            'color' => 'required|string|max:7'
        ]);
        $campus->update($validated);
        
        // 修正：更新後，一併回傳最新的校區資料及其地點
        return response()->json(['message' => '校區已成功更新！', 'data' => $campus->fresh()->load('locations')]);
    }

    public function destroy(Campus $campus){
        if ($campus->locations()->exists()) { 
            return response()->json(['message' => '無法刪除！尚有地點屬於此校區。'], 422); 
        }
        $id = $campus->id;
        $campus->delete();
        
        return response()->json(['message' => '校區已成功刪除！', 'data' => ['id' => $id]]);
    }
}