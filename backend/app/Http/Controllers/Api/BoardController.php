<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Board;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class BoardController extends Controller
{
    public function index(): JsonResponse
    {
        $boards = Board::query()
            ->with(['lists.cards'])
            ->orderBy('created_at')
            ->get();

        return response()->json($boards);
    }

    public function defaultBoard(): JsonResponse
    {
        $board = Board::query()->firstOrCreate(
            ['slug' => 'forge-2-board'],
            ['title' => 'Forge 2 Kanban']
        );

        $this->ensureDefaultLists($board);

        return response()->json($this->boardPayload($board));
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:120'],
        ]);

        $board = Board::query()->create([
            'title' => $data['title'],
            'slug' => Str::slug($data['title']).'-'.Str::random(6),
        ]);

        $this->ensureDefaultLists($board);

        return response()->json($this->boardPayload($board), 201);
    }

    public function show(Board $board): JsonResponse
    {
        return response()->json($this->boardPayload($board));
    }

    public function update(Request $request, Board $board): JsonResponse
    {
        $data = $request->validate([
            'title' => ['sometimes', 'required', 'string', 'max:120'],
        ]);

        $board->update($data);

        return response()->json($this->boardPayload($board));
    }

    public function destroy(Board $board): JsonResponse
    {
        $board->delete();

        return response()->json(['deleted' => true]);
    }

    private function ensureDefaultLists(Board $board): void
    {
        $defaults = ['Todo', 'Doing', 'Done'];
        $existing = $board->lists()->pluck('title')->all();

        foreach ($defaults as $position => $title) {
            if (! in_array($title, $existing, true)) {
                $board->lists()->create([
                    'title' => $title,
                    'position' => $position,
                ]);
            }
        }
    }

    private function boardPayload(Board $board): Board
    {
        return $board->fresh()->load([
            'lists' => fn ($query) => $query->orderBy('position'),
            'lists.cards' => fn ($query) => $query->orderBy('position')->orderBy('created_at'),
        ]);
    }
}
