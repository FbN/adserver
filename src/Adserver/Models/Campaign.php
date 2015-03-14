<?php

namespace Adserver\Models;

use Doctrine\ORM\Mapping as ORM;
use \Doctrine\Common\Collections\ArrayCollection;
use JMS\Serializer\Annotation as SERIALIZER;

/**
 * User
 *
 * @ORM\Table(name="campaign")
 * @ORM\Entity(repositoryClass="Fbn\Doctrine\SmartRepository")
 * @ORM\ChangeTrackingPolicy("NOTIFY")
 */
class Campaign extends \Fbn\Doctrine\SmartModel
{
	
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
     * @ORM\Column(name="name", type="string", length=50, unique=true)
     */
    protected $name;
    
    /**
     * @var boolean
     *
     * @ORM\Column(name="active", type="boolean")
     */
    protected $active=true;
    
    /**
     * @var integer
     *
     * @ORM\Column(name="goal", type="integer", nullable=true)
     */
    protected $goal;
    
    /**
     * @var integer
     *
     * @ORM\Column(name="delivered", type="integer")
     */
    protected $delivered=0;
        
    /**
     * @ORM\ManyToMany(targetEntity="User", mappedBy="campaignList")
     **/
    protected $userList;
    
    /**
     * @ORM\OneToMany(targetEntity="CampaignRuntime", mappedBy="campaign")
     **/
    protected $campaignRuntimeList;
    
    /**
     * @ORM\OneToMany(targetEntity="Banner", mappedBy="campaign")
     **/
    protected $bannerList;
    
    /**
     * @ORM\OneToMany(targetEntity="CampaignTimeFilter", mappedBy="campaign")
     **/
    protected $campaignTimeFilterList;
    
    /**
     * @ORM\OneToMany(targetEntity="CampaignCookieFilter", mappedBy="campaign")
     **/
    protected $campaignCookieFilterList;
    
    /**
     * @ORM\OneToMany(targetEntity="CampaignRefererFilter", mappedBy="campaign")
     **/
    protected $campaignRefererFilterList;
    
    public function __construct() {
    	$this->userList = new ArrayCollection();
    	$this->campaignRuntimeList = new ArrayCollection();
    	$this->bannerList = new ArrayCollection();
    	$this->campaignTimeFilterList = new ArrayCollection();
    	$this->campaignCookieFilterList = new ArrayCollection();
    	$this->campaignRefererFilterList = new ArrayCollection();
    }
    
}
