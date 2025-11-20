<?php
http_response_code(404);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>404 - Page Not Found</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { 
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; 
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); 
            min-height: 100vh; 
            display: flex; 
            align-items: center; 
            justify-content: center; 
            padding: 20px; 
        }
        .container { 
            background: white; 
            border-radius: 16px; 
            box-shadow: 0 20px 60px rgba(0,0,0,0.3); 
            max-width: 500px; 
            width: 100%; 
            padding: 60px 40px; 
            text-align: center; 
        }
        .error-code { 
            font-size: 120px; 
            font-weight: 700; 
            color: #667eea; 
            line-height: 1; 
            margin-bottom: 20px; 
        }
        h1 { 
            font-size: 32px; 
            margin-bottom: 16px; 
            color: #1a202c; 
        }
        p { 
            color: #718096; 
            margin-bottom: 32px; 
            font-size: 16px; 
        }
        .btn { 
            display: inline-block; 
            background: #667eea; 
            color: white; 
            padding: 14px 32px; 
            border-radius: 8px; 
            text-decoration: none; 
            font-weight: 600; 
            transition: background 0.2s; 
        }
        .btn:hover { 
            background: #5568d3; 
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="error-code">404</div>
        <h1>Page Not Found</h1>
        <p>The page you're looking for doesn't exist or has been moved.</p>
        <a href="/" class="btn">Go to Homepage</a>
    </div>
</body>
</html>
