<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Carte;
use App\Models\Transaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PaytechController extends Controller
{
    /**
     * 1) Initier un paiement de recharge
     * Appelé par Angular -> appelle PayTech -> renvoie redirect_url
     */
    public function initPayment(Request $request)
    {
        $user = $request->user(); // auth:api (JWT)

        $data = $request->validate([
            'carte_id' => 'required|integer|exists:cartes,id',
            'montant'  => 'required|numeric|min:0.01',
        ]);

        // Vérifier que la carte appartient bien à l'utilisateur connecté
        $carte = Carte::where('id', $data['carte_id'])
            ->where('utilisateur_id', $user->id)
            ->where('statut', 'active')
            ->firstOrFail();

        // Payload PayTech (d'après la doc /payment/request-payment) :contentReference[oaicite:4]{index=4}
        $payload = [
            'item_name'    => 'Recharge carte parking',
            'item_price'   => $data['montant'],
            'currency'     => 'XOF',
            'ref_command'  => 'RECH_' . uniqid() . '_C' . $carte->id,
            'command_name' => 'Recharge carte RFID parking',
            'env'          => config('services.paytech.env', 'test'),
            'ipn_url'      => config('app.url') . '/api/paytech/ipn',
            'success_url'  => config(env('FRONT_URL')) . '/payment-success',
            'cancel_url'   => config(env('FRONT_URL')) . '/payment-cancel',
            // plusieurs méthodes possibles sur la page PayTech (optionnel)
            'target_payment' => 'Orange Money, Wave, Free Money',
            // Données que tu veux récupérer dans l’IPN
            'custom_field' => json_encode([
                'user_id'   => $user->id,
                'carte_id'  => $carte->id,
                'montant'   => $data['montant'],
            ]),
        ];

        // Headers PayTech (API_KEY + API_SECRET) :contentReference[oaicite:5]{index=5}
        $headers = [
            'Accept'      => 'application/json',
            'Content-Type'=> 'application/json',
            'API_KEY'     => config('services.paytech.key'),
            'API_SECRET'  => config('services.paytech.secret'),
        ];

        $url = rtrim(config('services.paytech.base_url'), '/') . '/payment/request-payment';

        $response = Http::withHeaders($headers)->post($url, $payload);

        if (! $response->successful()) {
            Log::error('PayTech HTTP error', ['body' => $response->body()]);
            return response()->json(['message' => 'Impossible d’initier le paiement.'], 500);
        }

        $json = $response->json();

        if (($json['success'] ?? 0) != 1) {
            Log::warning('PayTech business error', $json);
            return response()->json([
                'message' => $json['message'] ?? 'Erreur PayTech.',
            ], 400);
        }

        // D’après la doc, la réponse contient success, token, redirect_url :contentReference[oaicite:6]{index=6}
        return response()->json([
            'redirect_url' => $json['redirect_url'] ?? $json['redirectUrl'] ?? null,
            'token'        => $json['token'] ?? null,
        ]);
    }

    /**
     * 2) IPN → PayTech notifie ton backend (paiement réussi / annulé)
     * Configurée via ipn_url dans la demande de paiement. :contentReference[oaicite:7]{index=7}
     */
    public function ipn(Request $request)
    {
        $payload = $request->all();
        Log::info('PayTech IPN received', $payload);

        // Vérification SHA256 des clés (doc: api_key_sha256, api_secret_sha256) :contentReference[oaicite:8]{index=8}
        $expectedKeyHash    = hash('sha256', config('services.paytech.key'));
        $expectedSecretHash = hash('sha256', config('services.paytech.secret'));

        if (
            !isset($payload['api_key_sha256'], $payload['api_secret_sha256']) ||
            $payload['api_key_sha256']    !== $expectedKeyHash ||
            $payload['api_secret_sha256'] !== $expectedSecretHash
        ) {
            Log::warning('PayTech IPN invalid signature');
            return response()->json(['message' => 'invalid signature'], 403);
        }

        $event = $payload['type_event'] ?? null;  // sale_complete / sale_canceled :contentReference[oaicite:9]{index=9}

        // Si le paiement est annulé, on ignore comme tu le veux
        if ($event === 'sale_canceled') {
            return response()->json(['message' => 'canceled_ignored'], 200);
        }

        if ($event !== 'sale_complete') {
            // autres types d’événements : on peut juste loguer
            Log::info('PayTech IPN other event', ['type_event' => $event]);
            return response()->json(['message' => 'ignored'], 200);
        }

        // Récupérer les données custom_field (encodées en Base64 dans l’IPN) :contentReference[oaicite:10]{index=10}
        $custom   = [];
        $rawCustom = $payload['custom_field'] ?? null;

        if ($rawCustom) {
            $decoded = base64_decode($rawCustom, true);
            if ($decoded !== false) {
                $custom = json_decode($decoded, true) ?: [];
            }
        }

        $carteId = $custom['carte_id'] ?? null;
        $userId  = $custom['user_id'] ?? null;

        if (!$carteId || !$userId) {
            Log::warning('PayTech IPN missing custom data', $custom);
            return response()->json(['message' => 'missing custom_field data'], 400);
        }

        // Montant confirmé par PayTech : final_item_price_xof > item_price_xof > item_price :contentReference[oaicite:11]{index=11}
        $amount = $payload['final_item_price_xof']
            ?? $payload['item_price_xof']
            ?? $payload['item_price']
            ?? null;

        if (!$amount) {
            Log::warning('PayTech IPN missing amount');
            return response()->json(['message' => 'missing amount'], 400);
        }

        $paymentMethod = $payload['payment_method'] ?? null; // ex: "Orange Money", "Wave" :contentReference[oaicite:12]{index=12}

        DB::transaction(function () use ($carteId, $userId, $amount, $paymentMethod, $payload) {
            $carte = Carte::lockForUpdate()->findOrFail($carteId);

            // Créditer la carte
            $carte->solde = $carte->solde + $amount;
            $carte->save();

            // Mapper la méthode PayTech vers ta colonne enum (option simple)
            $moyen = null;
            if ($paymentMethod) {
                $m = strtolower($paymentMethod);
                if (str_contains($m, 'orange')) $moyen = 'orange_money';
                elseif (str_contains($m, 'wave')) $moyen = 'wave';
                elseif (str_contains($m, 'free')) $moyen = 'free_money';
            }

            // Créer la transaction (uniquement si paiement réussi)
            Transaction::create([
                'carte_id'       => $carteId,
                'utilisateur_id' => $userId,
                'montant'        => $amount,
                'type'           => 'recharge',
                'moyen'          => $moyen,       // peut rester null si non mappable
                'statut'         => 'valide',
            ]);
        });

        return response()->json(['message' => 'ok'], 200);
    }
}