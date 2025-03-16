<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use GuzzleHttp\Client;
use League\Csv\Writer;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class ProductController extends Controller
{


    public function index()
{
    $products = Product::all(); 
    
    if (!$products) { 
        $products = []; 
    }
    
    return view('layouts.app', compact('products'));
}


public function edit($id)
{

    $product = Product::findOrFail($id);

 
    return view('products.edit', compact('product'));
}

public function update(Request $request, $id)
{

    $request->validate([
        'name' => 'required',
        'description' => 'required|string',
        'quality_score' => 'nullable|numeric',
        'recommendations' => 'nullable|string',
    ]);
    


    $product = Product::findOrFail($id);


    $product->update([
        'name' => $request->input('name'),
        'description' => $request->input('description'),
        'quality_score' => $request->input('quality_score'),
        'recommendations' => $request->input('recommendations'),
    ]);

  
    return redirect()->route('home')->with('success', 'Продукт успешно обновлен');
}


public function uploadCsv(Request $request)
{
    if ($request->hasFile('csv_file')) {
        $file = $request->file('csv_file');
        
        // Проверка типа файла
        $allowedExtensions = ['csv', 'txt'];
        $extension = $file->getClientOriginalExtension();
        
        if (!in_array($extension, $allowedExtensions)) {
            return redirect()->back()->withErrors(['error' => 'Разрешены только файлы CSV и TXT']);
        }
        
        // Если тип файла допустим, продолжаем загрузку
        $path = $file->storeAs('uploads', $file->getClientOriginalName());

        if ($extension === 'csv') {
            $rows = array_map('str_getcsv', file(storage_path('app/' . $path)));
        } elseif ($extension === 'txt') {
            $rows = [];
            $fileContent = file(storage_path('app/' . $path), FILE_IGNORE_NEW_LINES); // Удаляем символы перевода строки
            foreach ($fileContent as $line) {
                // Разделяем строку на название и описание
                $parts = explode(' ', $line); // Используйте пробел в качестве разделителя
                if (count($parts) >= 2) {
                    $name = $parts[0];
                    $description = implode(' ', array_slice($parts, 1)); // Объединяем остальные части в описание
                    $rows[] = [$name, $description];
                }
            }
        }

        foreach ($rows as $row) {
            // Проверка длины строки
            if (count($row) < 2) {
                // Если строка слишком короткая, пропускаем ее
                continue;
            }
            
            $product = new Product();
            $product->name = trim($row[0]); // Удаляем пробелы
            $product->description = trim($row[1]); // Удаляем пробелы
            
            $product->save();

            $response = $this->sendToGigaChat($product->description);

            $product->quality_score = $response['score'] ?? 0; 
            $product->recommendations = json_encode($response['recommendations'] ?? 'Нет рекомендаций');
            $product->save();
        }

        return redirect()->back()->with('success', 'Файл успешно загружен');
    }

    return redirect()->back()->withErrors(['error' => 'Пожалуйста, выберите CSV-файл']);
}



    
    public function downloadCsv()
    {
        $products = Product::all();
        $data = [];
        foreach ($products as $product) {
            $data[] = [
                'name' => $product->name,
                'description' => $product->description,
                'quality_score' => $product->quality_score,
                'recommendations' => json_decode($product->recommendations, true),
            ];
        }

        $headers = ['Название'. ' ', 'Описание' . ' ', 'Оценка' . ' ', 'Рекомендации' . ' '];

    
        $writer = Writer::createFromString('');
        $writer->insertOne($headers);

  
        foreach ($data as $row) {
            $writer->insertOne($row);
        }

     
        $csvData = $writer->__toString();  

        $csvData = $writer->__toString();
        $csvData = "\xEF\xBB\xBF" . $csvData; 

        $filename = 'processed_products.csv';

        return response($csvData)
            ->header('Content-Type', 'text/csv')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
    }
    
    private function sendToGigaChat($description)
{
    $client = new Client();

    try {
       
        $authResponse = $client->post(config('services.gigachat.oauth_endpoint'), [
            'verify' => false,
            'headers' => [
                'Authorization' => 'Basic ' . base64_encode(
                    config('services.gigachat.client_id') . ':' . config('services.gigachat.client_secret')
                ),
                'Content-Type' => 'application/x-www-form-urlencoded',
                'RqUID' => Str::uuid(), 
            ],
            'form_params' => [
                'grant_type' => 'client_credentials',
                'scope' => 'GIGACHAT_API_PERS', 
            ],
        ]);

        $authData = json_decode((string)$authResponse->getBody(), true);

        if (!isset($authData['access_token'])) {
            throw new \Exception('Не удалось получить токен доступа: ' . json_encode($authData));
        }

        $accessToken = $authData['access_token'];

        // 2. Запрос к API GigaChat
        $apiResponse = $client->post(config('services.gigachat.api_endpoint'), [
            'verify' => false, // Отключение проверки SSL (только для тестов!)
            'headers' => [
                'Authorization' => 'Bearer ' . $accessToken,
                'Content-Type' => 'application/json',
            ],
            'json' => [
                'model' => 'GigaChat',
                'messages' => [
                    [
                        'role' => 'user',
                        'content' => "Оцени описание товара от 1 до 10: $description",
                    ],
                ],
                'temperature' => 0.7,
                'max_tokens' => 100,
            ],
        ]);

        $responseData = json_decode((string)$apiResponse->getBody(), true);

        if (isset($responseData['choices'][0]['message']['content'])) {
            $content = $responseData['choices'][0]['message']['content'];
            preg_match('/Оценка: (\d+)/', $content, $scoreMatch);
            preg_match('/Рекомендации: (.+)/', $content, $recommendationsMatch);

            return [
                'score' => $scoreMatch[1] ?? 0,
                'recommendations' => $recommendationsMatch[1] ?? 'Нет рекомендаций',
            ];
        } else {
            return [
                'score' => 0,
                'recommendations' => 'Нет рекомендаций',
            ];
        }
    } catch (\Exception $e) {
    
        Log::error('Ошибка при запросе к GigaChat: ' . $e->getMessage());

        return [
            'score' => 0,
            'recommendations' => 'Нет рекомендаций',
        ];
    }
}

}
