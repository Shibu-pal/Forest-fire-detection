<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
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
        $input_json = json_encode($request->all());
        $command = 'python3 ../backend/predict_fire.py';
        $descriptorspec = [
            0 => ['pipe', 'r'],
            1 => ['pipe', 'w'],
            2 => ['pipe', 'w']
        ];
        $process = proc_open($command, $descriptorspec, $pipes);

        if (is_resource($process)) {
            fwrite($pipes[0], $input_json);
            fclose($pipes[0]);

            $output = stream_get_contents($pipes[1]);
            fclose($pipes[1]);

            $error_output = stream_get_contents($pipes[2]);
            fclose($pipes[2]);

            $return_value = proc_close($process);

            if ($return_value !== 0) {
                return Inertia::render('FirePredictionOutput', [
                    'error' => 'Prediction script error: ' . $error_output,
                ]);
            }

            $result = json_decode($output, true);
            $fire_risk = $result['fire_risk'] ?? null;
            $fire_risk = $fire_risk == 1 ? 'Fire' : 'No fire';
            $probability = $result['probability'] ?? 0;

            return Inertia::render('FirePredictionOutput', [
                'fire_risk' => $fire_risk,
                'probability' => $probability,
            ]);
        } else {
            return Inertia::render('FirePredictionOutput', [
                'error' => 'Unable to start prediction script',
            ]);
        }
    }
    
    public function checkImage(Request $request) {
        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg|max:2048',
        ]);
        $tempDir = storage_path('app/public/temp');
        if (!file_exists($tempDir)) {
            mkdir($tempDir, 0755, true);
        }
        $imagePath = $request->image->store('temp','public');

        $fullImagePath = storage_path('app/public/' . $imagePath);
        if (!file_exists($fullImagePath)) {
            return Inertia::render('FirePredictionOutput', [
                'error' => 'Failed to store image temporarily at '.$fullImagePath,
            ]);
        }
        $command = 'python3 ../backend/image_predict_fire.py';
        $descriptorspec = [
            0 => ['pipe', 'r'],
            1 => ['pipe', 'w'],
            2 => ['pipe', 'w']
        ];
        $process = proc_open($command, $descriptorspec, $pipes);

        if (is_resource($process)) {
            fwrite($pipes[0], $fullImagePath);
            fclose($pipes[0]);

            $output = stream_get_contents($pipes[1]);
            fclose($pipes[1]);

            $error_output = stream_get_contents($pipes[2]);
            fclose($pipes[2]);

            $return_value = proc_close($process);
            if (file_exists($fullImagePath)) {
                unlink($fullImagePath);
            }

            if ($return_value !== 0) {
                return Inertia::render('FirePredictionOutput', [
                    'error' => 'Image prediction script error: ' . $error_output,
                ]);
            }

            $result = json_decode($output, true);
            
            if (isset($result['error'])) {
                return Inertia::render('FirePredictionOutput', [
                    'error' => 'Image prediction error: ' . $result['error'],
                ]);
            }
            
            $fire_risk = $result['fire_risk'] ?? null;
            $fire_risk = $fire_risk == 1 ? 'Fire' : 'No fire';
            $probability = $result['probability'] ?? 0;

            return Inertia::render('FirePredictionOutput', [
                'fire_risk' => $fire_risk,
                'probability' => $probability,
            ]);
        } else {
            if (file_exists($fullImagePath)) {
                unlink($fullImagePath);
            }
            
            return Inertia::render('FirePredictionOutput', [
                'error' => 'Unable to start image prediction script',
            ]);
        }
    }
}
