<?php

namespace App\Http\Controllers;

use App\Models\Snippet;
use App\Models\Tag;
use Illuminate\Http\Request;

class SnippetController extends Controller
{
    public function displayAll()
    {
        // Getting all snippets with the tags of each one
        $snippets = Snippet::where('is_deleted', false)->with('tags')->paginate(10);

        return response()->json([
            'message' => true,
            'data' => $snippets
        ]);
    }

    public function addSnippet(Request $request)
    {
        $request->validate([
            'code' => 'required|string',
            'language' => 'required|string',
            'tags' => 'array',
            'is_favourite' => 'boolean'
        ]);

        $snippet = Snippet::create([
            'user_id' => $request->user_id,
            'code' => $request->code,
            'language' => $request->language,
            'is_favourite' => $request->is_favourite ?? false,
        ]);

        if ($request->has('tags')) {
            // Get or create tags
            $tags = collect($request->tags)->map(function ($tagName) {
                // Create new tag if it doesn't already exist
                return Tag::firstOrCreate(['name' => $tagName]);
            });

            // Attach the tags to the snippet
            $snippet->tags()->attach($tags->pluck('id')->toArray());
        }

        return response()->json([
            'message' => 'Snippet and tags added successfully'
        ]);
    }

    public function updateSnippet(Request $request, $id)
    {
        $request->validate([
            'code' => 'required|string',
            'language' => 'required|string',
            'tags' => 'array',
            'is_favourite' => 'boolean'
        ]);

        $snippet = Snippet::findOrFail($id);

        $snippet->update([
            'code' => $request->code,
            'language' => $request->lagnuage,
            'is_favourite' => $request->is_favourite ?? $snippet->is_favourite,
        ]);

        if ($request->has('tags')) {
            $tags = collect($request->tags)->map(function ($tagName) {
                return Tag::firstOrCreate(['name' => $tagName]);
            });
        }

        $snippet->tags()->sync($tags->pluck('id')->toArray());

        return response()->json([
            'message' => 'Snippet and tags updated successfully'
        ]);
    }

    public function deleteSnippet($id)
    {

        $snippet = Snippet::findOrFail($id);

        $snippet->update(['is_deleted' => true]);

        return response()->json([
            'message' => 'Snippet marked as deleted successfully'
        ]);
    }
}
