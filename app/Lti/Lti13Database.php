<?php

namespace App\Lti;
use App\Models\Lti13Registration;
use Packback\Lti1p3\Interfaces\IDatabase;
use Packback\Lti1p3\LtiDeployment;
use Packback\Lti1p3\LtiRegistration;
use Packback\Lti1p3\OidcException;

class Lti13Database implements IDatabase
{
    /**
     * @throws OidcException
     */
    public static function findIssuer($issuer_url, $client_id = null)
    {
        $query = Lti13Registration::where('issuer', $issuer_url);
        if ($client_id) {
            $query = $query->where('client_id', $client_id);
        }
        if ($query->count() > 1) {
            throw new OidcException('Found multiple registrations for the given issuer, ensure a client_id is specified on login (contact your LMS administrator)', 1);
        }
        return $query->first();
    }

    public function findRegistrationByIssuer($issuer, $client_id = null)
    {
        $issuer = self::findIssuer($issuer, $client_id);
        if (!$issuer) {
            return false;
        }

        return LtiRegistration::new()
            ->setAuthTokenUrl($issuer->platform_auth_token_endpoint)
            ->setAuthLoginUrl($issuer->platform_login_auth_endpoint)
            ->setClientId($issuer->client_id)
            ->setKeySetUrl($issuer->platform_key_set_endpoint)
            ->setKid($issuer->ltiKey->kid)
            ->setIssuer($issuer->issuer)
            ->setToolPrivateKey($issuer->ltiKey->private_key);
    }

    public function findDeployment($issuer, $deployment_id, $client_id = null)
    {
        $issuerModel = self::findIssuer($issuer, $client_id);
        if (!$issuerModel) {
            return false;
        }
        $deployment = $issuerModel->ltiDeployments()->where('deployment_id', $deployment_id)->first();
        if (!$deployment) {
            return false;
        }

        return LtiDeployment::new()
            ->setDeploymentId($deployment->id);
    }
}
