<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet"
          integrity="sha384-1BmE4kWBq78iYhFldvKuhfTAU6auU8tT94WrHftjDbrCEXSU1oBoqyl2QvZ6jIW3" crossorigin="anonymous">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css"
          integrity="sha512-9usAa10IRO0HhonpyAIVpjrylPvoDwiPUiKdWk5t3PyolY1cOd4DSE0Ga+ri4AuTroPR5aQvXU9xC6qOPnzFeg=="
          crossorigin="anonymous" referrerpolicy="no-referrer"/>
    @vite('resources/css/app.css')
    <title>{{ config('app.name') }}</title>
</head>
<body>

<div class="container mt-5 pt-5">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <h1 class="text-center fw-bold text-black">Загрузка CSV-файла</h1>
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

            <form method="POST" action="{{ route('upload.csv') }}" enctype="multipart/form-data" id="uploadForm">
                @csrf
                <div class="mb-3">
                    <label for="csv_file" class="form-label">Выберите CSV-файл:</label>
                    <div id="drop-area">
                        <p>Перетащите сюда файлы формата TXT, CSV или нажмите, чтобы выбрать</p>
                        <input type="file" id="csv_file" name="csv_file" class="form-control">
                    </div>
                </div>

                <div class="d-flex align-items-center">
                    <button type="submit" class="upload btn btn-dark" id="uploadButton">
                        <i class="fas fa-upload me-2"></i>
                        Загрузить
                        <span class="spinner-border spinner-border-sm ms-2 d-none" role="status"
                              aria-hidden="true"></span>
                    </button>
                    @if(isset($products) && count($products))
                        <a href="{{ route('download.csv') }}" class="btn btn-info ms-3"><i
                                class="fas fa-download me-2"></i>Скачать
                            CSV-файл</a>
                    @else
                        <a href="#" class="btn btn-info ms-3 disabled"><i class="fas fa-download me-2"></i>Скачать
                            CSV-файл</a>
                    @endif
                </div>
            </form>

            <div id="csvPreview" class="mt-3 blurred">
                <p>Предварительный просмотр CSV-файла</p>
            </div>
            <button id="showPreviewButton" class="btn btn-primary mt-2" data-bs-toggle="modal"
                    data-bs-target="#csvModal" disabled><i class="fas fa-eye me-2"></i>Показать предпросмотр
                <span class="spinner-border spinner-border-sm ms-2 d-none" role="status"
                      aria-hidden="true"></span></button>

            <br><br>

        <!-- Таблица для отображения оценок и рекомендаций -->

            <div class="section__center">
                @if(isset($products) && count($products))
                    <table class="table table-striped table-responsive">
                        <thead>
                        <tr>
                            <th>Название</th>
                            <th>Описание</th>
                            <th>Оценка</th>
                            <th>Рекомендации</th>
                            <th>Действия</th>
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
                                    <button class="btn btn-sm btn-warning edit-product-btn" data-bs-toggle="modal"
                                            data-bs-target="#editProductModal" data-product-id="{{ $product->id }}"
                                            data-product-name="{{ $product->name }}"
                                            data-product-description="{{ $product->description }}"
                                            data-product-quality_score="{{ $product->quality_score }}"
                                            data-product-recommendations="{{ $product->recommendations }}">
                                        Редактировать
                                        <span class="spinner-border spinner-border-sm ms-2 d-none" role="status"
                                              aria-hidden="true"></span>
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                @else
                    <p class="text-center">Нет данных для отображения.</p>
                @endif
            </div>
        </div>
    </div>
</div>... <!-- Модальное окно для предпросмотра CSV -->
<div class="modal fade" id="csvModal" tabindex="-1" aria-labelledby="csvModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="csvModalLabel">Предварительный просмотр CSV</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="modalCsvPreview"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Закрыть</button>
            </div>
        </div>
    </div>
</div>

<!-- Модальное окно для редактирования продукта -->
<div class="modal fade" id="editProductModal" tabindex="-1" aria-labelledby="editProductModalLabel"
     aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editProductModalLabel">Редактировать продукт</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="editProductForm" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="mb-3">
                        <label for="editProductName" class="form-label">Название</label>
                        <input type="text" class="form-control" id="editProductName" name="name" required>
                    </div>
                    <div class="mb-3">
                        <label for="editProductDescription" class="form-label">Описание</label>
                        <textarea class="form-control" id="editProductDescription" name="description" rows="3"
                                  required></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="editProductQualityScore" class="form-label">Оценка</label>
                        <input type="number" class="form-control" id="editProductQualityScore" name="quality_score">
                    </div>
                    <div class="mb-3">
                        <label for="editProductRecommendations" class="form-label">Рекомендации</label>
                        <textarea class="form-control" id="editProductRecommendations" name="recommendations"
                                  rows="3"></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary" id="saveChangesButton">Сохранить изменения
                        <span class="spinner-border spinner-border-sm ms-2 d-none" role="status"
                              aria-hidden="true"></span>
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

@vite('resources/js/app.js')
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-ka7Sk0Gln4gmtz2MlQnikT1wXgYsOg+OMhuP+IlRH9sENBO0LRn5q+8nbTov4+1p"
        crossorigin="anonymous"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const fileInput = document.getElementById('csv_file');
        const csvPreview = document.getElementById('csvPreview');
        const modalCsvPreview = document.getElementById('modalCsvPreview');
        const showPreviewButton = document.getElementById('showPreviewButton');
        const uploadButton = document.querySelector('.upload');
        const spinner = uploadButton.querySelector('.spinner-border');
        const uploadForm = document.getElementById('uploadForm');
        // Drag and Drop
        let dropArea = document.getElementById('drop-area');

        // Get the editProductModal element
        const editProductModal = document.getElementById('editProductModal');

        // Предотвращаем действия браузера по умолчанию
        ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
            dropArea.addEventListener(eventName, preventDefaults, false)
        });

        function preventDefaults(e) {
            e.preventDefault();
            e.stopPropagation();
        }

        // Выделяем drop area при перетаскивании
        ['dragenter', 'dragover'].forEach(eventName => {
            dropArea.addEventListener(eventName, highlight, false)
        });

        ['dragleave', 'drop'].forEach(eventName => {
            dropArea.addEventListener(eventName, unhighlight, false)
        });

        function highlight(e) {
            dropArea.classList.add('dragover');
        }

        function unhighlight(e) {
            dropArea.classList.remove('dragover');
        }

        // Обрабатываем перетаскивание файлов
        dropArea.addEventListener('drop', handleDrop, false);

        function handleDrop(e) {
            let dt = e.dataTransfer;
            let files = dt.files;

            handleFiles(files);
        }

        function handleFiles(files) {
            // Здесь можно обрабатывать полученные файлы
            // Например, отправлять на сервер
            console.log(files);
            fileInput.files = files; // Обновляем input[type="file"]
            // Вызываем событие change, чтобы обновить предпросмотр
            const event = new Event('change', {bubbles: true});
            fileInput.dispatchEvent(event);
        }

        // Открываем диалоговое окно выбора файлов при клике на drop area
        dropArea.addEventListener('click', () => {
            fileInput.click();
        });

        fileInput.addEventListener('change', function (event) {
            const file = event.target.files[0];

            if (file) {
                const reader = new FileReader();

                reader.onload = function (e) {
                    const csvData = e.target.result;
                    const table = csvToTable(csvData);
                    csvPreview.innerHTML = table;
                    modalCsvPreview.innerHTML = table;
                    csvPreview.classList.add('blurred');
                    showPreviewButton.disabled = false;
                }

                reader.readAsText(file);
            } else {
                csvPreview.innerHTML = '<p>Предварительный просмотр CSV-файла</p>';
                modalCsvPreview.innerHTML = '';
                csvPreview.classList.remove('blurred');
                showPreviewButton.disabled = true;
            }
        });

        showPreviewButton.addEventListener('click', function (event) {
            const button = event.target;
            const spinner = button.querySelector('.spinner-border');
            spinner.classList.remove('d-none');
            button.disabled = true;

            // Simulate a delay - replace with actual preview loading logic
            setTimeout(() => {
                csvPreview.classList.remove('blurred');
                spinner.classList.add('d-none');
                button.disabled = false;
            }, 2000);
        });

        uploadForm.addEventListener('submit', function () {
            spinner.classList.remove('d-none');
            uploadButton.disabled = true; // Disable the button to prevent multiple submissions
        });

        // JavaScript для модального окна редактирования
        // Check if editProductModal element exists before adding event listener
        if (editProductModal) {
            editProductModal.addEventListener('show.bs.modal', function (event) {
                const button = event.relatedTarget; // Button that triggered the modal
                const productId = button.dataset.productId;
                const productName = button.dataset.productName;
                const productDescription = button.dataset.productDescription;
                const productQualityScore = button.dataset.productQuality_score;
                const productRecommendations = button.dataset.productRecommendations;

                // Update the modal's content.
                const modalTitle = editProductModal.querySelector('.modal-title');
                const modalProductName = editProductModal.querySelector('#editProductName');
                const modalProductDescription = editProductModal.querySelector('#editProductDescription');
                const modalProductQualityScore = editProductModal.querySelector('#editProductQualityScore');
                const modalProductRecommendations = editProductModal.querySelector('#editProductRecommendations');
                const editProductForm = editProductModal.querySelector('#editProductForm');

                modalTitle.textContent = 'Редактировать продукт: ' + productName;
                modalProductName.value = productName;
                modalProductDescription.value = productDescription;
                modalProductQualityScore.value = productQualityScore;
                modalProductRecommendations.value = productRecommendations;

                // Set the form's action URL
                editProductForm.action = '/products/' + productId; // Adjust the URL to match your route
            });
        }

        // Add spinner functionality to the "Сохранить изменения" button
        const saveChangesButton = document.getElementById('saveChangesButton');
        if (saveChangesButton) {
            uploadForm.addEventListener('submit', function (event) {
                const button = event.target.querySelector('#saveChangesButton');
                const spinner = button.querySelector('.spinner-border');

                spinner.classList.remove('d-none');
                button.disabled = true; // Disable the button to prevent multiple submissions
            });
        }

        function csvToTable(csvData) {
            const rows = csvData.split('\n');
            let table = '<table class="table table-bordered">';

            for (let i = 0; i < rows.length; i++) {
                const cells = rows[i].split(',');
                table += '<tr>';
                for (let j = 0; j < cells.length; j++) {
                    table += '<td>' + cells[j] + '</td>';
                }
                table += '</tr>';
            }

            table += '</table>';
            return table;
        }
    });
</script>
</body>

</html>

