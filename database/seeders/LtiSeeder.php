<?php

namespace Database\Seeders;

use App\Models\Lti13Deployment;
use App\Models\Lti13Key;
use App\Models\Lti13Registration;
use Illuminate\Database\Seeder;

class LtiSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $res = openssl_pkey_new();
        openssl_pkey_export($res, $private_key);
        $public_key = openssl_pkey_get_details($res)['key'];

        $key = Lti13Key::create(array(
            'public_key' => $public_key,
            'private_key' => $private_key
        ));

        $registration = Lti13Registration::create(array(
            'issuer' => 'https://canvas-dev.heyhi.sg',
            'client_id' => '10000000000005',
            'platform_login_auth_endpoint' => 'https://canvas-dev.heyhi.sg/api/lti/authorize_redirect',
            'platform_auth_token_endpoint' => 'https://canvas-dev.heyhi.sg/login/oauth2/token',
            'platform_key_set_endpoint' => 'https://canvas-dev.heyhi.sg/api/lti/security/jwks',
            'lti_key_id' => $key->id,
        ));

        Lti13Deployment::create(array(
            'lti_registration_id' => $registration->id,
            'deployment_id' => '6:387e3d3c9786bae26ec46c1ac345da97a6812a5a'
        ));
    }
}
