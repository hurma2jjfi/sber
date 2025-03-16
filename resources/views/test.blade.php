<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Умное ранжирование товаров</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-10">
                <div class="card shadow">
                    <div class="card-header bg-primary text-white">
                        <h3 class="mb-0">Умное ранжирование товаров</h3>
                    </div>
                    <div class="card-body">
                        <!-- Форма для загрузки CSV-файла -->
                        <div class="mb-4">
                            <label for="csvFile" class="form-label">Загрузите CSV-файл с товарами</label>
                            <input type="file" class="form-control" id="csvFile" accept=".csv">
                        </div>

                        <!-- Кнопка для предпросмотра файла -->
                        <div class="d-grid gap-2 d-md-flex justify-content-md-end mb-4">
                            <button class="btn btn-secondary me-md-2" onclick="previewFile()">
                                Предпросмотр
                            </button>
                            <button class="btn btn-primary" onclick="analyzeFile()">
                                Анализировать
                            </button>
                        </div>

                        <!-- Таблица для предпросмотра файла -->
                        <div class="mb-3">
                            <h5>Предпросмотр файла</h5>
                            <table class="table table-bordered" id="previewTable">
                                <thead>
                                    <tr>
                                        <th>Товар</th>
                                        <th>Описание</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <!-- Данные предпросмотра будут добавлены сюда -->
                                </tbody>
                            </table>
                        </div>

                        <!-- Таблица для отображения результатов -->
                        <div class="mb-3">
                            <h5>Результаты анализа</h5>
                            <table class="table table-bordered" id="resultsTable">
                                <thead>
                                    <tr>
                                        <th>Товар</th>
                                        <th>Описание</th>
                                        <th>Оценка</th>
                                        <th>Рекомендации</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <!-- Результаты будут добавлены сюда -->
                                </tbody>
                            </table>
                        </div>

                        <!-- Сообщения о статусе -->
                        <div id="status" class="alert d-none"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        async function previewFile() {
            const fileInput = document.getElementById('csvFile');
            const status = document.getElementById('status');

            if (!fileInput.files.length) {
                showStatus('Пожалуйста, выберите файл.', 'danger');
                return;
            }

            const file = fileInput.files[0];
            const formData = new FormData();
            formData.append('csvFile', file);

            status.classList.add('d-none');

            try {
                const response = await fetch('/api/preview', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    },
                    body: formData,
                });

                if (!response.ok) {
                    const errorData = await response.json();
                    showStatus(`Ошибка: ${errorData.error}`, 'danger');
                    return;
                }

                const data = await response.json();
                if (data.preview) {
                    displayPreview(data.preview);
                    showStatus('Предпросмотр завершен!', 'success');
                } else {
                    showStatus('Ошибка: Нет данных для отображения.', 'danger');
                }
            } catch (error) {
                showStatus(`Ошибка подключения: ${error.message}`, 'danger');
            }
        }

        function displayPreview(previewData) {
            const tableBody = document.querySelector('#previewTable tbody');
            tableBody.innerHTML = '';

            previewData.forEach(item => {
                const row = document.createElement('tr');
                row.innerHTML = `
                    <td>${item.product}</td>
                    <td>${item.description}</td>
                `;
                tableBody.appendChild(row);
            });
        }

        async function analyzeFile() {
            const fileInput = document.getElementById('csvFile');
            const status = document.getElementById('status');

            if (!fileInput.files.length) {
                showStatus('Пожалуйста, выберите файл.', 'danger');
                return;
            }

            const file = fileInput.files[0];
            const formData = new FormData();
            formData.append('csvFile', file);

            status.classList.add('d-none');

            try {
                const response = await fetch('/api/analyze', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    },
                    body: formData,
                });

                if (!response.ok) {
                    const errorData = await response.json();
                    showStatus(`Ошибка: ${errorData.error}`, 'danger');
                    return;
                }

                const data = await response.json();
                if (data.results) {
                    fetchResults();
                    showStatus('Анализ завершен!', 'success');
                } else {
                    showStatus('Ошибка: Нет данных для отображения.', 'danger');
                }
            } catch (error) {
                showStatus(`Ошибка подключения: ${error.message}`, 'danger');
            }
        }

        async function fetchResults() {
            try {
                const response = await fetch('/api/results');
                if (!response.ok) {
                    throw new Error('Ошибка при получении результатов.');
                }

                const data = await response.json();
                if (data.results) {
                    displayResults(data.results);
                }
            } catch (error) {
                showStatus(`Ошибка: ${error.message}`, 'danger');
            }
        }

        function displayResults(results) {
            const tableBody = document.querySelector('#resultsTable tbody');
            tableBody.innerHTML = '';

            results.forEach(item => {
                const row = document.createElement('tr');
                row.innerHTML = `
                    <td>${item.name}</td>
                    <td>${item.description}</td>
                    <td>${item.quality_score}</td>
                    <td>${item.recommendations}</td>
                `;
                tableBody.appendChild(row);
            });
        }

        function showStatus(message, type = 'info') {
            const status = document.getElementById('status');
            status.className = `alert alert-${type}`;
            status.textContent = message;
            status.classList.remove('d-none');
        }
    </script>
</body>
</html>