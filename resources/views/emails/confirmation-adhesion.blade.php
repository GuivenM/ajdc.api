<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirmation demande d'adhésion</title>
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
            border-bottom: 3px solid #17a2b8;
        }
        .content {
            padding: 30px 20px;
            background-color: #ffffff;
        }
        .info-box {
            background-color: #f8f9fa;
            border-left: 4px solid #17a2b8;
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
        .etape {
            margin: 10px 0;
            padding: 10px;
            background-color: #e9ecef;
            border-radius: 5px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Association des Jeunes Élites Congolaises au Bénin</h1>
        <p style="color: #17a2b8; font-style: italic;">Solidarité - Réflexion - Action</p>
    </div>

    <div class="content">
        <h2>Bonjour {{ $adhesion->prenom }} {{ $adhesion->nom }},</h2>
        
        <p><strong>Votre demande d'adhésion à l'AJECB a bien été reçue !</strong></p>

        <div class="info-box">
            <h3>Récapitulatif de votre demande :</h3>
            <p><strong>Nom complet :</strong> {{ $adhesion->prenom }} {{ $adhesion->nom }}</p>
            <p><strong>Email :</strong> {{ $adhesion->email }}</p>
            <p><strong>Téléphone :</strong> {{ $adhesion->telephone }}</p>
            <p><strong>Profession :</strong> {{ $adhesion->profession }}</p>
            <p><strong>Ville :</strong> {{ $adhesion->ville }}</p>
        </div>

        <h3>Prochaines étapes :</h3>
        
        <div class="etape">
            <strong>Étape 1 :</strong> Examen de votre dossier par le Bureau Exécutif
        </div>
        
        <div class="etape">
            <strong>Étape 2 :</strong> Vous recevrez une réponse sous 7 à 14 jours
        </div>
        
        <div class="etape">
            <strong>Étape 3 :</strong> Si votre candidature est retenue, vous serez invité à une réunion d'intégration
        </div>

        <p><strong>Numéro de suivi :</strong> AJECB-{{ $adhesion->id }}-{{ $adhesion->created_at->format('Ymd') }}</p>

        <p>Nous vous remercions pour votre intérêt et votre engagement envers la communauté congolaise au Bénin.</p>

        <p>Solidaires,</p>
        <p><strong>Le Bureau Exécutif de l'AJECB</strong></p>
    </div>

    <div class="footer">
        <p>Association des Jeunes Élites Congolaises au Bénin (AJECB)</p>
        <p>Cotonou - République du Bénin</p>
        <p>Email: contact@ajecb.org | Tél: +229 00 00 00 00</p>
    </div>
</body>
</html>