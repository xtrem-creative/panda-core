<?php

/**
 * Panda user
 * 
 * @package Panda
 * @author Stanislas Michalak <stanislas.michalak@gmail.com>
 */

namespace Panda\component\user;

use Panda\Application;
use Panda\component\AbstractComponent;
use Panda\component\error\UnknownUserException;
use Panda\component\error\UserNotConnectedException;
use Panda\component\error\UserAlreadyConnectedException;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Cookie;

abstract class User extends AbstractComponent implements UserInterface
{
    protected $session;
    protected $userProvider;
    protected $components = array();

    protected $roles;
    protected $username;
    protected $password;
    protected $passwordSalt;
    protected $sessionSalt;

    public function __construct(Application $app)
    {
        parent::__construct($app);
        $this->setPasswordSalt($app->getComponent('config\Config')->get('services.user.hash.password_salt'));
        $this->setSessionSalt($app->getComponent('config\Config')->get('services.user.hash.session_salt'));
        $this->session = new Session();
        $this->session->start();
        $this->refreshSession();
    }

    /**
     * Login the user with $username and $password credentials
     * 
     * @param string $username
     * @param string $password
     * @throws UnknownUserException
     */
    public function login($username, $password, $useCookie = false)
    {
        if ($this->isOnline()) {
            throw new UserAlreadyConnectedException('You are already connected as "'.$this->getUsername().'".');
        } else if (is_string($username) && $this->userProvider->userExists($username, \_hash($password, $this->passwordSalt))) {
            $this->setUsername($username);
            $this->userProvider->refreshUser($this);
            $time = time();
            $session = array(
                'username' => $username,
                'time'     => $time,
                'key'      => $this->buildKey($username, $time)
            );
            $this->session->set('__user', $session);
            if ($useCookie) {
                $response = new Response();
                $response->headers->setCookie(new Cookie('__user', serialize($session)));
            }
        } else {
            throw new UnknownUserException('Unkwnown user "'.(string) $username.'".');
        }
    }

    /**
     * Logout the current user
     * 
     * @throws UserNotConnectedException
     */ 
    public function logout()
    {
        if ($this->isOnline()) {
            $session = array(
                'username' => null,
                'time'     => null,
                'key'      => null
            );
            $this->session->set('__user', $session);
            $this->refreshSession();
            if ($this->app->getRequest()->cookies->has('__user')) {
                $response = new Response();
                $response->headers->clearCookie('__user');
            }
        } else {
            throw new UserNotConnectedException('You are not connected.');
        }
    }

    /**
     * Check whether the current user is a guest.
     * 
     * @return bool
     */
    public function isOnline()
    {
        return !empty($this->username);
    }

    protected function refreshSession()
    {
        $session = array(
            'username' => null,
            'time' => time(),
            'key' => ''
        );

        if ($this->session->has('__user')) {
            $session = $this->session->get('__user');
            if ($this->verifKey($session['username'], $session['time'], $session['key'])) {
                $time = time();
                $session = array(
                    'username' => $session['username'],
                    'time'     => $time,
                    'key'      => $this->buildKey($session['username'], $time)
                );
            }
        } else if ($this->app->getRequest()->cookies->has('__user')) {
            $cookie = unserialize($this->app->getRequest()->cookies->get('__user'));
            if ($this->verifKey($cookie['username'], $cookie['time'], $cookie['key'])) {
                $time = time();
                $session = array(
                    'username' => $cookie['username'],
                    'time'     => $time,
                    'key'      => $this->buildKey($cookie['username'], $time)
                );
                $response = new Response();
                $response->headers->setCookie(new Cookie('__user', serialize($session)));
            }
        }
        $this->session->set('__user', $session);
        if ($session['username'] !== null) {
            $this->setUsername($session['username']);
            $this->userProvider->refreshUser($this);
        } else {
            $this->setRoles('ROLE_GUEST');
        }
    }

    protected function verifKey()
    {
        $argList = func_get_args();
        $key = array_pop($argList);

        return call_user_func_array(array($this, "buildKey"), $argList) === $key;
    }

    protected function buildKey()
    {
        $argList = func_get_args();
        $toHash = implode('#', $argList);
        return \_hash($toHash, $this->sessionSalt);
    }

    /**
    * Sets the user attributes with the given data.
    * 
    * @param array $data
    */
    public function hydrate($data)
    {
        if (!is_array($data) && !($data instanceof \ArrayAccess)) {
            throw new \InvalidArgumentException('Can\'t hydrate User with no array argument.');
        }

        if (!empty($data)) {
            foreach ($data as $key => $value) {
                $this->{'set' . ucfirst($key)}($value);
            }
        }
    }

    /**
    * Gets the roles list.
    *
    * @return array
    */
    public function getRoles()
    {
        return $this->roles;
    }

    /**
    * Parses the roles from a string containing a list of roles
    * 
    * @param string $roles
    * @return array
    */
    protected function parseRoles($roles, $operator = '&')
    {
        if ($operator !== '&' && $operator !== '|') {
            throw new \InvalidArgumentException('Invalid role operator ("&" or "|" needed).');
        }

        if (!is_string($roles)) {
            throw new \InvalidArgumentException('Invalid user roles (need a string).');
        }

        if (empty($roles)) {
            return null;
        } else {
            if (!preg_match('#^(ROLE_([A-Z]+))( '.$operator.' (ROLE_([A-Z]+)))*$#', $roles)) {
                throw new \InvalidArgumentException('Invalid user roles (need a string separated by & for AND and | for OR).'); 
            }

            $rolesArray = explode(' '.$operator.' ', $roles);
            return $rolesArray;
        }
    }

    /**
     * Check whether the current user has the given roles.
     * 
     * Roles format : ROLE_X & ROLE_Y | ROLE_Z
     * Operator priority : &, then |
     * 
     * @param string $roles
     * @return bool
     */
    public function hasRoles($roles)
    {
        $userRoles = $this->getRoles();
        if (!empty($roles) && !empty($userRoles)) {
            $rolesArray = explode(' | ', $roles);
            foreach ($rolesArray as $orRoles) {
                $andRoles = explode(' & ', $orRoles);
                $roleVerified = true;
                foreach ($andRoles as $role) {
                    if (!in_array($role, $userRoles)) {
                        $roleVerified = false;
                        break;
                    }
                }
                if ($roleVerified) {
                    break;
                }
            }
            return $roleVerified;
        } else {
            return empty($roles);
        }
    }
    
    /**
    * Sets the roles list.
    *
    * @param string $roles the roles
    */
    public function setRoles($roles)
    {
        $this->roles = $this->parseRoles($roles);
    }

    /**
    * Gets the username.
    *
    * @return string
    */
    public function getUsername()
    {
        return $this->username;
    }
    
    /**
    * Sets the username.
    *
    * @param string $username the username
    */
    public function setUsername($username)
    {
        if (is_string($username) && !empty($username)) {
            $this->username = $username;
        } else {
            throw new \InvalidArgumentException('Invalid user.');
        }
    }

    /**
    * Gets the hashed password.
    *
    * @return string
    */
    public function getPassword()
    {
        return $this->password;
    }

    /**
    * Sets and hash the password.
    *
    * @param string $password the password
    */
    public function setPassword($password)
    {
        $this->password = \_hash($password, $this->getPasswordSalt());
    }

    /**
    * Gets the password salt.
    *
    * @return string
    */
    public function getPasswordSalt()
    {
        return $this->passwordSalt;
    }
    
    /**
    * Sets the password salt.
    *
    * @param string $salt the salt
    */
    public function setPasswordSalt($salt)
    {
        if (is_string($salt) && !empty($salt)) {
            $this->passwordSalt = $salt;
        } else {
            throw new \InvalidArgumentException('Invalid password salt.');
        }
    }

    /**
    * Gets the session salt.
    *
    * @return string
    */
    public function getSessionSalt()
    {
        return $this->sessionSalt;
    }
    
    /**
    * Sets the session salt.
    *
    * @param string $salt the salt
    */
    public function setSessionSalt($salt)
    {
        if (is_string($salt) && !empty($salt)) {
            $this->sessionSalt = $salt;
        } else {
            throw new \InvalidArgumentException('Invalid session salt.');
        }
    }

    public function getSession()
    {
        return $this->session;
    }

    public function getComponent($componentName)
    {
        if (!array_key_exists($componentName, $this->components)) {
            if (is_file(APP_DIR . 'UserBundle/res/service/' . $componentName . '.class.php')) {
                $componentPath = '\\UserBundle\\res\\service\\' . $componentName;
            } else if (is_file(VENDOR_DIR . 'panda/component/user/component/' . $componentName . '.class.php')) {
                $componentPath = '\\Panda\\component\\user\\component\\' . $componentName;
            } else {
                throw new \RuntimeException('Unkwnown "'.$componentName.'" user service');
            }

            if (func_num_args() > 1) {
                $reflection = new \ReflectionClass($componentPath);
                $args = func_get_args();
                unset($args[0]);
                $this->components[$componentName] = $reflection->newInstanceArgs(array_merge(array($this), $args));
            } else {
                $this->components[$componentName] = new $componentPath($this);   
            }
        }
        return $this->components[$componentName];
    }
}