<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Le 11 de chaque mois à 8h (lendemain de la date limite de paiement,
// Article 2 du règlement intérieur) : rappels, avertissements, et radiation
// automatique après 3 mois consécutifs d'impayé (Article 3).
// Nécessite que le cron du serveur appelle `php artisan schedule:run`
// chaque minute (crontab : * * * * * cd /chemin/du/projet && php artisan schedule:run >> /dev/null 2>&1)
Schedule::command('cotisations:verifier-retards')
    ->monthlyOn(11, '08:00')
    ->onOneServer();
