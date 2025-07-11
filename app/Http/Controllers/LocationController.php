<?php
namespace App\Http\Controllers;
use App\Models\Location;
use App\Models\Campus;
use Illuminate\Http\Request;
class LocationController extends Controller {
    public function getLocationsByCampus(Campus $campus){ return response()->json($campus->locations); }
    public function store(Request $request){
        $validated = $request->validate(['campus_id' => 'required|exists:campuses,id', 'name' => 'required|string|max:255',]);
        $location = Location::create($validated);
        return response()->json(['message' => '地點已成功新增！', 'data' => $location->load('campus')]);
    }
    public function update(Request $request, Location $location){
        $validated = $request->validate(['campus_id' => 'required|exists:campuses,id', 'name' => 'required|string|max:255',]);
        $location->update($validated);
        return response()->json(['message' => '地點已成功更新！', 'data' => $location->fresh()->load('campus')]);
    }
    public function destroy(Location $location){
        if ($location->courses()->exists()) { return response()->json(['message' => '無法刪除！尚有課程正在此地點進行。'], 422); }
        $id = $location->id;
        $location->delete();
        // 【修正】統一回應格式，回傳被刪除的 ID
        return response()->json(['message' => '地點已成功刪除！', 'data' => ['id' => $id]]);
    }
}