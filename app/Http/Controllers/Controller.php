<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use function Laravel\Prompts\text;

class Controller
{
    public function showHome()
    {
        return view('home');
    }

    function callAudioAPI($curl, $audioFile)
    {   
        // Open audio file and read content as binary
        $audioData = file_get_contents($audioFile->getRealPath());

        // Set headers for API call
        $headers = [
            'Authorization: Bearer ********', // Hugging Face token
            'Content-Type: audio/wav',
        ];

        // Initialize curl session
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $curl);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $audioData);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        // Execute curl session and get the response
        $response = curl_exec($ch);

        // Check for curl errors
        if ($response === false) {
            $error = curl_error($ch);
            curl_close($ch);
            return response()->json(['error' => 'Curl Error Occurred: ' . $error], 500);
        }

        curl_close($ch);
        
        // Decode the JSON response from Hugging Face API
        return json_decode($response, true);
    }

    function callTextAPI($curl, $textData)
    {
        // Set headers for API call
        $headers = [
            'Authorization: Bearer ********', // Hugging Face token
            'Content-Type: application/json',
        ];

        // Initialize curl session
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $curl);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $textData);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        // Execute curl session and get the response
        $response = curl_exec($ch);

        // Check for curl errors
        if ($response === false) {
            $error = curl_error($ch);
            curl_close($ch);
            return response()->json(['error' => 'Curl Error Occurred: ' . $error], 500);
        }

        curl_close($ch);
        
        // Decode the JSON response from Hugging Face API
        return json_decode($response, true);
    }

    public function printText(Request $request)
    {
        $request->validate([
            'audio' => 'required'
        ]);

        $audioData = $request->file('audio');

        // URLs for Hugging Face APIs
        $audioCurl = "https://api-inference.huggingface.co/models/openai/whisper-large-v3-turbo";
        $textCurl = "https://api-inference.huggingface.co/models/deepset/roberta-base-squad2";

        
        $audioResponse = $this->callAudioAPI($audioCurl, $audioData);

        // Define text data for text API
        $textDataName = json_encode([
            "inputs" => [
                "question" => "What is the name of the project?", 
                "context" => $audioResponse["text"]
            ]
        ]);

        $textDataPurpose = json_encode([
            "inputs" => [
                "question" => "What is the purpose of the project?", 
                "context" => $audioResponse["text"]
            ]
        ]);

        $textDataCost = json_encode([
            "inputs" => [
                "question" => "How much is the cost of the project?", 
                "context" => $audioResponse["text"]
            ]
        ]);

        $textResponseName = $this->callTextAPI($textCurl, $textDataName);
        $textResponsePurpose = $this->callTextAPI($textCurl, $textDataPurpose);
        $textResponseCost = $this->callTextAPI($textCurl, $textDataCost);

        // Pass responses to the Blade view
        return response()->json([
            'audioResponse' => $audioResponse["text"], 
            'textResponseName' => $textResponseName["answer"],
            'textResponsePurpose' => $textResponsePurpose["answer"],
            'textResponseCost' => $textResponseCost["answer"]
        ]);
    }
}
