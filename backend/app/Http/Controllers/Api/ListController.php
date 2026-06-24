<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Board;
use App\Models\ListModel;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ListController extends Controller
{
    public function store(Request $request, Board $board): JsonResponse
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:80'],
        ]);

        $position = (int) $board->lists()->max('position') + 1;

        $list = $board->lists()->create([
            'title' => $data['title'],
            'position' => $position,
        ]);

        return response()->json($list->load('cards'), 201);
    }

    public function update(Request $request, ListModel $listModel): JsonResponse
    {
        $data = $request->validate([
            'title' => ['sometimes', 'required', 'string', 'max:80'],
            'position' => ['sometimes', 'integer', 'min:0'],
        ]);

        $listModel->update($data);

        return response()->json($listModel->fresh()->load('cards'));
    }

    public function destroy(ListModel $listModel): JsonResponse
    {
        $listModel->delete();

        return response()->json(['deleted' => true]);
    }
}
