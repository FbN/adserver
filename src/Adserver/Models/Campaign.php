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
     * @ORM\Column(name="name", type="string", length=50)
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
     * Optional Cookie Filter
     * 
     * @var string
     * 
     * @ORM\Column(name="cookie", length=150, type="string", nullable=true) 
     * */
    protected $cookie;
    
    
    // === Day / Hours  Filters ===
    
    /**
     * @var boolean
     *
     * @ORM\Column(name="time_filter_active", type="boolean")
     * */
    protected $timeFilterActive=false;
    
    /**
     * @var boolean
     *
     * @ORM\Column(name="d_sunday", type="boolean")
     * */
    protected $dSunday=true;
    
    /**
     * @var boolean
     *
     * @ORM\Column(name="d_monday", type="boolean")
     * */
    protected $dMonday=true;
    
    /**
     * @var boolean
     *
     * @ORM\Column(name="d_tuesday", type="boolean")
     * */
    protected $dTuesday=true;
    
    /**
     * @var boolean
     *
     * @ORM\Column(name="d_wednesday", type="boolean")
     * */
    protected $dWednesday=true;
    
    /**
     * @var boolean
     *
     * @ORM\Column(name="d_thursday", type="boolean")
     * */
    protected $dThursday=true;
    
    /**
     * @var boolean
     *
     * @ORM\Column(name="d_friday", type="boolean")
     * */
    protected $dFriday=true;
    
    /**
     * @var boolean
     *
     * @ORM\Column(name="d_saturday", type="boolean")
     * */
    protected $dSaturday=true;
    
    /**
     * @var boolean
     *
     * @ORM\Column(name="h0", type="boolean")
     * */
    protected $h0=true;
    
    /**
     * @var boolean
     *
     * @ORM\Column(name="h1", type="boolean")
     * */
    protected $h1=true;
    
    /**
     * @var boolean
     *
     * @ORM\Column(name="h2", type="boolean")
     * */
    protected $h2=true;
    
    /**
     * @var boolean
     *
     * @ORM\Column(name="h3", type="boolean")
     * */
    protected $h3=true;
    
    /**
     * @var boolean
     *
     * @ORM\Column(name="h4", type="boolean")
     * */
    protected $h4=true;
    
    /**
     * @var boolean
     *
     * @ORM\Column(name="h5", type="boolean")
     * */
    protected $h5=true;
    
    /**
     * @var boolean
     *
     * @ORM\Column(name="h6", type="boolean")
     * */
    protected $h6=true;
    
    /**
     * @var boolean
     *
     * @ORM\Column(name="h7", type="boolean")
     * */
    protected $h7=true;
    
    /**
     * @var boolean
     *
     * @ORM\Column(name="h8", type="boolean")
     * */
    protected $h8=true;
    
    /**
     * @var boolean
     *
     * @ORM\Column(name="h9", type="boolean")
     * */
    protected $h9=true;
    
    /**
     * @var boolean
     *
     * @ORM\Column(name="h10", type="boolean")
     * */
    protected $h10=true;
    
    /**
     * @var boolean
     *
     * @ORM\Column(name="h11", type="boolean")
     * */
    protected $h11=true;
    
    /**
     * @var boolean
     *
     * @ORM\Column(name="h12", type="boolean")
     * */
    protected $h12=true;
    
    /**
     * @var boolean
     *
     * @ORM\Column(name="h13", type="boolean")
     * */
    protected $h13=true;
    
    /**
     * @var boolean
     *
     * @ORM\Column(name="h14", type="boolean")
     * */
    protected $h14=true;
    
    /**
     * @var boolean
     *
     * @ORM\Column(name="h15", type="boolean")
     * */
    protected $h15=true;
    
    /**
     * @var boolean
     *
     * @ORM\Column(name="h16", type="boolean")
     * */
    protected $h16=true;
    
    /**
     * @var boolean
     *
     * @ORM\Column(name="h17", type="boolean")
     * */
    protected $h17=true;
    
    /**
     * @var boolean
     *
     * @ORM\Column(name="h18", type="boolean")
     * */
    protected $h18=true;
    
    /**
     * @var boolean
     *
     * @ORM\Column(name="h19", type="boolean")
     * */
    protected $h19=true;
    
    /**
     * @var boolean
     *
     * @ORM\Column(name="h20", type="boolean")
     * */
    protected $h20=true;
    
    /**
     * @var boolean
     *
     * @ORM\Column(name="h21", type="boolean")
     * */
    protected $h21=true;
    
    /**
     * @var boolean
     *
     * @ORM\Column(name="h22", type="boolean")
     * */
    protected $h22=true;
    
    /**
     * @var boolean
     *
     * @ORM\Column(name="h23", type="boolean")
     * */
    protected $h23=true;    
    
    
    /**
     * @ORM\OneToMany(targetEntity="CampaignRefererFilter", mappedBy="campaign")
     **/
    protected $campaignRefererFilterList;    
    
    public function __construct() {
    	$this->userList = new ArrayCollection();
    	$this->campaignRuntimeList = new ArrayCollection();
    	$this->bannerList = new ArrayCollection();
    	$this->campaignRefererFilterList = new ArrayCollection();
    }
    
}
