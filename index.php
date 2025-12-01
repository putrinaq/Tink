<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome to Tink</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            color: #333;
        }

        .container {
            background: white;
            padding: 3rem;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            text-align: center;
            max-width: 500px;
        }

        h1 {
            color: #667eea;
            margin-bottom: 1rem;
            font-size: 2.5rem;
        }

        p {
            color: #666;
            line-height: 1.6;
            margin-bottom: 1rem;
        }

        .info {
            background: #f8f9fa;
            padding: 1rem;
            border-radius: 10px;
            margin-top: 2rem;
            font-size: 0.9rem;
        }

        .status {
            color: #28a745;
            font-weight: bold;
        }
    </style>
</head>

<body>
    <div class="container">
        <h1>🎉 Welcome to Tink!</h1>
        <p>Your website is successfully deployed and running.</p>
        <p>This is your <strong>index.php</strong> file.</p>

        <div class="info">
            <p class="status">✅ PHP is working properly</p>
            <p><strong>PHP Version:</strong> <?php echo phpversion(); ?></p>
            <p><strong>Server:</strong> <?php echo $_SERVER['SERVER_SOFTWARE']; ?></p>
            <p><strong>Document Root:</strong> <?php echo $_SERVER['DOCUMENT_ROOT']; ?></p>
        </div>
    </div>
</body>

</html>