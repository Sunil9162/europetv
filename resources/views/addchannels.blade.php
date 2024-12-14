<!DOCTYPE html>
<html>

<head>
    <title>Add Channel</title>
    <link rel="stylesheet" href="style.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f8f9fa;
            margin: 0;
            padding: 0;
        }

        .container {
            max-width: 600px;
            margin: 50px auto;
            padding: 20px;
            background-color: #ffffff;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        h2 {
            text-align: center;
            margin-bottom: 20px;
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-group label {
            display: block;
            margin-bottom: 5px;
        }

        .form-control {
            width: 100%;
            padding: 10px;
            box-sizing: border-box;
        }

        .btn-primary {
            display: block;
            width: 100%;
            padding: 10px;
            background-color: #007bff;
            color: #ffffff;
            border: none;
            cursor: pointer;
        }

        .btn-primary:hover {
            background-color: #0056b3;
        }
    </style>
</head>

<body>
    <div class="container">
        <h2>Add New Channel</h2>
        <form id="channelForm" method="POST">
            @csrf
            <div class="form-group">
                <label for="name">Channel Name:</label>
                <input type="text" class="form-control" id="name" name="name" required>
            </div>
            <div class="form-group">
                <label for="image">Channel Image:</label>
                <input type="text" class="form-control" id="image" name="image" required>
            </div>
            <div class="form-group">
                <label for="url">Channel URL:</label>
                <input type="url" class="form-control" id="url" name="url" required>
            </div>
            <button type="submit" class="btn btn-primary">Add Channel</button>
        </form>
    </div>

    <script>
        const channelForm = document.getElementById('channelForm');

        channelForm.addEventListener('submit', async (e) => {
            e.preventDefault();

            const formData = new FormData(channelForm);

            const response = await fetch('/channels', {
                method: 'POST',
                body: formData
            });

            if (response.status == 200) {
                alert('Channel added successfully');
                channelForm.reset();
            } else {
                response.json().then(data => {
                    alert(data.message);
                });
            }
        });
    </script>
</body>

</html>