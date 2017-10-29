<?php
/**
 * User: tuttarealstep
 * Date: 01/07/17
 * Time: 11.27
 */

namespace Honey\Sessions;

class SessionsManager
{
    /**
     * @param null $sessionPath
     */
    function secureSessionPath($sessionPath = null)
    {
        if(is_null($sessionPath))
        {
            $sessionPath = sys_get_temp_dir();
        }
        session_save_path($sessionPath);
    }

    function startSession()
    {
        session_start();

        if(empty($_SESSION['time']))
        {
            $_SESSION['time'] = time();
            $this->regenerateSession();
        }
    }

    /**
     * @param bool $reload
     */
    function regenerateSession($reload = false)
    {
        if(!isset($_SESSION['nonce']) || $reload)
            $_SESSION['nonce'] = md5(microtime(true));

        if(!isset($_SESSION['ipAddress']) || $reload)
            $_SESSION['ipAddress'] = $_SERVER['REMOTE_ADDR'];

        if(!isset($_SESSION['userAgent']) || $reload)
            $_SESSION['userAgent'] = $_SERVER['HTTP_USER_AGENT'];

        $_SESSION['OBSOLETE'] = true;
        $_SESSION['EXPIRES'] = time() + 60;

        session_regenerate_id(false);

        $newSession = session_id();
        session_write_close();

        session_id($newSession);
        session_start();

        unset($_SESSION['OBSOLETE']);
        unset($_SESSION['EXPIRES']);
    }

    /**
     * @param bool $reloadRegenerateSession
     */
    function checkSession($reloadRegenerateSession = false)
    {
        try
        {
            if($_SESSION['OBSOLETE'] && ($_SESSION['EXPIRES'] < time()))
                throw new \Exception('Attempt to use expired session.');

            if($_SESSION['ipAddress'] != $_SERVER['REMOTE_ADDR'])
                throw new \Exception('IP Address mixmatch (possible session hijacking attempt).');

            if($_SESSION['userAgent'] != $_SERVER['HTTP_USER_AGENT'])
                throw new \Exception('Useragent mixmatch (possible session hijacking attempt).');

            if(!$_SESSION['OBSOLETE'] && mt_rand(1, 100) == 1)
            {
                $this->regenerateSession($reloadRegenerateSession);
            }
        } catch(\Exception $e)
        {
            die($e->getMessage());
        }
    }
}