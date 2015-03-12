<?php

namespace Adserver\Models;

use Doctrine\ORM\Mapping as ORM;
use \Doctrine\Common\Collections\ArrayCollection;
use JMS\Serializer\Annotation as SERIALIZER;

/**
 * User
 *
 * @ORM\Table(name="banner")
 * @ORM\Entity(repositoryClass="Fbn\Doctrine\SmartRepository")
 * @ORM\ChangeTrackingPolicy("NOTIFY")
 */
class Banner extends \Fbn\Doctrine\SmartModel
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
     * @var string
     *
     * @ORM\Column(name="caption", type="string", length=150)
     */
    protected $caption;
    
    /**
     * @var string
     *
     * @ORM\Column(name="url", type="string", length=250)
     */
    protected $url;
    
    /**
     * @ORM\ManyToOne(targetEntity="Campaign", inversedBy="bannerList")
     **/
    protected $campaign;
    
}
