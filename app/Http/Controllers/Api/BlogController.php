<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Blog;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class BlogController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Blog::query();

        if ($search = $request->string('q')) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('content', 'like', "%{$search}%")
                  ->orWhere('keywords', 'like', "%{$search}%");
            });
        }

        return response()->json(
            $query->latest('created_at')->paginate(
                perPage: (int) $request->integer('per_page') ?: 15
            )
        );
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'content' => ['required', 'string'],
            'keywords' => ['nullable', 'string', 'max:512'],
        ]);

        $blog = Blog::create($data);

        return response()->json($blog, Response::HTTP_CREATED);
    }

    /**
     * Display the specified resource.
     */
    public function show(Blog $blog)
    {
        return response()->json($blog);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Blog $blog)
    {
        $data = $request->validate([
            'title' => ['sometimes', 'required', 'string', 'max:255'],
            'content' => ['sometimes', 'required', 'string'],
            'keywords' => ['nullable', 'string', 'max:512'],
        ]);

        $blog->update($data);

        return response()->json($blog);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Blog $blog)
    {
        $blog->delete();

        return response()->json(null, Response::HTTP_NO_CONTENT);
    }
}
