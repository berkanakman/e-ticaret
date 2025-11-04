<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;


class UploadController extends Controller
{
    public function quillImage(Request $request)
    {
        $request->validate([
            'image' => 'required|image|max:5120' // 5 MB
        ]);

        $file = $request->file('image');
        $name = Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)) . '-' . time() . '.' . $file->getClientOriginalExtension();
        $path = $file->storeAs('uploads/quill', $name, 'public'); // storage/app/public/uploads/quill
        $url = URL::to(Storage::disk('public')->url($path));
        // Quill expects JSON with link to image
        return response()->json(['success' => true, 'url' => $url]);
    }
}
