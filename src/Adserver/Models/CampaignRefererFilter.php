<?php

namespace Adserver\Models;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as SERIALIZER;

/**
 * User
 *
 * @ORM\Table(name="campaign_referer_filter")
 * @ORM\Entity(repositoryClass="Fbn\Doctrine\SmartRepository")
 * @ORM\ChangeTrackingPolicy("NOTIFY")
 */
class CampaignRefererFilter extends \Fbn\Doctrine\SmartModel
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
     * @ORM\Column(name="referer", type="string") 
     * */
    protected $referer;
    
    /**
     * @var boolean
     *
     * @ORM\Column(name="hostname_only", type="boolean")
     * */
    protected $hostnameOnly=false;
    
    /**
     * @ORM\ManyToOne(targetEntity="Campaign", inversedBy="campaignRefererFilterList")
     * @ORM\JoinColumn(name="campaign_id", referencedColumnName="id", onDelete="CASCADE")
     **/
    protected $campaign;
    
    
}
