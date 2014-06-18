namespace App;

use Phalcon\Security\Exception;

class Security extends \Phalcon\Security
{

    /**
     * @param $tokenKey
     * @param int $lifetime
     * @param null $numberBytes
     * @return string
     * @throws \Phalcon\Security\Exception
     */
    public function getMultipleToken($tokenKey, $lifetime = 0, $numberBytes = null)
    {
        if (null === $numberBytes) {
            $numberBytes = 12;
        }

        if (0 !== $lifetime) {
            $lifetime = \time() + $lifetime;
        }

        if (false === function_exists('openssl_random_pseudo_bytes')) {
            throw new Exception('Openssl extension must be loaded');
        }
        $token = \md5(openssl_random_pseudo_bytes($numberBytes));

        $this->session->set('$MultipleToken-' . $tokenKey, $token);
        $this->session->set('$MultipleTokenTime-' . $tokenKey, $lifetime);

        return $token;
    }

    /**
     * @param $tokenKey
     * @param $tokenValue
     * @return bool
     */

    public function checkMultipleToken($tokenKey, $tokenValue)
    {
        $token = $this->getMultipleSessionToken($tokenKey);
        if (null !== $token) {
            if ($tokenValue === $token) {
                return true;
            }
        }
        return false;
    }

    /**
     * @param $tokenKey
     * @return string|null
     */
    public function getMultipleSessionToken($tokenKey)
    {
        if ($this->session->has('$MultipleToken-' . $tokenKey)) {
            $token = $this->session->get('$MultipleToken-' . $tokenKey, null);
            $lifetime = (int)$this->session->get('$MultipleTokenTime-' . $tokenKey, 0);
            if (0 === $lifetime) {
                return $token;
            }

            $time = \time();
            if ($time < $lifetime) {
                return $token;
            }else {
                $this->session->remove('$MultipleToken-' . $tokenKey);
                $this->session->remove('$MultipleTokenTime-' . $tokenKey);
            }
        }
        return null;
    }
}
