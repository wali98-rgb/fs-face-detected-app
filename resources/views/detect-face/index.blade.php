<!DOCTYPE html>
<html>

<head>
    <title>Deteksi Wajah</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .face-box {
            border: 2px solid red;
            position: absolute;
            pointer-events: none;
        }

        .image-wrapper {
            position: relative;
            display: inline-block;
        }
    </style>
</head>

<body>
    <div class="container mt-5">
        <h2 class="mb-4">Deteksi Wajah dengan Google Vision</h2>

        @if (session('message'))
            <div class="alert alert-info">{{ session('message') }}</div>
        @endif

        <form action="{{ route('detect.face') }}" method="POST" enctype="multipart/form-data" id="detectForm">
            @csrf
            <div class="mb-3">
                <label for="image" class="form-label">Upload Gambar</label>
                <input type="file" name="image" class="form-control" required>
                @error('image')
                    <small class="text-danger">{{ $message }}</small>
                @enderror
            </div>
            <button type="submit" class="btn btn-primary">Deteksi Wajah</button>
        </form>

        <div id="result" class="mt-4"></div>
    </div>

    <script>
        const form = document.getElementById('detectForm');
        const resultDiv = document.getElementById('result');

        form.addEventListener('submit', async function(e) {
            e.preventDefault();
            const formData = new FormData(form);
            resultDiv.innerHTML = 'Memproses...';

            try {
                const response = await fetch(form.action, {
                    method: 'POST',
                    body: formData
                });

                const text = await response.text();

                try {
                    const data = JSON.parse(text);

                    if (response.ok) {
                        if (data.faces) {
                            resultDiv.innerHTML = `
                    <div class="alert alert-success">Wajah terdeteksi: ${data.faces.length}</div>
                    <pre>${JSON.stringify(data.faces, null, 2)}</pre>`;
                        } else if (data.message) {
                            resultDiv.innerHTML = `<div class="alert alert-warning">${data.message}</div>`;
                        }
                    } else {
                        resultDiv.innerHTML = `<div class="alert alert-danger">Error: ${data.message}</div>`;
                    }

                } catch (jsonError) {
                    // Bukan JSON valid, tampilkan HTML response dari Laravel
                    resultDiv.innerHTML =
                        `<div class="alert alert-danger">Server Error:<br><pre>${text}</pre></div>`;
                }

            } catch (error) {
                resultDiv.innerHTML =
                    `<div class="alert alert-danger">Terjadi kesalahan saat menghubungi server: ${error}</div>`;
            }
        });
    </script>
</body>

</html>
