<?php

namespace Panda\component\user;

interface UserProviderInterface
{
	public function loadUserByProvidedData(UserInterface $user);

	public function refreshUser(UserInterface $user);

	public function userExists($username, $password);
}