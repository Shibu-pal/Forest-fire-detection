<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Inertia\Inertia;
use Illuminate\Support\Facades\Storage;

class DataController extends Controller
{
    //
    public function checkData(Request $request) {
        $request->validate([
            'temperature' => 'required|numeric',
            'humidity' => 'required|numeric',
            'wind_speed' => 'required|numeric',
            'vegetation_type' => 'required|string',
            'elevation' => 'required|numeric',
        ]);
        $response = Http::post('https://forest-fire-detection-rugt.onrender.com/predict/data', $request->all());

        if ($response->successful()) {
            $result = $response->json();
            return Inertia::render('FirePredictionOutput', [
                'fire_risk' => $result['fire_risk'] == 1 ? 'Fire' : 'No fire',
                'probability' => $result['probability'],
            ]);
        }
        return Inertia::render('FirePredictionOutput', ['error' => 'API Error']);
    }
    
    public function checkImage(Request $request) {
        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg|max:2048',
        ]);
        try {
            // 2. Open the file for reading
            $imageFile = $request->file('image');
            
            // 3. Send the file to the persistent Python API via HTTP POST
            $response = Http::attach(
                'file', 
                file_get_contents($imageFile->getRealPath()), 
                $imageFile->getClientOriginalName()
            )->post('https://forest-fire-detection-rugt.onrender.com/predict/image');

            if ($response->successful()) {
                $result = $response->json();
                
                // 4. Map the API response to the frontend labels
                $fireRiskLabel = ($result['fire_risk'] == 1) ? 'Fire' : 'No fire';
                
                return Inertia::render('FirePredictionOutput', [
                    'fire_risk' => $fireRiskLabel,
                    'probability' => $result['probability'],
                ]);
            }

            return Inertia::render('FirePredictionOutput', [
                'error' => 'Image prediction API failed: ' . $response->body(),
            ]);

        } catch (\Exception $e) {
            return Inertia::render('FirePredictionOutput', [
                'error' => 'An error occurred: ' . $e->getMessage(),
            ]);
        }
    }
}
