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
        $user = $request->user();

        if (!$user) {
            return response()->json(['error' => 'User not authenticated'], 401);
        }

        $request->validate([
            'code' => 'required|string',
            'language' => 'required|string',
            'tags' => 'array',
            'is_favourite' => 'boolean',
        ]);

        $user_id = $user->id;

        // Create the snippet and associate it with the user
        $snippet = Snippet::create([
            'user_id' => $user_id,
            'code' => $request->code,
            'language' => $request->language,
            'is_favourite' => $request->is_favourite ?? false,
        ]);

        // Add tags if provided
        if ($request->has('tags') && is_array($request->tags)) {
            $tags = collect($request->tags)->map(function ($tagName) {
                return Tag::firstOrCreate(['name' => $tagName]);
            });

            // Attach the tags to the snippet
            $snippet->tags()->attach($tags->pluck('id')->toArray());
        }

        return response()->json([
            'message' => 'Snippet and tags added successfully',
            'snippet' => $snippet
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

    public function permanentDeleteSnippet(Request $request, $id)
    {
        $user_id = $request->user()->id;

        $snippet = Snippet::where('user_id', $user_id)->firstOrFail($id);

        $snippet->delete();

        return response()->json([
            'message' => 'Snippet permanently deleted'
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

    public function toggleFavourite(Request $request, $id)
    {
        $user_id = $request->user()->id;

        $snippet = Snippet::where('user_id', $user_id)->firstOrFail($id);

        // toggle the favourite value 
        $snippet->update(['is_favourite' => !$snippet->is_favourite]);

        return response()->json([
            'message' => $snippet->is_favourite ?
                'Snippet marked as favourite' :
                'Snippet removed from favourites'
        ]);
    }

    public function displayFavourites(Request $request)
    {
        $user_id = $request->user()->id;

        $snippets = Snippet::where('user_id', $user_id)
            ->where('is_favourite', true)
            ->get();

        return response()->json([
            'snippets' => $snippets
        ]);
    }

    public function getTags()
    {
        $tags = Tag::all();

        return response()->json([
            'tags' => $tags
        ]);
    }
}
