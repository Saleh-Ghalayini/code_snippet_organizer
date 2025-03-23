<?php

namespace App\Http\Controllers;

use App\Models\Snippet;
use App\Models\Tag;
use Illuminate\Http\Request;

class SnippetController extends Controller
{
    public function displayAll(Request $request)
    {
        $user_id = $request->user()->id;

        // Getting all snippets for the authenticated user with their tags
        $snippets = Snippet::where('user_id', $user_id)
            ->where('is_deleted', false)
            ->with('tags')
            ->paginate(10);

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

        // Get the authenticated user's ID
        $user_id = $request->user()->id;

        // Create the snippet and associate it with the user
        $snippet = Snippet::create([
            'user_id' => $user_id,
            'code' => $request->code,
            'language' => $request->language,
            'is_favourite' => $request->is_favourite ?? false,
        ]);

        // Add tags if provided
        if ($request->has('tags')) {
            $tags = collect($request->tags)->map(function ($tagName) {
                return Tag::firstOrCreate(['name' => $tagName]);
            });

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

        $user_id = $request->user()->id;

        $snippet = Snippet::where('user_id', $user_id)->findOrFail($id);

        $snippet->update([
            'code' => $request->code,
            'language' => $request->language,
            'is_favourite' => $request->is_favourite ?? $snippet->is_favourite,
        ]);

        // Update the tags if provided
        if ($request->has('tags')) {
            $tags = collect($request->tags)->map(function ($tagName) {
                return Tag::firstOrCreate(['name' => $tagName]);
            });
            $snippet->tags()->sync($tags->pluck('id')->toArray());
        }

        return response()->json([
            'message' => 'Snippet and tags updated successfully'
        ]);
    }

    public function deleteSnippet($id)
    {
        $user_id = request()->user()->id;

        $snippet = Snippet::where('user_id', $user_id)->findOrFail($id);

        $snippet->update(['is_deleted' => true]);

        return response()->json([
            'message' => 'Snippet marked as deleted successfully'
        ]);
    }

    public function restoreSnippet($id)
    {
        $user_id = request()->user()->id;

        $snippet = Snippet::where('user_id', $user_id)
            ->where('id', $id)
            ->where('is_deleted', true)
            ->firstOrFail();

        // Restore the snippet
        $snippet->update(['is_deleted' => false]);

        return response()->json([
            'message' => 'Snippet restored successfully'
        ]);
    }

    public function searchSnippet(Request $request)
    {
        $request->validate([
            'language' => 'nullable|string',
            'tag' => 'nullable|string'
        ]);

        $user_id = $request->user()->id;

        $snippets = Snippet::where('user_id', $user_id)
            ->where('is_deleted', false);

        // Filter by language if provided
        if ($request->has('language') && $request->language)
            $snippets->where('language', 'like', '%' . $request->language . '%');

        // Filter by tag if provided
        if ($request->has('tag') && $request->tag)
            $snippets->whereHas('tags', function ($query) use ($request) {
                $query->where('name', 'like', '%' . $request->tag . '%');
            });

        $snippets = $snippets->with('tags')->paginate(10);

        return response()->json([
            'message' => true,
            'data' => $snippets
        ]);
    }
}
