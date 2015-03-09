<?php
namespace Adserver\Security;

use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Agents\Utils\Principal;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use ApiMart\Models\User;

class UserProvider implements UserProviderInterface{
	
	private $em;

	public function __construct($em){
		$this->em = $em;
	}

	public function loadUserByUsername($username){
		
		$u = User::findOneByEmail($this->em, $username);
		
		if (!$u) {
			throw new UsernameNotFoundException(sprintf('Username "%s" does not exist.', $username));
		}

		return new Principal($u->getEmail(), $u->getPassword(), $u->getId(), $u->getFirstname(), $u->getLastname(), $u->getRoleList(), true, true, true, true);
	}

	public function refreshUser(UserInterface $user){
		if (!$user instanceof Principal) {
			throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', get_class($user)));
		}

		return $this->loadUserByUsername($user->getUsername());
	}

	public function supportsClass($class){
		return $class === 'Adserver\Security\Principal';
	}
	
}