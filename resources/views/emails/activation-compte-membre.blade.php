<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Activation de votre espace membre</title>
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
        .bouton {
            display: inline-block;
            background-color: #17a2b8;
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
        <p style="color: #17a2b8; font-style: italic;">Solidarité - Réflexion - Action</p>
    </div>

    <div class="content">
        <h2>Bonjour {{ $membre->prenom }} {{ $membre->nom }},</h2>

        <p><strong>Félicitations, votre adhésion à l'AJDCB a été approuvée !</strong></p>

        <p>Votre espace membre vous attend. Il vous permettra de suivre vos cotisations, de vous inscrire aux événements et de rester informé de la vie de l'association.</p>

        <p>Pour l'activer, définissez votre mot de passe en cliquant sur le bouton ci-dessous :</p>

        <p style="text-align: center;">
            <a href="{{ $lienActivation }}" class="bouton">Activer mon espace membre</a>
        </p>

        <p>Ce lien est valable 7 jours. Si vous n'êtes pas à l'origine de cette demande, vous pouvez ignorer cet email.</p>

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
