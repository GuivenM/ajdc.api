<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirmation de message</title>
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
            border-bottom: 3px solid #007bff;
        }
        .logo {
            max-width: 150px;
            margin-bottom: 10px;
        }
        .content {
            padding: 30px 20px;
            background-color: #ffffff;
        }
        .message-box {
            background-color: #f8f9fa;
            border-left: 4px solid #007bff;
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
        .btn {
            display: inline-block;
            padding: 10px 20px;
            background-color: #007bff;
            color: #ffffff;
            text-decoration: none;
            border-radius: 5px;
            margin-top: 15px;
        }
        .btn:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Association des Jeunes de la Diaspora Congolaise au Bénin</h1>
        <p style="color: #007bff; font-style: italic;">Solidarité - Réflexion - Action</p>
    </div>

    <div class="content">
        <h2>Bonjour {{ $message->prenom }} {{ $message->nom }},</h2>
        
        <p>Nous vous remercions d'avoir contacté l'AJDCB. Votre message a bien été reçu et sera traité dans les plus brefs délais.</p>

        <div class="message-box">
            <h3>Récapitulatif de votre message :</h3>
            <p><strong>Objet :</strong> 
                @switch($message->objet)
                    @case('question') Question @break
                    @case('partenariat') Demande de partenariat @break
                    @case('adhesion') Demande d'adhésion @break
                    @case('urgence') Urgence communautaire @break
                    @default Autre
                @endswitch
            </p>
            <p><strong>Message :</strong></p>
            <p>{{ $message->message }}</p>
        </div>

        <p><strong>Notre équipe vous répondra dans les 48 heures maximum.</strong></p>

        <p>En attendant, n'hésitez pas à :</p>
        <ul>
            <li>Visiter notre site web pour plus d'informations</li>
            <li>Nous suivre sur nos réseaux sociaux</li>
            <li>Consulter notre guide du Congolais au Bénin</li>
        </ul>

        <a href="https://ajdcb.org" class="btn">Visiter notre site</a>

        <p>Solidaires,</p>
        <p><strong>L'équipe AJDCB</strong></p>
    </div>

    <div class="footer">
        <p>Association des Jeunes de la Diaspora Congolaise au Bénin (AJDCB)</p>
        <p>Cotonou - République du Bénin</p>
        <p>Email: contact@ajdcb.org | Tél: +229 01 66 24 62 68</p>
        <p>&copy; {{ date('Y') }} AJDCB. Tous droits réservés.</p>
    </div>
</body>
</html>