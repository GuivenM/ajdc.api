<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Réponse à votre message</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            background-color: #f8f9fa;
            padding: 20px;
            text-align: center;
            border-bottom: 3px solid #28a745;
        }
        .content {
            padding: 30px 20px;
            background-color: #ffffff;
        }
        .reponse-box {
            background-color: #f8f9fa;
            border-left: 4px solid #28a745;
            padding: 15px;
            margin: 20px 0;
        }
        .footer {
            text-align: center;
            padding: 20px;
            font-size: 12px;
            color: #6c757d;
            background-color: #f8f9fa;
        }
        .signature {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #dee2e6;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Association des Jeunes de la Diaspora Congolaise au Bénin</h1>
        <p style="color: #28a745; font-style: italic;">Solidarité - Réflexion - Action</p>
    </div>

    <div class="content">
        <!-- Utilisez messageData au lieu de message -->
        <h2>Bonjour {{ $messageData->prenom }} {{ $messageData->nom }},</h2>
        
        <p>Suite à votre message du {{ $messageData->created_at->format('d/m/Y') }}, voici notre réponse :</p>

        <div class="reponse-box">
            <h3>Réponse de l'AJDCB :</h3>
            <p>{{ $reponse }}</p>
        </div>

        <p>Nous restons à votre disposition pour toute information complémentaire.</p>

        <div class="signature">
            <p>Cordialement,</p>
            <p><strong>L'équipe AJDCB</strong></p>
        </div>
    </div>

    <div class="footer">
        <p>Association des Jeunes de la Diaspora Congolaise au Bénin (AJDCB)</p>
        <p>Cotonou - République du Bénin</p>
        <p>Email: contact@ajdcb.org | Tél: +229 01 66 24 62 68</p>
    </div>
</body>
</html>