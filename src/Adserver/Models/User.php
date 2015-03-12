<?php

namespace Adserver\Models;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as SERIALIZER;

/**
 * User
 *
 * @ORM\Table(name="user")
 * @ORM\Entity(repositoryClass="Fbn\Doctrine\SmartRepository")
 * @ORM\ChangeTrackingPolicy("NOTIFY")
 */
class User extends \Fbn\Doctrine\SmartModel
{
	
	const ROLE_ADMIN = 'ROLE_ADMIN';
	const ROLE_CUSTOMER = 'ROLE_CUSTOMER';
	
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    protected $id;
    
    /**
     * @var string
     *
     * @ORM\Column(name="email", type="string", length=50, unique=true)
     */
    protected $email;

    /**
     * @var string
     *
     * @ORM\Column(name="password", type="string", length=150, nullable=false)
     */
    protected $password;

    /**
     * @var string
     *
     * @ORM\Column(name="role", type="string", length=25, nullable=false)
     */
    protected $role;

    /**
     * @var string
     *
     * @ORM\Column(name="firstname", type="string", length=32, nullable=true)
     */
    protected $firstname;

    /**
     * @var string
     *
     * @ORM\Column(name="lastname", type="string", length=32, nullable=true)
     */
    protected $lastname;

    /**
     * @var boolean
     *
     * @ORM\Column(name="active", type="boolean", nullable=false)
     */
    protected $active = true;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="creationTime", type="datetime", nullable=false)
     */
    protected $creationTime;
    
    /**
     * @var \DateTime
     *
     * @ORM\Column(name="loginTime", type="datetime", nullable=false)
     */
    protected $loginTime;

    /**
     * @ORM\ManyToMany(targetEntity="Campaign", inversedBy="userList")
     * @ORM\JoinTable(name="user_campaign")
     **/
    protected $campaignList;
    
    public function getRoleList(){
    	if(!$this->role) return array();
    	return explode(',', $this->role);
    }
    
    public function setRoleList($roles){
    	$this->role = implode(',', $roles);
    }
    
    public function isAdmin(){
    	 return in_array(self::ROLE_ADMIN, $this->getRoleList());
    }
    
    public function setRoleAdmin(){
    	$this->setRole(self::ROLE_ADMIN);
    }
    
    public function setRoleAgent(){
    	$this->setRole(self::ROLE_AGENT);
    }
    
    public function __construct(){
    	$this->creationTime = new \DateTime();
    	$this->loginTime = $this->creationTime;
    	$this->setRoleList(array(self::ROLE_AGENT));
    	$this->campaignList = new \Doctrine\Common\Collections\ArrayCollection();
    }
    
    public function getLabel(){
    	if($this->firstname||$this->lastname){
    		return $this->firstname.($this->lastname?' '.$this->lastname:'');
    	}
    	return substr($this->email, strpos($this->email,"@"));
    }
    
}
