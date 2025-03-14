<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-GLHLhV/vQIGnqrzz84Xt6n7d91WcQw4u9Xka9zuSeEMwhHQ81Svc6oXVvpFO5uA6sk" crossorigin="anonymous">
    <title>{{ config('app.name') }}</title>
</head>
<body>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <h1>Загрузка CSV-файла</h1>
                @if(session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                @endif
                @if(session('error'))
                    <div class="alert alert-danger">{{ session('error') }}</div>
                @endif

                <!-- Вывод ошибок валидации -->
                @if ($errors->any())
                    <div class="alert alert-danger">
                        <ul>
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('upload.csv') }}" enctype="multipart/form-data">
                    @csrf
                    <div class="mb-3">
                        <label for="csv_file" class="form-label">Выберите CSV-файл:</label>
                        <input type="file" id="csv_file" name="csv_file" class="form-control">
                    </div>
                    <button type="submit" class="btn btn-primary">Загрузить</button>
                </form>
                <br><br>
                <a href="{{ route('download.csv') }}" class="btn btn-info">Скачать обработанный CSV-файл</a>

                <!-- Таблица для отображения оценок и рекомендаций -->
                @if(isset($products) && count($products))
                <h2 class="mt-5">Результаты оценки и рекомендации</h2>
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Название</th>
                            <th>Описание</th>
                            <th>Оценка</th>
                            <th>Рекомендации</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($products as $product)
                        <tr>
                            <td>{{ $product->name }}</td>
                            <td>{{ $product->description }}</td>
                            <td>{{ $product->quality_score }}</td>
                            <td>{{ $product->recommendations }}</td>
                            <td>
                                <a href="{{ route('products.edit', $product->id) }}" class="btn btn-sm btn-warning">Редактировать</a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                @else
                <p>Нет данных для отображения.</p>
                @endif
            </div>
        </div>
    </div>

@vite('resources/js/app.js')


<script defer src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
</body>
</html>
