<?php

/**
 * Panda user interface
 * 
 * A common interface to manage users. You have to implement it to
 * use the users service with the application.
 * 
 * @package Panda
 * @author Stanislas Michalak <stanislas.michalak@gmail.com>
 */

namespace Panda\component\user;

interface UserInterface
{
	public function getRoles();

	public function getPassword();

    public function getPasswordSalt();

	public function getSessionSalt();

	public function getUsername();

	public function isOnline();
}