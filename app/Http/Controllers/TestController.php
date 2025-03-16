<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use League\Csv\Reader;
use App\Models\Product;

class TestController extends Controller
{
    public function analyze(Request $request)
{
    $request->validate([
        'csvFile' => 'required|file|mimes:csv,txt',
    ]);

    try {
        $file = $request->file('csvFile');
        $csv = Reader::createFromPath($file->getRealPath(), 'r');
        $csv->setHeaderOffset(0);

        $results = [];
        foreach ($csv as $record) {
            $description = $record['description'] ?? '';
            if (empty($description)) {
                continue;
            }

            // Отправка описания в GigaChat для анализа
            $analysisResult = $this->analyzeDescription($description);

            // Сохранение результатов в базу данных
            $product = Product::create([
                'name' => $record['product'] ?? 'N/A',
                'description' => $description,
                'quality_score' => $analysisResult['score'] ?? null,
                'recommendations' => $analysisResult['recommendations'] ?? null,
            ]);

            $results[] = $product;
        }

        return response()->json(['results' => $results]);
    } catch (\Exception $e) {
        Log::error("Ошибка при анализе файла: " . $e->getMessage());
        return response()->json(['error' => 'Ошибка при анализе файла.'], 500);
    }
}

    public function preview(Request $request)
    {
        $request->validate([
            'csvFile' => 'required|file|mimes:csv,txt',
        ]);

        try {
            $file = $request->file('csvFile');
            $csv = Reader::createFromPath($file->getRealPath(), 'r');
            $csv->setHeaderOffset(0);

            $previewData = [];
            foreach ($csv as $record) {
                $previewData[] = [
                    'product' => $record['product'] ?? 'N/A',
                    'description' => $record['description'] ?? '',
                ];
            }

            return response()->json(['preview' => $previewData]);
        } catch (\Exception $e) {
            Log::error("Ошибка при предпросмотре файла: " . $e->getMessage());
            return response()->json(['error' => 'Ошибка при предпросмотре файла.'], 500);
        }
    }

    public function getResults()
    {
        $products = Product::all();
        return response()->json(['results' => $products]);
    }

    private function analyzeDescription($description)
    {
        $authorizationKey = 'ZTJjOTkyNzctMGNjYS00ODYwLTgwZjEtNDFlNzliZTliMzAwOjNmMGUwZWNkLWEyZDQtNDdkYy1hMzc1LTQwOTYyNTZmNTE5YQ==';
        $rqUID = uniqid();

        $response = Http::withoutVerifying()->withHeaders([
            'Content-Type' => 'application/x-www-form-urlencoded',
            'Accept' => 'application/json',
            'Authorization' => 'Basic ' . base64_encode($authorizationKey),
            'RqUID' => $rqUID,
        ])->asForm()->post('https://ngw.devices.sberbank.ru:9443/api/v2/oauth', [
            'grant_type' => 'client_credentials',
            'scope' => 'GIGACHAT_API_PERS',
        ]);

        if ($response->successful()) {
            $token = $response->json()['access_token'];

            // Отправка описания в GigaChat для анализа
            $analysisResponse = Http::withHeaders([
                'Authorization' => 'Bearer ' . $token,
                'Content-Type' => 'application/json',
            ])->post('https://gigachat.devices.sberbank.ru/api/v1/analyze', [
                'text' => $description,
            ]);

            if ($analysisResponse->successful()) {
                return $analysisResponse->json();
            }
        }

        return ['score' => 'N/A', 'recommendations' => 'N/A'];
    }
}