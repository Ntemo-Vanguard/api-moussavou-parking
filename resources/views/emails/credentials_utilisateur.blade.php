<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vos accès – MOUSSAVOU-PARKING</title>
    <style>
        html, body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif,
                         'Apple Color Emoji', 'Segoe UI Emoji', 'Segoe UI Symbol';
            font-size: 16px;
            /* ✅ orange + transparent */
            background-color: rgba(255, 165, 0, 0.18);
            margin: 0;
            padding: 0;
            color: #333333;
        }

        .email-container {
            max-width: 600px;
            margin: 30px auto;
            background-color: #ffffff;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            padding: 20px;
        }

        .email-header {
            text-align: center;
            padding: 20px;
            /* ✅ un peu plus visible que le body */
            background-color: rgba(255, 165, 0, 0.35);
        }

        .email-header img {
            max-width: 120px;
            height: auto;
        }

        .email-content {
            padding: 20px;
            line-height: 1.6;
        }

        .email-content h1 {
            font-size: 24px;
            color: #2d3748;
            margin-bottom: 20px;
        }

        .email-content p {
            margin-bottom: 16px;
            font-size: 16px;
        }

        .credentials-box {
            background: #f8fafc;
            border-left: 5px solid orange;
            padding: 15px;
            margin: 20px 0;
            border-radius: 6px;
        }

        .credentials-box p {
            margin: 6px 0;
            font-size: 15px;
        }

        .highlight {
            color: orange;
            font-weight: bold;
        }

        .important {
            color: red;
            font-weight: bold;
        }

        .btn {
            display: inline-block;
            margin: 25px auto 10px;
            padding: 12px 22px;
            background-color: #2d3748;
            color: #ffffff !important;
            text-decoration: none;
            border-radius: 5px;
            font-size: 15px;
            transition: 0.3s;
        }

        .btn:hover {
            background-color: orange;
        }

        .email-footer {
            text-align: center;
            font-size: 14px;
            padding: 12px 20px;
            /* ✅ transparent aussi */
            background-color: rgba(255, 165, 0, 0.35);
        }

        .email-footer a {
            color: #333333;
            text-decoration: none;
        }
    </style>
</head>
<body>
    <div class="email-container">

        <!-- Header -->
        <div class="email-header">
            <img src="https://i.imgur.com/JA3As2v.png" alt="Logo" style="filter: brightness(1); opacity:0.5;">
        </div>

        <!-- Content -->
        <div class="email-content">
            <h1>Bonjour {{ $utilisateur->nom }},</h1>

            <p>
                Un compte <span class="highlight">{{ ucfirst($utilisateur->role) }}</span>
                vient d’être créé pour vous sur la plateforme
                <strong>MOUSSAVOU-PARKING</strong>.
            </p>

            <div class="credentials-box">
                <p><strong>Email :</strong> {{ $utilisateur->email }}</p>
                <p><strong>Mot de passe :</strong> {{ $motDePasse }}</p>
                <p><strong>Rôle :</strong> {{ ucfirst($utilisateur->role) }}</p>
            </div>

            <div style="text-align:center;">
                <a class="btn" href="{{ config('app.front_url', 'http://localhost:4200') }}/login">
                    Accéder à la plateforme
                </a>
            </div>
        </div>

        <!-- Footer -->
        <div class="email-footer">
            &copy; {{ date('Y') }}
            <a href="#">MOUSSAVOU-PARKING — MFK Productions</a>. All rights reserved
        </div>

    </div>
</body>
</html>