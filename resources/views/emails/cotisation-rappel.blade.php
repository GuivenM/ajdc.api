<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rappel de cotisation AJDCB</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background-color: #f8f9fa; padding: 20px; text-align: center; border-bottom: 3px solid #3f794b; }
        .content { padding: 30px 20px; background-color: #ffffff; }
        .alerte { background-color: #fff7ed; border: 1px solid #fdba74; color: #9a3412; padding: 14px 18px; border-radius: 8px; margin: 20px 0; }
        .bouton { display: inline-block; background-color: #3f794b; color: #ffffff; text-decoration: none; padding: 12px 28px; border-radius: 5px; margin: 20px 0; }
        .footer { text-align: center; padding: 20px; font-size: 12px; color: #6c757d; background-color: #f8f9fa; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Association des Jeunes de la Diaspora Congolaise au Bénin</h1>
        <p style="color: #3f794b; font-style: italic;">Solidarité - Réflexion - Action</p>
    </div>

    <div class="content">
        <h2>Bonjour {{ $membre->prenom }} {{ $membre->nom }},</h2>

        @if($avertissementFinal)
            <div class="alerte">
                <strong>Dernier avertissement.</strong> Votre cotisation est impayée depuis {{ $moisRetard }} mois consécutifs.
                Conformément à l'Article 3 du règlement intérieur, un 3<sup>ème</sup> mois consécutif de non-paiement
                entraîne une <strong>radiation automatique</strong> de l'association.
            </div>
            <p>Pour régulariser votre situation, merci de vous rapprocher du Trésorier ou de payer votre cotisation dès que possible.</p>
        @else
            <p>
                Nous n'avons pas encore reçu votre cotisation mensuelle de {{ $moisRetard }} mois. Un petit rappel amical
                avant que ça ne s'accumule !
            </p>
            <p>La cotisation mensuelle est fixée à 1.000 FCFA (Article 2 du règlement intérieur), à régler auprès du Trésorier.</p>
        @endif

        <p>Solidaires,</p>
        <p><strong>Le Bureau Exécutif de l'AJDCB</strong></p>
    </div>

    <div class="footer">
        <p>Association des Jeunes de la Diaspora Congolaise au Bénin (AJDCB)</p>
        <p>Cotonou - République du Bénin</p>
        <p>Email: contact@ajdcb.org</p>
    </div>
</body>
</html>
