<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Card;
use App\Models\ListModel;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CardController extends Controller
{
    public function store(Request $request, ListModel $listModel): JsonResponse
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:160'],
            'description' => ['nullable', 'string'],
            'due_date' => ['nullable', 'date'],
        ]);

        $position = (int) $listModel->cards()->max('position') + 1;

        $card = $listModel->cards()->create([
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'due_date' => $data['due_date'] ?? null,
            'position' => $position,
        ]);

        return response()->json($card, 201);
    }

    public function update(Request $request, Card $card): JsonResponse
    {
        $data = $request->validate([
            'title' => ['sometimes', 'required', 'string', 'max:160'],
            'description' => ['sometimes', 'nullable', 'string'],
            'due_date' => ['sometimes', 'nullable', 'date'],
            'list_id' => ['sometimes', 'integer', Rule::exists('lists', 'id')],
            'position' => ['sometimes', 'integer', 'min:0'],
        ]);

        $card->update($data);

        return response()->json($card->fresh());
    }

    public function move(Request $request, Card $card): JsonResponse
    {
        $data = $request->validate([
            'list_id' => ['required', 'integer', Rule::exists('lists', 'id')],
            'position' => ['nullable', 'integer', 'min:0'],
        ]);

        $targetList = ListModel::query()->findOrFail($data['list_id']);
        $position = $data['position'] ?? ((int) $targetList->cards()->max('position') + 1);

        $card->update([
            'list_id' => $targetList->id,
            'position' => $position,
        ]);

        return response()->json($card->fresh());
    }

    public function destroy(Card $card): JsonResponse
    {
        $card->delete();

        return response()->json(['deleted' => true]);
    }
}
