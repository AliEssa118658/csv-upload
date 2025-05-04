<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CSV Upload</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <script src="https://cdn.jsdelivr.net/npm/pusher-js@7.0.3/dist/web/pusher.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/laravel-echo@1.10.0/dist/echo.iife.js"></script>
    <style>
        /* Reset some basic styles */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        /* General body styling */
        body {
            font-family: 'Roboto', sans-serif;
            background-color: #f4f6f9;
            color: #333;
            padding: 20px;
            max-width: 1200px;
            margin: 0 auto;
        }

        h2 {
            color: #2c3e50;
            font-size: 28px;
            margin-bottom: 20px;
        }

        h3 {
            font-size: 22px;
            color: #34495e;
            margin-bottom: 15px;
        }

        /* Form styling */
        form {
            display: flex;
            flex-direction: column;
            gap: 10px;
            margin-bottom: 30px;
        }

        input[type="file"] {
            padding: 10px;
            font-size: 16px;
            border: 1px solid #ddd;
            border-radius: 4px;
            background-color: #fff;
        }

        button {
            padding: 12px 20px;
            background-color: #3498db;
            color: #fff;
            border: none;
            border-radius: 4px;
            font-size: 16px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        button:hover {
            background-color: #2980b9;
        }

        /* Table styling */
        table {
            width: 100%;
            border-collapse: collapse;
            background-color: #fff;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            margin-top: 20px;
        }

        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }

        th {
            background-color: #f4f6f9;
            color: #34495e;
        }

        td.status {
            color: #95a5a6;
        }

        /* Responsive table adjustments */
        @media (max-width: 768px) {
            table, th, td {
                font-size: 14px;
            }

            button {
                font-size: 14px;
                padding: 10px 16px;
            }
        }
    </style>
</head>
<body>
    <h2>Upload CSV File</h2>
    <form action="/upload" method="POST" enctype="multipart/form-data">
        @csrf
        <input type="file" name="file" required>
        <button type="submit">Upload</button>
    </form>

    <h3>Recent Uploads</h3>
    <table>
        <thead>
            <tr>
                <th>Time</th>
                <th>File Name</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody id="uploads-table">
            <!-- Populated by JS -->
        </tbody>
    </table>

    <script>
        // Fetch and update the upload statuses every 5 seconds
        async function fetchUploads() {
            const res = await fetch('/status');
            const data = await res.json();
            const tbody = document.getElementById('uploads-table');
            tbody.innerHTML = '';

            data.forEach(upload => {
                const row = `
                    <tr>
                        <td>${new Date(upload.created_at).toLocaleString()}</td>
                        <td>${upload.file_name}</td>
                        <td class="status" id="upload-status-${upload.id}">${upload.status}</td>
                    </tr>
                `;
                tbody.innerHTML += row;
            });
        }

        // Initial fetch and set interval to refresh uploads every 5 seconds
        setInterval(fetchUploads, 5000);
        fetchUploads();

        // Laravel Echo for real-time upload status updates
        var echo = new Echo({
            broadcaster: 'pusher',
            key: '{{ env("PUSHER_APP_KEY") }}',
            cluster: '{{ env("PUSHER_APP_CLUSTER") }}',
            forceTLS: true
        });

        // Listen for 'UploadStatusUpdated' event
        echo.channel('upload-status-channel')
            .listen('UploadStatusUpdated', (event) => {
                console.log(event);
                // Update the status in the table for the corresponding upload ID
                const statusElement = document.querySelector(`#upload-status-${event.uploadId}`);
                if (statusElement) {
                    statusElement.innerText = event.status;
                }
            });
    </script>
</body>
</html>
