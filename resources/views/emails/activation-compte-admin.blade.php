<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Votre accès administrateur AJDCB</title>
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
            border-bottom: 3px solid #3f794b;
        }
        .content {
            padding: 30px 20px;
            background-color: #ffffff;
        }
        .role-badge {
            display: inline-block;
            background-color: #f4f0e6;
            color: #705924;
            font-weight: bold;
            padding: 4px 12px;
            border-radius: 999px;
            font-size: 13px;
        }
        .bouton {
            display: inline-block;
            background-color: #3f794b;
            color: #ffffff;
            text-decoration: none;
            padding: 12px 28px;
            border-radius: 5px;
            margin: 20px 0;
        }
        .footer {
            text-align: center;
            padding: 20px;
            font-size: 12px;
            color: #6c757d;
            background-color: #f8f9fa;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Association des Jeunes de la Diaspora Congolaise au Bénin</h1>
        <p style="color: #3f794b; font-style: italic;">Solidarité - Réflexion - Action</p>
    </div>

    <div class="content">
        <h2>Bonjour {{ $user->prenom }} {{ $user->nom }},</h2>

        <p>
            Un accès à l'espace d'administration du site de l'AJDCB vient de vous être créé, avec le rôle
            <span class="role-badge">{{ $user->role_label }}</span>.
        </p>

        <p>Pour l'activer, définissez votre mot de passe en cliquant sur le bouton ci-dessous :</p>

        <p style="text-align: center;">
            <a href="{{ $lienActivation }}" class="bouton">Activer mon accès administrateur</a>
        </p>

        <p>Ce lien est valable 7 jours. Si vous n'êtes pas à l'origine de cette demande, contactez immédiatement le Bureau Exécutif.</p>

        <p>Solidaires,</p>
        <p><strong>Le Bureau Exécutif de l'AJDCB</strong></p>
    </div>

    <div class="footer">
        <p>Association des Jeunes de la Diaspora Congolaise au Bénin (AJDCB)</p>
        <p>Cotonou - République du Bénin</p>
        <p>Email: contact@ajdcb.org | Tél: +229 01 66 24 62 68</p>
    </div>
</body>
</html>
