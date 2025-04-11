<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;

class MediaController extends Controller
{
    public function upload(Request $request)
    {
        // Validate the incoming request
        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);
        // Handle the image upload
        $cloudinaryImage = $request->file('image')->storeOnCloudinary('uploads');
        // Get the URL of the uploaded image
        $imageUrl = $cloudinaryImage->getSecurePath();
        // Optionally, save the URL to the database or return it in the response
        return response()->json(['url' => $imageUrl]);
    }
}