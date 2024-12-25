# API-MTN-BENIN
Des fonctions simples d'utilisation en PHP pour l'intégration de l'API MoMo de MTN Bénin
# README

Ce projet est une implémentation PHP pour interagir avec l'API MoMo Developer de MTN. Il permet de :

1. Créer un utilisateur API.
2. Générer une clé API pour cet utilisateur.
3. Obtenir un token d'accès.
4. Effectuer une demande de paiement.
5. Vérifier le solde d'un compte.
6. Vérifier le statut d'une transaction.

## Prérequis

1. **PHP** : Version 7.4 ou supérieure.
2. **Composer** : Utilisé pour inclure les dépendances comme `ramsey/uuid`.
3. **Clé d'abonnement MTN** : Vous devez disposer d'une clé d'abonnement valide pour le sandbox de MTN MoMo Developer.
4. **Accès Internet** : Nécessaire pour effectuer les appels API.
5. **Serveur Web** : Un serveur capable d'exécuter des scripts PHP (Apache, Nginx, etc.).

## Installation

1. **Clonez ce dépôt ou copiez le code** :
   ```bash
   git clone https://votre-repo-git.com
   ```
2. **Installez les dépendances** :
   ```bash
   composer install
   ```
3. **Créez un fichier de configuration `.env`** (si applicable) pour stocker vos clés sensibles, comme votre `Ocp-Apim-Subscription-Key`.

## Configuration

Assurez-vous de remplacer les valeurs par défaut dans le script PHP par vos propres clés et identifiants :

- **`$subscriptionKey`** : Votre clé d'abonnement.
- **`providerCallbackHost`** : L'URL de votre serveur pour recevoir les callbacks (ex. : `http://example.com/callback`).

## Utilisation

### Étapes principales

#### 1. Créer un utilisateur API

- Appelez l'API `https://sandbox.momodeveloper.mtn.com/v1_0/apiuser` avec une `X-Reference-Id` unique.
- L'utilisateur sera créé et vous recevrez une réponse avec le statut HTTP.

#### 2. Générer une clé API

- Appelez l'API `https://sandbox.momodeveloper.mtn.com/v1_0/apiuser/{X-Reference-Id}/apikey` pour générer une clé API associée à l'utilisateur créé.
- Cette clé sera utilisée pour obtenir un token d'accès.

#### 3. Obtenir un token d'accès

- Appelez l'API `https://sandbox.momodeveloper.mtn.com/collection/token/` avec les identifiants de l'utilisateur (UUID et clé API).
- Vous recevrez un `access_token`.

#### 4. Effectuer une demande de paiement

- Appelez l'API `https://sandbox.momodeveloper.mtn.com/collection/v1_0/requesttopay` en passant les détails de la transaction (montant, devise, numéro de téléphone du payeur, etc.).
- Vous recevrez une confirmation si la demande a été acceptée.

#### 5. Vérifier le solde d'un compte

- Appelez l'API `https://sandbox.momodeveloper.mtn.com/collection/v1_0/account/balance` avec le token d'accès pour vérifier le solde.

#### 6. Vérifier le statut d'une transaction

- Appelez l'API `https://sandbox.momodeveloper.mtn.com/collection/v1_0/requesttopay/{X-Reference-Id}` pour connaître le statut d'une transaction.

## Points importants

- **Sandbox uniquement** : Ce script est configuré pour le mode sandbox. En production, remplacez `sandbox` par `mtnbenin` dans les URLs.
- **SSL** : Pour le sandbox, la vérification SSL est désactivée (`CURLOPT_SSL_VERIFYPEER = false`). En production, assurez-vous d'activer cette vérification.
- **UUID** : Utilisez des identifiants uniques pour chaque demande.

## Exemple d'exécution

Lancez le script PHP via un navigateur ou en ligne de commande :

```bash
php api_mtn.php
```

Le script affichera les réponses des différentes étapes sous forme JSON ou en texte brut. Vous pourrez ensuite utiliser ces différentes réponses sans problème

## Dépendances

- `ramsey/uuid` : Pour générer des UUID uniques.

