{{-- resources/views/emails/nouvelle-adhesion.blade.php --}}
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nouvelle demande d'adhésion</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #333;
            margin: 0;
            padding: 0;
            background-color: #f4f4f7;
        }
        .container {
            max-width: 600px;
            margin: 20px auto;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 20px;
            padding: 3px;
        }
        .content {
            background-color: white;
            border-radius: 18px;
            padding: 40px 30px;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
        }
        .logo {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 50%;
            margin: 0 auto 20px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .logo span {
            color: white;
            font-size: 40px;
            font-weight: bold;
        }
        h1 {
            color: #333;
            font-size: 28px;
            margin-bottom: 10px;
        }
        .badge {
            display: inline-block;
            background: #fef3c7;
            color: #d97706;
            padding: 8px 16px;
            border-radius: 50px;
            font-size: 14px;
            font-weight: bold;
            margin-bottom: 20px;
        }
        .info-card {
            background: #f8fafc;
            border-radius: 15px;
            padding: 25px;
            margin-bottom: 25px;
            border: 1px solid #e2e8f0;
        }
        .info-row {
            display: flex;
            margin-bottom: 15px;
            padding-bottom: 15px;
            border-bottom: 1px solid #e2e8f0;
        }
        .info-row:last-child {
            border-bottom: none;
            margin-bottom: 0;
            padding-bottom: 0;
        }
        .info-label {
            width: 120px;
            font-weight: 600;
            color: #64748b;
        }
        .info-value {
            flex: 1;
            color: #1e293b;
        }
        .button {
            display: inline-block;
            padding: 15px 30px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            text-decoration: none;
            border-radius: 10px;
            font-weight: bold;
            margin: 10px 5px;
            transition: transform 0.2s;
        }
        .button:hover {
            transform: translateY(-2px);
        }
        .button-secondary {
            background: white;
            color: #667eea;
            border: 2px solid #667eea;
        }
        .footer {
            text-align: center;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 2px solid #e2e8f0;
            color: #64748b;
        }
        .highlight {
            background: #fef3c7;
            padding: 2px 8px;
            border-radius: 5px;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="content">
            <div class="header">
                <div class="logo">
                    <span>AJ</span>
                </div>
                <h1>✨ Nouvelle demande d'adhésion</h1>
                <div class="badge">
                    ⏳ En attente de traitement
                </div>
            </div>

            <p style="font-size: 18px; margin-bottom: 25px; text-align: center;">
                Une nouvelle personne souhaite rejoindre l'association 
                <strong class="highlight">AJECB</strong>
            </p>

            <div class="info-card">
                <h3 style="margin-top: 0; margin-bottom: 20px; color: #333;">
                    📋 Informations du demandeur
                </h3>
                
                <div class="info-row">
                    <span class="info-label">Nom complet</span>
                    <span class="info-value"><strong>{{ $prenom }} {{ $nom }}</strong></span>
                </div>
                
                <div class="info-row">
                    <span class="info-label">Email</span>
                    <span class="info-value">{{ $email }}</span>
                </div>
                
                <div class="info-row">
                    <span class="info-label">Téléphone</span>
                    <span class="info-value">{{ $telephone }}</span>
                </div>
                
                <div class="info-row">
                    <span class="info-label">Ville</span>
                    <span class="info-value">{{ $ville }}</span>
                </div>
                
                <div class="info-row">
                    <span class="info-label">Date</span>
                    <span class="info-value">{{ now()->format('d/m/Y H:i') }}</span>
                </div>
            </div>

            <div style="text-align: center; margin: 30px 0;">
                <a href="{{ $dashboardUrl }}" class="button">
                    👁️ Voir la demande complète
                </a>
                
                <a href="{{ $listeUrl }}" class="button button-secondary">
                    📋 Gérer toutes les demandes
                </a>
            </div>

            <div style="background: #f0f9ff; border-radius: 10px; padding: 20px; margin: 20px 0;">
                <p style="margin: 0; color: #0369a1;">
                    <strong>ℹ️ Action requise :</strong><br>
                    Cette demande est en attente de traitement. Veuillez l'examiner et prendre une décision dans les plus brefs délais.
                </p>
            </div>

            <div class="footer">
                <p style="margin: 0;">
                    Cet email a été envoyé automatiquement suite à une nouvelle demande d'adhésion.<br>
                    © {{ date('Y') }} AJECB - Tous droits réservés
                </p>
            </div>
        </div>
    </div>
</body>
</html>