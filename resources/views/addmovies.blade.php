<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Movie</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        /* Loader styles */
        .loader {
            display: none;
            /* Hidden by default */
            border: 8px solid #f3f3f3;
            /* Light grey */
            border-top: 8px solid #3498db;
            /* Blue */
            border-radius: 50%;
            width: 50px;
            height: 50px;
            animation: spin 1s linear infinite;
            position: absolute;
            left: 50%;
            top: 50%;
            transform: translate(-50%, -50%);
            z-index: 10;
            /* Ensure it's above other elements */
        }

        @keyframes spin {
            0% {
                transform: rotate(0deg);
            }

            100% {
                transform: rotate(360deg);
            }
        }

        .overlay {
            display: none;
            /* Hidden by default */
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: rgba(0, 0, 0, 0.5);
            /* Semi-transparent background */
            z-index: 5;
            /* Below the loader */
        }

        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }

        .container {
            max-width: 600px;
            margin: 50px auto;
            padding: 20px;
            background: white;
            border-radius: 5px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        h1 {
            text-align: center;
            color: #333;
        }

        .form-group {
            margin-bottom: 15px;
        }

        label {
            display: block;
            margin-bottom: 5px;
            color: #333;
        }

        input[type="text"],
        input[type="url"],
        input[type="number"],
        select,
        textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }

        textarea {
            height: 100px;
        }

        button {
            width: 100%;
            padding: 10px;
            background-color: #28a745;
            color: white;
            border: none;
            border-radius: 4px;
            font-size: 16px;
            cursor: pointer;
        }

        button:hover {
            background-color: #218838;
        }
    </style>
</head>

<body>
    <div class="container">
        <h1>Add a Movie</h1>
        <form id="movieForm">
            <div class="form-group">
                <label for="title">Title:</label>
                <input type="text" id="title" name="title" required>
            </div>

            <div class="form-group">
                <label for="poster">Poster URL:</label>
                <input type="url" id="poster" name="poster" required>
            </div>

            <div class="form-group">
                <label for="movie_url">Movie URL:</label>
                <input type="url" id="movie_url" name="movie_url" required>
            </div>

            <button type="submit">Add Movie</button>
        </form>

        <div class="overlay" id="overlay">
            <div class="loader"></div>
        </div>
    </div>

    <script>
        document.getElementById('movieForm').addEventListener('submit', function(e) {
            e.preventDefault();

            // Show loader
            document.getElementById('overlay').style.display = 'block';

            const formData = {
                title: this.title.value,
                description: "A random description for the movie.", // Random value
                director: "John Doe", // Random value
                producer: "Jane Smith", // Random value
                release_year: 2024, // Random value
                rating: "PG", // Random value
                poster: this.poster.value,
                trailer_url: "https://example.com/random-trailer.mp4", // Random value
                movie_url: this.movie_url.value,
                categories: ["Action"], // Random value
                tags: ["Adventure"] // Random value
            };

            fetch('https://iptv.sunilflutter.in/api/movies', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(formData)
                })
                .then(response => response.json())
                .then(data => {
                    console.log('Success:', data);
                    alert('Movie added successfully!');
                    this.reset(); // Reset the form after submission
                })
                .catch((error) => {
                    console.error('Error:', error);
                    alert('Failed to add movie.');
                })
                .finally(() => {
                    // Hide loader
                    document.getElementById('overlay').style.display = 'none';
                });
        });
    </script>
</body>

</html>