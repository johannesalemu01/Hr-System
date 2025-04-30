<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;

class MediaController extends Controller
{
    public function upload(Request $request)
    {
        
        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);
        
        $cloudinaryImage = $request->file('image')->storeOnCloudinary('uploads');
        
        $imageUrl = $cloudinaryImage->getSecurePath();
        
        return response()->json(['url' => $imageUrl]);
    }
}