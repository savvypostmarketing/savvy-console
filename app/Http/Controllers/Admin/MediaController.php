<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class MediaController extends Controller
{
    /**
     * Upload a media file (image, video, document).
     */
    public function upload(Request $request): JsonResponse
    {
        $request->validate([
            'file' => ['required', 'file', 'max:20480'], // 20MB max
            'folder' => ['nullable', 'string', 'max:100'],
        ]);

        $file = $request->file('file');
        $folder = $request->input('folder', 'media');

        // Determine file type
        $mimeType = $file->getMimeType();
        $isImage = str_starts_with($mimeType, 'image/');
        $isVideo = str_starts_with($mimeType, 'video/');

        // Generate unique filename
        $extension = $file->getClientOriginalExtension();
        $filename = Str::uuid() . '.' . $extension;

        // Store file
        $path = $file->storeAs($folder, $filename, 'public');

        if (!$path) {
            return response()->json([
                'success' => 0,
                'message' => 'Failed to upload file',
            ], 500);
        }

        $url = url('storage/' . $path);

        // Return format compatible with Editor.js Image Tool
        return response()->json([
            'success' => 1,
            'file' => [
                'url' => $url,
                'path' => $path,
                'name' => $file->getClientOriginalName(),
                'size' => $file->getSize(),
                'type' => $mimeType,
                'isImage' => $isImage,
                'isVideo' => $isVideo,
            ],
        ]);
    }

    /**
     * Upload image specifically for Editor.js (supports byFile and byUrl).
     */
    public function uploadImage(Request $request): JsonResponse
    {
        // Handle file upload
        if ($request->hasFile('image')) {
            $request->validate([
                'image' => ['required', 'image', 'max:10240'], // 10MB max for images
            ]);

            $file = $request->file('image');
            $extension = $file->getClientOriginalExtension();
            $filename = Str::uuid() . '.' . $extension;

            $path = $file->storeAs('posts/media', $filename, 'public');

            if (!$path) {
                return response()->json([
                    'success' => 0,
                    'message' => 'Failed to upload image',
                ], 500);
            }

            return response()->json([
                'success' => 1,
                'file' => [
                    'url' => url('storage/' . $path),
                ],
            ]);
        }

        // Handle URL upload (download and save)
        if ($request->has('url')) {
            $request->validate([
                'url' => ['required', 'url'],
            ]);

            try {
                $url = $request->input('url');
                $contents = file_get_contents($url);

                if (!$contents) {
                    throw new \Exception('Could not download image');
                }

                // Get extension from URL or default to jpg
                $extension = pathinfo(parse_url($url, PHP_URL_PATH), PATHINFO_EXTENSION) ?: 'jpg';
                $filename = Str::uuid() . '.' . $extension;

                $path = 'posts/media/' . $filename;
                Storage::disk('public')->put($path, $contents);

                return response()->json([
                    'success' => 1,
                    'file' => [
                        'url' => url('storage/' . $path),
                    ],
                ]);
            } catch (\Exception $e) {
                return response()->json([
                    'success' => 0,
                    'message' => 'Failed to download image from URL',
                ], 400);
            }
        }

        return response()->json([
            'success' => 0,
            'message' => 'No image provided',
        ], 400);
    }

    /**
     * Delete a media file.
     */
    public function delete(Request $request): JsonResponse
    {
        $request->validate([
            'path' => ['required', 'string'],
        ]);

        $path = $request->input('path');

        // Security: Only allow deletion from specific folders
        $allowedFolders = ['posts/media', 'portfolio', 'testimonials', 'media'];
        $isAllowed = false;

        foreach ($allowedFolders as $folder) {
            if (str_starts_with($path, $folder)) {
                $isAllowed = true;
                break;
            }
        }

        if (!$isAllowed) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid file path',
            ], 403);
        }

        if (Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);

            return response()->json([
                'success' => true,
                'message' => 'File deleted successfully',
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'File not found',
        ], 404);
    }

    /**
     * Fetch link metadata for Editor.js Link Tool.
     */
    public function fetchLink(Request $request): JsonResponse
    {
        $request->validate([
            'url' => ['required', 'url'],
        ]);

        $url = $request->input('url');

        try {
            $html = @file_get_contents($url);

            if (!$html) {
                throw new \Exception('Could not fetch URL');
            }

            // Parse title
            preg_match('/<title>(.*?)<\/title>/i', $html, $titleMatches);
            $title = $titleMatches[1] ?? '';

            // Parse description
            preg_match('/<meta[^>]*name=["\']description["\'][^>]*content=["\'](.*?)["\']/i', $html, $descMatches);
            $description = $descMatches[1] ?? '';

            // Parse OG image
            preg_match('/<meta[^>]*property=["\']og:image["\'][^>]*content=["\'](.*?)["\']/i', $html, $imageMatches);
            $image = $imageMatches[1] ?? '';

            return response()->json([
                'success' => 1,
                'link' => $url,
                'meta' => [
                    'title' => html_entity_decode($title, ENT_QUOTES, 'UTF-8'),
                    'description' => html_entity_decode($description, ENT_QUOTES, 'UTF-8'),
                    'image' => [
                        'url' => $image,
                    ],
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => 0,
                'message' => 'Failed to fetch link metadata',
            ], 400);
        }
    }
}
