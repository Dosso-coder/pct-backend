<?php

namespace App\Http\Controllers;

use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterAdminRequest;
use App\Http\Requests\Auth\RegisterEnseignantRequest;
use App\Http\Requests\Auth\RegisterSecretaireRequest;
use App\Http\Requests\Auth\UpdateProfileRequest;
use App\Models\Administrateur;
use App\Models\Enseignant;
use App\Models\SecretairePrincipal;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    public function register(Request $request): JsonResponse
    {
        $type = $request->input('type');

        return match ($type) {
            'administrateur' => $this->registerAdministrateur(app(RegisterAdminRequest::class)),
            'secretaire' => $this->registerSecretaire(app(RegisterSecretaireRequest::class)),
            'enseignant' => $this->registerEnseignant(app(RegisterEnseignantRequest::class)),
            default => $this->error('Le type d\'utilisateur est invalide.', [
                'type' => ['Le type doit etre administrateur, secretaire ou enseignant.'],
            ], 422),
        };
    }

    public function login(LoginRequest $request): JsonResponse
    {
        $type = $request->input('type');

        return match ($type) {
            'administrateur' => $this->loginUser(
                $request,
                Administrateur::class,
                'user_log_adm',
                'user_pasw_adm',
                ['user_log_adm', 'nom_adm', 'pren_adm', 'email_adm', 'ann_aca', 'rol_usr']
            ),
            'secretaire' => $this->loginUser(
                $request,
                SecretairePrincipal::class,
                'user_log_sp',
                'user_pasw_sp',
                ['user_log_sp', 'user_log_adm', 'nom_sp', 'pren_sp', 'email_sp', 'rol_sp']
            ),
            'enseignant' => $this->loginUser(
                $request,
                Enseignant::class,
                'user_log_ens',
                'user_pasw_ens',
                [
                    'id_ens',
                    'user_log_ens',
                    'user_log_adm',
                    'user_log_sp',
                    'id_grade',
                    'id_statut',
                    'id_depart',
                    'nom_ens',
                    'pren_ens',
                    'email_ens',
                    'tel_ens',
                    'taux_hor_ens',
                ]
            ),
            default => $this->error('Le type d\'utilisateur est invalide.', [
                'type' => ['Le type doit etre administrateur, secretaire ou enseignant.'],
            ], 422),
        };
    }

    public function logout(Request $request): JsonResponse
    {
        $user = $request->user();
        Log::info('Déconnexion', ['user' => $user?->getKey(), 'ip' => $request->ip()]);
        $user?->currentAccessToken()?->delete();

        return $this->success([], 'Deconnexion reussie.');
    }

    private function registerAdministrateur(RegisterAdminRequest $request): JsonResponse
    {
        $data = $request->validated();

        if (DB::table('administrateur')->where('user_log_adm', $data['user_log_adm'])->exists()) {
            return $this->error('Cet administrateur existe deja.', [
                'user_log_adm' => ['Ce login est deja utilise.'],
            ], 409);
        }

        unset($data['type']);
        $data['user_pasw_adm'] = Hash::make($data['user_pasw_adm']);

        DB::table('administrateur')->insert($data);

        return $this->success([
            'user_log_adm' => $data['user_log_adm'],
            'ann_aca' => $data['ann_aca'],
            'rol_usr' => $data['rol_usr'],
        ], 'Administrateur inscrit avec succes.', 201);
    }

    private function registerSecretaire(RegisterSecretaireRequest $request): JsonResponse
    {
        $data = $request->validated();

        if (DB::table('secretaire_principal')->where('user_log_sp', $data['user_log_sp'])->exists()) {
            return $this->error('Ce secretaire existe deja.', [
                'user_log_sp' => ['Ce login est deja utilise.'],
            ], 409);
        }

        if (DB::table('secretaire_principal')->where('email_sp', $data['email_sp'])->exists()) {
            return $this->error('Ce secretaire existe deja.', [
                'email_sp' => ['Cet email est deja utilise.'],
            ], 409);
        }

        unset($data['type']);
        $data['user_pasw_sp'] = Hash::make($data['user_pasw_sp']);

        DB::table('secretaire_principal')->insert($data);

        return $this->success([
            'user_log_sp' => $data['user_log_sp'],
            'user_log_adm' => $data['user_log_adm'],
            'nom_sp' => $data['nom_sp'],
            'pren_sp' => $data['pren_sp'],
            'email_sp' => $data['email_sp'],
            'rol_sp' => $data['rol_sp'],
        ], 'Secretaire principal inscrit avec succes.', 201);
    }

    private function registerEnseignant(RegisterEnseignantRequest $request): JsonResponse
    {
        $data = $request->validated();

        if (DB::table('enseignants')->where('user_log_ens', $data['user_log_ens'])->exists()) {
            return $this->error('Cet enseignant existe deja.', [
                'user_log_ens' => ['Ce login est deja utilise.'],
            ], 409);
        }

        if (DB::table('enseignants')->where('email_ens', $data['email_ens'])->exists()) {
            return $this->error('Cet enseignant existe deja.', [
                'email_ens' => ['Cet email est deja utilise.'],
            ], 409);
        }

        unset($data['type']);
        $data['user_pasw_ens'] = Hash::make($data['user_pasw_ens']);

        DB::table('enseignants')->insert($data);

        return $this->success([
            'user_log_ens' => $data['user_log_ens'],
            'user_log_adm' => $data['user_log_adm'],
            'user_log_sp' => $data['user_log_sp'],
            'nom_ens' => $data['nom_ens'],
            'pren_ens' => $data['pren_ens'],
            'email_ens' => $data['email_ens'],
            'tel_ens' => $data['tel_ens'],
        ], 'Enseignant inscrit avec succes.', 201);
    }

    private function loginUser(
        Request $request,
        string $modelClass,
        string $loginField,
        string $passwordField,
        array $publicFields
    ): JsonResponse {
        $login = $request->input('login');
        $password = $request->input('password');

        $user = $modelClass::query()->where($loginField, $login)->first();

        if (! $user || ! Hash::check($password, $user->{$passwordField})) {
            Log::warning('Tentative de connexion échouée', [
                'login' => $login,
                'type' => $request->input('type'),
                'ip' => $request->ip(),
            ]);

            return $this->error('Identifiants invalides.', [], 401);
        }

        if (isset($user->status) && $user->status === 'INACTIF') {
            return $this->error('Votre compte a été désactivé. Veuillez contacter l\'administrateur.', [], 403);
        }

        DB::table($user->getTable())
            ->where($loginField, $login)
            ->update(['last_login_at' => now()]);

        $userData = [];
        foreach ($publicFields as $field) {
            $userData[$field] = $user->{$field};
        }

        $user->tokens()->delete();
        $plainTextToken = $user->createToken(
            $request->input('type').'-'.Str::slug($login),
            [$request->input('type')]
        )->plainTextToken;

        Log::info('Connexion réussie', [
            'login' => $login,
            'type' => $request->input('type'),
            'ip' => $request->ip(),
        ]);

        return $this->success([
            'type' => $request->input('type'),
            'token_type' => 'Bearer',
            'access_token' => $plainTextToken,
            'user' => $userData, // Changed 'data' to 'user' to avoid nested data if possible, or keep as is.
        ], 'Connexion reussie.');
    }

    public function updateProfile(UpdateProfileRequest $request): JsonResponse
    {
        $user = auth()->user();

        if (! $user) {
            return $this->error('Non authentifié', [], 401);
        }

        $data = $request->validated();
        $table = null;
        $whereField = null;
        $loginField = null;

        if (isset($user->user_log_adm)) {
            $table = 'administrateur';
            $whereField = 'user_log_adm';
            $loginField = $user->user_log_adm;
        } elseif (isset($user->user_log_sp)) {
            $table = 'secretaire_principal';
            $whereField = 'user_log_sp';
            $loginField = $user->user_log_sp;
        } elseif (isset($user->user_log_ens)) {
            $table = 'enseignants';
            $whereField = 'user_log_ens';
            $loginField = $user->user_log_ens;
        }

        if (! $table || ! $loginField) {
            return $this->error('Type d\'utilisateur invalide', [], 400);
        }

        $updates = [];

        if (isset($data['nom'])) {
            $parts = explode(' ', $data['nom'], 2);
            $prefix = match ($table) {
                'administrateur' => 'adm',
                'enseignants' => 'ens',
                'secretaire_principal' => 'sp',
            };
            $updates["pren_{$prefix}"] = $parts[0] ?? '';
            $updates["nom_{$prefix}"] = $parts[1] ?? $parts[0];
        }

        if (isset($data['email'])) {
            $emailField = match ($table) {
                'administrateur' => 'email_adm',
                'enseignants' => 'email_ens',
                'secretaire_principal' => 'email_sp',
            };
            $updates[$emailField] = $data['email'];
        }

        if (isset($data['password']) && ! empty($data['password'])) {
            $passwordField = match ($table) {
                'administrateur' => 'user_pasw_adm',
                'enseignants' => 'user_pasw_ens',
                'secretaire_principal' => 'user_pasw_sp',
            };
            $updates[$passwordField] = Hash::make($data['password']);
        }

        if (! empty($updates)) {
            DB::table($table)
                ->where($whereField, $loginField)
                ->update($updates);
        }

        $updatedUser = DB::table($table)->where($whereField, $loginField)->first();
        $prefix = match ($table) {
            'administrateur' => 'adm',
            'enseignants' => 'ens',
            'secretaire_principal' => 'sp',
        };

        return $this->success([
            'nom' => trim(($updatedUser->{"pren_{$prefix}"} ?? '').' '.($updatedUser->{"nom_{$prefix}"} ?? '')),
            'email' => $updatedUser->{"email_{$prefix}"} ?? '',
            'login' => $updatedUser->{$whereField} ?? '',
        ], 'Profil mis à jour avec succès');
    }
}
