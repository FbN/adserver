<?php

namespace Adserver\Models;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as SERIALIZER;

/**
 * User
 *
 * @ORM\Table(name="campaign_runtime")
 * @ORM\Entity(repositoryClass="Fbn\Doctrine\SmartRepository")
 * @ORM\ChangeTrackingPolicy("NOTIFY")
 */
class CampaignRuntime extends \Fbn\Doctrine\SmartModel
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
     * @var \DateTime
     * 
     * @ORM\Column(type="datetime", name="start") 
     * */
    protected $start;
    
    /** 
     * @var \DateTime
     * 
     * @ORM\Column(type="datetime", name="end") 
     * */
    protected $end;
    
    /**
     * @ORM\ManyToOne(targetEntity="Campaign", inversedBy="campaignRuntimeList")
     **/
    protected $campaign;
    
    
    public function __construct() {
    	$this->userList = new \Doctrine\Common\Collections\ArrayCollection();
    }
    
}
