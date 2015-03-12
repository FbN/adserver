<?php
namespace Adserver\Security;


use Symfony\Component\Security\Core\User\AdvancedUserInterface;
use Adserver\Models\User;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

final class Principal implements AdvancedUserInterface {
	
	const AVATAR = 'retro';
	const AVATAR_SIZE = 215;
	
    private $username;
    private $password;
    private $firstname;
    private $lastname;
    private $id;    
    private $enabled;
    private $accountNonExpired;
    private $credentialsNonExpired;
    private $accountNonLocked;
    private $roles;

    public function __construct($username, $password, $id, $firstname=null, $lastname=null, array $roles = array(), $enabled = true, $userNonExpired = true, $credentialsNonExpired = true, $userNonLocked = true)
    {
        if (empty($username)) {
            throw new \InvalidArgumentException('The username cannot be empty.');
        }

        $this->username = $username;
        $this->password = $password;
        $this->id = $id;
        $this->enabled = $enabled;
        $this->firstname=$firstname;
        $this->lastname=$lastname;
        $this->accountNonExpired = $userNonExpired;
        $this->credentialsNonExpired = $credentialsNonExpired;
        $this->accountNonLocked = $userNonLocked;
        $this->roles = $roles;
    }

    /**
     * {@inheritdoc}
     */
    public function getRoles()
    {
        return $this->roles;
    }

    /**
     * {@inheritdoc}
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * {@inheritdoc}
     */
    public function getSalt()
    {
    }

    /**
     * {@inheritdoc}
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * {@inheritdoc}
     */
    public function isAccountNonExpired()
    {
        return $this->accountNonExpired;
    }

    /**
     * {@inheritdoc}
     */
    public function isAccountNonLocked()
    {
        return $this->accountNonLocked;
    }

    /**
     * {@inheritdoc}
     */
    public function isCredentialsNonExpired()
    {
        return $this->credentialsNonExpired;
    }

    /**
     * {@inheritdoc}
     */
    public function isEnabled()
    {
        return $this->enabled;
    }

    /**
     * {@inheritdoc}
     */
    public function eraseCredentials()
    {
    }
    
    public function getFirstname(){return $this->firstname;}
    
    public function getLastname(){return $this->lastname;}
    
    public function getLabel(){
    	if($this->firstname) return $this->firstname;
    	if($this->lastname) return $this->lastname;
    	return $this->username;
    	}
    	
    public function getAvatar(){
    	return "http://www.gravatar.com/avatar/" . md5( strtolower( trim( $this->username ) ) ) . "?d=" . urlencode( self::AVATAR ) . "&s=" . self::AVATAR_SIZE;
    }
    
    public function getId(){
    	return $this->id;
    }
  
    public function isGrantedAdmin(){    	
    	return in_array(\Adserver\Models\User::ROLE_ADMIN, $this->roles);
    }
    
    public function isGrantedAgent(){
    	return in_array(\Adserver\Models\User::ROLE_CUSTOMER, $this->roles);
    }
    
    public function assertGrantedAdmin(){
    	if(!$this->isGrantedAdmin()){
    		throw new AccessDeniedException('You are not Admin');
    	}
    }
    
    public function assertGrantedAgent(){
    	if(!$this->isGrantedAgent()){
    		throw new AccessDeniedException('You are not allowed');
    	}
    }
    
}