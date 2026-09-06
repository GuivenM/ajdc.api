<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Radiation automatique AJDCB</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background-color: #f8f9fa; padding: 20px; text-align: center; border-bottom: 3px solid #3f794b; }
        .content { padding: 30px 20px; background-color: #ffffff; }
        .alerte { background-color: #fef2f2; border: 1px solid #fca5a5; color: #991b1b; padding: 14px 18px; border-radius: 8px; margin: 20px 0; }
        .footer { text-align: center; padding: 20px; font-size: 12px; color: #6c757d; background-color: #f8f9fa; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Association des Jeunes de la Diaspora Congolaise au Bénin</h1>
        <p style="color: #3f794b; font-style: italic;">Solidarité - Réflexion - Action</p>
    </div>

    <div class="content">
        @if($pourBureau)
            <h2>Radiation automatique</h2>
            <div class="alerte">
                <strong>{{ $membre->prenom }} {{ $membre->nom }}</strong> vient d'être automatiquement radié(e) après
                {{ $moisRetard }} mois consécutifs de cotisation impayée (Règlement intérieur, Article 3).
            </div>
            <p>Le statut de ce membre a été basculé sur « inactif ». Si c'est une erreur (paiement reçu par un autre
            canal, par exemple), vous pouvez le réactiver manuellement depuis l'espace admin, page Membres.</p>
        @else
            <h2>Bonjour {{ $membre->prenom }} {{ $membre->nom }},</h2>
            <div class="alerte">
                Conformément à l'Article 3 du règlement intérieur, votre qualité de membre de l'AJDCB a été retirée
                suite à {{ $moisRetard }} mois consécutifs de cotisation impayée.
            </div>
            <p>Si vous souhaitez régulariser votre situation et revenir parmi nous, rapprochez-vous du Bureau Exécutif.</p>
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
