<?php

namespace App\Services;

use App\Models\Lti13Key;
use App\Models\Lti13Registration;
use App\Models\LtiKey;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Packback\Lti1p3\Interfaces\ICache;
use Packback\Lti1p3\Interfaces\ICookie;
use Packback\Lti1p3\Interfaces\IDatabase;
use Packback\Lti1p3\Interfaces\ILtiServiceConnector;
use Packback\Lti1p3\JwksEndpoint;
use Packback\Lti1p3\LtiDeepLinkResource;
use Packback\Lti1p3\LtiException;
use Packback\Lti1p3\LtiMessageLaunch;
use Packback\Lti1p3\LtiOidcLogin;
use Packback\Lti1p3\OidcException;

/**
 * Class Lti13Service.
 */
class Lti13Service
{
    public $db;
    public $cache;
    public $cookie;
    public $serviceConnector;
    private $launchUrl;

    public function __construct(
        IDatabase            $db,
        ICache               $cache,
        ICookie              $cookie,
        ILtiServiceConnector $serviceConnector)
    {
        $this->db = $db;
        $this->cache = $cache;
        $this->cookie = $cookie;
        $this->serviceConnector = $serviceConnector;

        $this->launchUrl = env('APP_URL') . "/lti/launches";
    }

    /**
     * @throws LtiException
     */
    public function getCachedLaunch($launch_id): ?LtiMessageLaunch
    {
        return LtiMessageLaunch::fromCache($launch_id, $this->db, $this->cache, $this->serviceConnector);
    }

    /**
     * Validate an LTI launch.
     *
     * @throws LtiException
     */
    public function validateLaunch(Request $request): LtiMessageLaunch
    {
        return LtiMessageLaunch::new($this->db, $this->cache, $this->cookie, $this->serviceConnector)
            ->validate($request->all());
    }

    /**
     * Launch a deep link.
     */
    public function launchDeepLink(LtiMessageLaunch $launch): void
    {
        $resource = LtiDeepLinkResource::new()
            ->setUrl($this->launchUrl);
        $launch->getDeepLink()->outputResponseForm([$resource]);
    }

    /**
     * Get the URL for an OIDC login redirect.
     *
     * @throws OidcException
     */
    public function login(Request $request)
    {
        return LtiOidcLogin::new($this->db, $this->cache, $this->cookie)
            ->doOidcLoginRedirect($this->launchUrl, $request->all())
            ->getRedirectUrl();
    }

    /**
     * Get a JWKS objects (optionally by ID).
     */
    public function getPublicJwks()
    {
        return JwksEndpoint::new(Lti13Key::getKeySets())->getPublicJwks();
    }
}
