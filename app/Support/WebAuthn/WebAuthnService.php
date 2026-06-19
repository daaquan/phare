<?php

namespace App\Support\WebAuthn;

use App\Models\Passkey;
use App\Models\User;
use Cose\Algorithms;
use Symfony\Component\Serializer\SerializerInterface;
use Webauthn\AttestationStatement\AttestationStatementSupportManager;
use Webauthn\AttestationStatement\NoneAttestationStatementSupport;
use Webauthn\AuthenticatorAssertionResponse;
use Webauthn\AuthenticatorAssertionResponseValidator;
use Webauthn\AuthenticatorAttestationResponse;
use Webauthn\AuthenticatorAttestationResponseValidator;
use Webauthn\AuthenticatorSelectionCriteria;
use Webauthn\CeremonyStep\CeremonyStepManagerFactory;
use Webauthn\CredentialRecord;
use Webauthn\Denormalizer\WebauthnSerializerFactory;
use Webauthn\PublicKeyCredential;
use Webauthn\PublicKeyCredentialCreationOptions;
use Webauthn\PublicKeyCredentialDescriptor;
use Webauthn\PublicKeyCredentialParameters;
use Webauthn\PublicKeyCredentialRequestOptions;
use Webauthn\PublicKeyCredentialRpEntity;
use Webauthn\PublicKeyCredentialUserEntity;

/**
 * WebAuthn（パスキー）のサーバー側処理ラッパー。
 *
 * 署名・アテステーション検証という暗号処理は web-auth/webauthn-lib に委譲する。
 * TOTP と違い WebAuthn（CBOR + COSE + EC/RSA 署名）の自前実装はセキュリティ
 * リスクが高いため、アプリ側はオプション生成・資格情報の保存・ログイン接続だけを担う。
 */
final class WebAuthnService
{
    private SerializerInterface $serializer;

    private CeremonyStepManagerFactory $ceremonies;

    public function __construct()
    {
        // アテステーション形式は none のみ受け付ける（プラットフォーム認証器のパスキー用途）。
        $attestationManager = new AttestationStatementSupportManager([
            new NoneAttestationStatementSupport(),
        ]);

        $this->serializer = (new WebauthnSerializerFactory($attestationManager))->create();

        $this->ceremonies = new CeremonyStepManagerFactory();
        $this->ceremonies->setAttestationStatementSupportManager($attestationManager);
        $this->ceremonies->setAllowedOrigins([$this->origin()]);
    }

    // ------------------------------------------------------------------
    // 登録 (attestation)
    // ------------------------------------------------------------------

    /**
     * 登録用オプションを生成し JSON を返す。チャレンジ照合のため、呼び出し側で
     * この JSON をセッションへ退避しておくこと。
     */
    public function creationOptions(User $user): string
    {
        $rp = PublicKeyCredentialRpEntity::create($this->rpName(), $this->rpId());

        $userEntity = PublicKeyCredentialUserEntity::create(
            (string)$user->email,
            (string)$user->id,
            (string)$user->name,
        );

        // 既存パスキーは除外（同じ認証器での二重登録を防ぐ）。
        $exclude = [];
        foreach (Passkey::findByUserId((int)$user->id) as $passkey) {
            $exclude[] = PublicKeyCredentialDescriptor::create(
                PublicKeyCredentialDescriptor::CREDENTIAL_TYPE_PUBLIC_KEY,
                self::base64urlDecode((string)$passkey->credential_id),
            );
        }

        $options = PublicKeyCredentialCreationOptions::create(
            $rp,
            $userEntity,
            random_bytes(32),
            [
                PublicKeyCredentialParameters::createPk(Algorithms::COSE_ALGORITHM_ES256),
                PublicKeyCredentialParameters::createPk(Algorithms::COSE_ALGORITHM_RS256),
            ],
            AuthenticatorSelectionCriteria::create(
                null,
                AuthenticatorSelectionCriteria::USER_VERIFICATION_REQUIREMENT_PREFERRED,
                AuthenticatorSelectionCriteria::RESIDENT_KEY_REQUIREMENT_PREFERRED,
            ),
            PublicKeyCredentialCreationOptions::ATTESTATION_CONVEYANCE_PREFERENCE_NONE,
            $exclude,
        );

        return $this->serializer->serialize($options, 'json');
    }

    /**
     * ブラウザからの登録レスポンスを検証し、成功すれば資格情報を保存する。
     *
     * @param string $optionsJson 発行時にセッションへ退避した creationOptions の JSON
     * @param string $responseJson ブラウザが返した PublicKeyCredential の JSON
     *
     * @throws \Throwable 検証失敗時
     */
    public function verifyAndStore(User $user, string $optionsJson, string $responseJson, string $name): Passkey
    {
        /** @var PublicKeyCredentialCreationOptions $options */
        $options = $this->serializer->deserialize(
            $optionsJson,
            PublicKeyCredentialCreationOptions::class,
            'json',
        );

        $credential = $this->serializer->deserialize($responseJson, PublicKeyCredential::class, 'json');
        if (!$credential->response instanceof AuthenticatorAttestationResponse) {
            throw new \RuntimeException('Invalid attestation response');
        }

        $validator = AuthenticatorAttestationResponseValidator::create(
            $this->ceremonies->creationCeremony(),
        );

        $record = $validator->check($credential->response, $options, $this->rpId());

        $passkey = new Passkey();
        $passkey->fill([
            'user_id' => (int)$user->id,
            'name' => $name !== '' ? $name : 'パスキー',
            'credential_id' => self::base64urlEncode($record->publicKeyCredentialId),
            'data' => $this->serializer->serialize($record, 'json'),
        ]);
        $passkey->save();

        return $passkey;
    }

    // ------------------------------------------------------------------
    // 認証 (assertion)
    // ------------------------------------------------------------------

    /**
     * ログイン用オプション（ユーザー名なし・discoverable）を生成し JSON を返す。
     * 呼び出し側でセッションへ退避しておくこと。
     */
    public function requestOptions(): string
    {
        $options = PublicKeyCredentialRequestOptions::create(
            random_bytes(32),
            $this->rpId(),
            [], // allowCredentials を空にして discoverable credential を許可
            PublicKeyCredentialRequestOptions::USER_VERIFICATION_REQUIREMENT_PREFERRED,
        );

        return $this->serializer->serialize($options, 'json');
    }

    /**
     * ログイン用アサーションを検証し、成功すれば所有ユーザーを返す（失敗時は null）。
     *
     * @throws \Throwable 検証失敗時
     */
    public function verifyAssertion(string $optionsJson, string $responseJson): ?User
    {
        /** @var PublicKeyCredentialRequestOptions $options */
        $options = $this->serializer->deserialize(
            $optionsJson,
            PublicKeyCredentialRequestOptions::class,
            'json',
        );

        $credential = $this->serializer->deserialize($responseJson, PublicKeyCredential::class, 'json');
        if (!$credential->response instanceof AuthenticatorAssertionResponse) {
            throw new \RuntimeException('Invalid assertion response');
        }

        $passkey = Passkey::findFirstByCredentialId(self::base64urlEncode($credential->rawId));
        if (!$passkey instanceof Passkey) {
            return null;
        }

        /** @var CredentialRecord $record */
        $record = $this->serializer->deserialize((string)$passkey->data, CredentialRecord::class, 'json');

        $validator = AuthenticatorAssertionResponseValidator::create(
            $this->ceremonies->requestCeremony(),
        );

        $updated = $validator->check(
            $record,
            $credential->response,
            $options,
            $this->rpId(),
            $record->userHandle,
        );

        // 署名カウンター等の更新を保存する。
        $passkey->data = $this->serializer->serialize($updated, 'json');
        $passkey->last_used_at = date('Y-m-d H:i:s');
        $passkey->save();

        return User::findFirstById((int)$passkey->user_id);
    }

    // ------------------------------------------------------------------
    // 設定値（Relying Party）
    // ------------------------------------------------------------------

    private function appUrl(): string
    {
        // env() の第2引数に文字列デフォルトを渡すと is_callable 経由で関数扱いされる
        // 危険があるため、?: で安全にフォールバックする。
        return (string)(env('APP_URL') ?: 'http://localhost:8000');
    }

    private function rpId(): string
    {
        return parse_url($this->appUrl(), PHP_URL_HOST) ?: 'localhost';
    }

    private function origin(): string
    {
        $parts = parse_url($this->appUrl());
        $scheme = $parts['scheme'] ?? 'http';
        $host = $parts['host'] ?? 'localhost';
        $port = isset($parts['port']) ? ':' . $parts['port'] : '';

        return "{$scheme}://{$host}{$port}";
    }

    private function rpName(): string
    {
        return (string)config('app.name', 'Phare');
    }

    // ------------------------------------------------------------------
    // base64url
    // ------------------------------------------------------------------

    public static function base64urlEncode(string $bytes): string
    {
        return rtrim(strtr(base64_encode($bytes), '+/', '-_'), '=');
    }

    public static function base64urlDecode(string $value): string
    {
        return (string)base64_decode(strtr($value, '-_', '+/'));
    }
}
