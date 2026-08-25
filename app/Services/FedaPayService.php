<?php

namespace App\Services;

use FedaPay\FedaPay;
use FedaPay\Transaction;
use FedaPay\Webhook;

class FedaPayService
{
    public function __construct()
    {
        FedaPay::setApiKey((string) config('services.fedapay.secret_key'));
        FedaPay::setEnvironment((string) config('services.fedapay.environment'));
    }

    /**
     * Crée une transaction FedaPay et génère le lien de paiement sécurisé
     * vers lequel rediriger le client.
     *
     * @param float $montant Montant en XOF (entier attendu par FedaPay)
     * @param string $description
     * @param array $customer ['firstname' => ..., 'lastname' => ..., 'email' => ..., 'phone_number' => ['number' => ..., 'country' => 'bj']]
     * @return array{transaction_id: int, checkout_url: string}
     */
    public function initierTransaction(float $montant, string $description, array $customer = []): array
    {
        $transaction = Transaction::create([
            'description' => $description,
            'amount' => (int) round($montant),
            'currency' => ['iso' => 'XOF'],
            'callback_url' => config('services.fedapay.callback_url'),
            'customer' => $customer,
        ]);

        $token = $transaction->generateToken();

        return [
            'transaction_id' => $transaction->id,
            'checkout_url' => $token->url,
        ];
    }

    /**
     * Récupère l'état à jour d'une transaction directement auprès de FedaPay
     * (utile pour re-vérifier un paiement indépendamment du webhook).
     */
    public function recupererTransaction(string $transactionId): Transaction
    {
        return Transaction::retrieve($transactionId);
    }

    /**
     * Vérifie la signature d'un webhook FedaPay et retourne l'évènement décodé.
     * Lève une exception si la signature est invalide.
     *
     * @param string $payload Corps brut de la requête
     * @param string $signature Valeur de l'en-tête X-FEDAPAY-SIGNATURE
     */
    public function verifierWebhook(string $payload, string $signature): object
    {
        return Webhook::constructEvent(
            $payload,
            $signature,
            (string) config('services.fedapay.webhook_secret')
        );
    }
}
