<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Recharge effectuée – MOUSSAVOU-PARKING</title>
    <style>
        html, body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            background-color: rgba(255, 165, 0, 0.18);
            margin: 0;
            padding: 0;
            color: #333;
        }
        .email-container {
            max-width: 600px;
            margin: 30px auto;
            background: #fff;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        .email-header {
            text-align: center;
            background-color: rgba(255, 165, 0, 0.35);
            padding: 20px;
        }
        .email-header img {
            max-width: 120px;
            opacity: 0.5;
        }
        .email-content {
            padding: 20px;
        }
        .credentials-box {
            background: #f8fafc;
            border-left: 5px solid orange;
            padding: 15px;
            margin: 20px 0;
            border-radius: 6px;
        }
        .highlight {
            color: orange;
            font-weight: bold;
        }
        .btn {
            display: inline-block;
            background: #2d3748;
            color: #fff !important;
            padding: 12px 22px;
            border-radius: 5px;
            text-decoration: none;
        }
        .btn:hover {
            background: orange;
        }
        .email-footer {
            text-align: center;
            font-size: 14px;
            background-color: rgba(255, 165, 0, 0.35);
            padding: 12px;
        }
    </style>
</head>
<body>

<div class="email-container">

    <div class="email-header">
        <img src="https://i.imgur.com/JA3As2v.png" alt="Logo">
    </div>

    <div class="email-content">
        <h1>Bonjour {{ $carte->utilisateur->nom }},</h1>

        <p>
            Une recharge vient d’être effectuée sur votre carte RFID
            <strong class="highlight">{{ $carte->code_rfid }}</strong>.
        </p>

        <div class="credentials-box">
            <p><strong>Montant rechargé :</strong> {{ number_format($montant, 0, ',', ' ') }} XOF</p>
            <p><strong>Nouveau solde :</strong> {{ number_format($carte->solde, 0, ',', ' ') }} XOF</p>
            <p><strong>Moyen de paiement :</strong> {{ ucfirst(str_replace('_', ' ', $moyen)) }}</p>
        </div>

        <div style="text-align:center;">
            <a class="btn" href="{{ config('app.front_url', 'http://localhost:4200') }}/login">
                Accéder à mon compte
            </a>
        </div>
    </div>

    <div class="email-footer">
        &copy; {{ date('Y') }} MOUSSAVOU-PARKING — MFK Productions
    </div>

</div>

</body>
</html>