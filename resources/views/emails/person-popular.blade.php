<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            background-color: #ff6b6b;
            color: white;
            padding: 20px;
            text-align: center;
            border-radius: 5px 5px 0 0;
        }
        .content {
            background-color: #f9f9f9;
            padding: 30px;
            border: 1px solid #ddd;
            border-radius: 0 0 5px 5px;
        }
        .stats {
            background-color: white;
            padding: 15px;
            margin: 20px 0;
            border-left: 4px solid #ff6b6b;
        }
        .footer {
            text-align: center;
            margin-top: 20px;
            color: #777;
            font-size: 12px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🔥 Popular Person Arrived!</h1>
        </div>
        <div class="content">
            <p>Hello Mimin,</p>
            
            <p>A person on TinderKW has reached a significant milestone!</p>
            
            <div class="stats">
                <h3>Person Details:</h3>
                <ul>
                    <li><strong>Name:</strong> {{ $personName }}</li>
                    <li><strong>Age:</strong> {{ $personAge }}</li>
                    <li><strong>Person ID:</strong> #{{ $personId }}</li>
                    <li><strong>Total Likes:</strong> <span style="color: #ff6b6b; font-weight: bold;">{{ $likeCount }}</span></li>
                </ul>
            </div>
            
            <p>This person has received <strong>{{ $likeCount }} likes</strong> and has crossed the 50-like threshold!</p>
            
            <p>This might indicate high engagement and profile quality. Consider featuring this profile or reviewing for platform insights.</p>
            
            <p>Best regards,<br>
            TinderKW System</p>
        </div>
        <div class="footer">
            <p>This is an automated notification from TinderKW API</p>
        </div>
    </div>
</body>
</html>
