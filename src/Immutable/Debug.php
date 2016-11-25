<?php

namespace Potherca\Katwizy\Immutable;

use Symfony\Component\HttpFoundation\Request;

class Debug
{
    ////////////////////////////// CLASS PROPERTIES \\\\\\\\\\\\\\\\\\\\\\\\\\\\
    const DEBUG_OFF = '0';
    const DEBUG_ON = '1';
    const DEBUG_TOKEN = 'debug-token';

    /** @var bool */
    private $debug;
    /** @var Request */
    private $request;

    //////////////////////////// SETTERS AND GETTERS \\\\\\\\\\\\\\\\\\\\\\\\\\\
    /**
     * @return bool
     */
    private function getDebug()
    {
        if ($this->debug === null) {
            $this->debug = $this->createDebug();
        }

        return $this->debug;
    }

    //////////////////////////////// PUBLIC API \\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\
    /**
     * @param Request $request
     */
    final public function __construct(Request $request = null)
    {
        $this->request = $request;
    }

    final public function __toString()
    {
        return $this->getDebug()
            ? self::DEBUG_ON
            : self::DEBUG_OFF
        ;
    }

    ////////////////////////////// UTILITY METHODS \\\\\\\\\\\\\\\\\\\\\\\\\\\\\
    /**
     * @return bool
     */
    private function createDebug()
    {
        $debug = getenv('SYMFONY_DEBUG');

        if ((int)$debug !== 0) {
            $debug = true;
        } else {
            $systemDebugToken = getenv('DEBUG_TOKEN');
            $userDebugToken = null;

            $request = $this->request;

            if ($request !== null) {
                $userDebugToken = $this->debugToken($request);
                $cookieDebugToken = $request->cookies->get(self::DEBUG_TOKEN);

                if ($userDebugToken === null) {
                    $userDebugToken = $cookieDebugToken;
                } elseif ($userDebugToken !== $cookieDebugToken) {
                    $this->setCookie($userDebugToken);
                } // else { /*Cookie already set*/ }
            }

            if ($userDebugToken !== null && $userDebugToken === $systemDebugToken) {
                $debug = true;
            }
        }

        return $debug;
    }

    /**
     * @param Request $request
     *
     * @return string|null
     */
    private function debugToken(Request $request)
    {
        $tokens = [
            $request->request->get(self::DEBUG_TOKEN),   // $_POST
            $request->query->get(self::DEBUG_TOKEN),     // $_GET
            $request->headers->get(self::DEBUG_TOKEN),   // Header
        ];

        /* Remove empty values from array */
        $tokens = array_filter($tokens);

        return array_shift($tokens);
    }

    /**
     * @param mixed $userDebugToken
     */
    private function setCookie($userDebugToken)
    {
        $name = self::DEBUG_TOKEN;
        $value = $userDebugToken;
        $expire = 0;
        $path = '/';
        $domain = null;
        $secure = false;
        $httpOnly = true;

        try {
            $domain = $this->request->getHost();
        } catch (\UnexpectedValueException $exception) {
            // Host name is invalid
        }

        setcookie($name, $value, $expire, $path, $domain, $secure, $httpOnly);
    }

}
