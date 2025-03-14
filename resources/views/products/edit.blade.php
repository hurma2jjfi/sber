<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-GLHLhV/vQIGnqrzz84Xt6n7d91WcQw4u9Xka9zuSeEMwhHQ81Svc6oXVvpFO5uA6sk" crossorigin="anonymous">
    <title>Редактирование продукта</title>
</head>
<body>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <h1>Редактирование продукта</h1>

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

                <!-- Форма редактирования -->
                <form method="POST" action="{{ route('products.update', $product->id) }}">
                    @csrf
                    @method('PUT') <!-- Используем метод PUT для обновления -->

                    <div class="mb-3">
                        <label for="name" class="form-label">Название</label>
                        <input type="text" id="name" name="name" class="form-control" value="{{ $product->name }}" required>
                    </div>

                    <div class="mb-3">
                        <label for="description" class="form-label">Описание</label>
                        <textarea id="description" name="description" class="form-control" required>{{ $product->description }}</textarea>
                    </div>

                    <div class="mb-3">
                        <label for="quality_score" class="form-label">Оценка</label>
                        <input type="number" id="quality_score" name="quality_score" class="form-control" value="{{ $product->quality_score }}">
                    </div>

                    <div class="mb-3">
                        <label for="recommendations" class="form-label">Рекомендации</label>
                        <textarea id="recommendations" name="recommendations" class="form-control">{{ $product->recommendations }}</textarea>
                    </div>

                    <button type="submit" class="btn btn-primary">Обновить</button>
                    <a href="{{ route('home') }}" class="btn btn-secondary">Отмена</a>
                </form>
            </div>
        </div>
    </div>

    <script defer src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
</body>
</html>