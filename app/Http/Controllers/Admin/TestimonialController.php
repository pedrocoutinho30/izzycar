<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Testimonial;
use Illuminate\Http\Request;

class TestimonialController extends Controller
{
    public function index()
    {
        $testimonials = Testimonial::orderBy('created_at', 'desc')->get();

        $stats = [
            'total'      => $testimonials->count(),
            'publicados' => $testimonials->where('published', true)->count(),
            'media'      => $testimonials->count()
                                ? round($testimonials->avg('rating'), 1)
                                : 0,
            'google'     => $testimonials->where('origin', 'google')->count(),
        ];

        return view('admin.v2.testimonials.index', compact('testimonials', 'stats'));
    }

    public function create()
    {
        $origins = Testimonial::ORIGINS;
        return view('admin.v2.testimonials.create', compact('origins'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'        => 'required|string|max:255',
            'rating'      => 'required|numeric|min:0|max:5|in:0,0.5,1,1.5,2,2.5,3,3.5,4,4.5,5',
            'comment'     => 'string|nullable',
            'origin'      => 'required|in:' . implode(',', array_keys(Testimonial::ORIGINS)),
            'published'   => 'sometimes|boolean',
            'review_date' => 'nullable|date'
        ]);

        $data['published'] = $request->boolean('published');

        Testimonial::create($data);

        return redirect()->route('admin.testimonials.index')
                         ->with('success', 'Testemunho criado com sucesso.');
    }

    public function edit(Testimonial $testimonial)
    {
        $origins = Testimonial::ORIGINS;
        return view('admin.v2.testimonials.edit', compact('testimonial', 'origins'));
    }

    public function update(Request $request, Testimonial $testimonial)
    {
        $data = $request->validate([
            'name'        => 'required|string|max:255',
            'rating'      => 'required|numeric|min:0|max:5|in:0,0.5,1,1.5,2,2.5,3,3.5,4,4.5,5',
            'comment'     => 'string|nullable',
            'origin'      => 'required|in:' . implode(',', array_keys(Testimonial::ORIGINS)),
            'published'   => 'sometimes|boolean',
            'review_date' => 'nullable|date',
        ]);

        $data['published'] = $request->boolean('published');

        $testimonial->update($data);

        return redirect()->route('admin.testimonials.index')
                         ->with('success', 'Testemunho actualizado.');
    }

    public function destroy(Testimonial $testimonial)
    {
        $testimonial->delete();

        return redirect()->route('admin.testimonials.index')
                         ->with('success', 'Testemunho eliminado.');
    }

    public function togglePublished(Testimonial $testimonial)
    {
        $testimonial->update(['published' => !$testimonial->published]);

        return response()->json(['published' => $testimonial->published]);
    }
}
