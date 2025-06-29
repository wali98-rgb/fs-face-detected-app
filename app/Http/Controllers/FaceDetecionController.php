<?php

namespace App\Http\Controllers;

use Google\Cloud\Vision\V1\Image;
use Google\Cloud\Vision\V1\Feature;
use Google\Cloud\Vision\V1\AnnotateImageRequest;
use Google\Cloud\Vision\V1\BatchAnnotateImagesRequest;
use Google\Cloud\Vision\V1\Client\ImageAnnotatorClient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class FaceDetecionController extends Controller
{
    public function detectFace(Request $request)
    {
        $request->validate([
            'image' => 'required|image|max:2048',
        ]);

        try {
            $imageFile = $request->file('image');
            $imageData = file_get_contents($imageFile->getRealPath());

            $imageAnnotator = new ImageAnnotatorClient([
                'credentials' => base_path('storage/app/google/face-detection-464410-1ed95e3749ca.json')
            ]);

            // Buat objek Image dan Feature
            $image = (new Image())->setContent($imageData);
            $feature = (new Feature())->setType(Feature\Type::FACE_DETECTION);

            // Buat AnnotateImageRequest
            $annotateImageRequest = (new AnnotateImageRequest())
                ->setImage($image)
                ->setFeatures([$feature]);

            // Bungkus dalam BatchAnnotateImagesRequest
            $batchRequest = (new BatchAnnotateImagesRequest())
                ->setRequests([$annotateImageRequest]);

            // Panggil batchAnnotateImages dengan objek batch
            $response = $imageAnnotator->batchAnnotateImages($batchRequest);

            $imageAnnotator->close();

            $faceAnnotations = $response->getResponses()[0]->getFaceAnnotations();

            if (empty($faceAnnotations)) {
                return response()->json(['message' => 'No faces detected.']);
            }

            // Format bounding box hasil deteksi
            $faces = [];
            foreach ($faceAnnotations as $face) {
                $vertices = [];
                foreach ($face->getBoundingPoly()->getVertices() as $vertex) {
                    $vertices[] = [
                        'x' => $vertex->getX(),
                        'y' => $vertex->getY()
                    ];
                }
                $faces[] = ['boundingPoly' => $vertices];
            }

            return response()->json(['faces' => $faces]);
        } catch (\Exception $e) {
            Log::error("Face detection error: " . $e->getMessage());
            return response()->json([
                'error' => true,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function detect()
    {
        $client = new ImageAnnotatorClient([
            'credentials' => base_path('storage\app\google\face-detection-464410-1ed95e3749ca.json'),
        ]);

        $imagePath = public_path('images/photo.jpg');
        $imageData = file_get_contents($imagePath);

        $response = $client->faceDetection($imageData);
        $faces = $response->getFaceAnnotations();

        foreach ($faces as $face) {
            $boundingPoly = $face->getBoundingPoly();
            // Lakukan sesuatu dengan koordinat wajah
        }

        $client->close();

        // return response()->json(['faces_detected' => count($results->faces())]);
    }

    public function detectFaceForm()
    {
        return view('detect-face.index');
    }
}
