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
 * @ORM\HasLifecycleCallbacks
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
     * @ORM\JoinColumn(name="campaign_id", referencedColumnName="id", onDelete="CASCADE")
     **/
    protected $campaign;
    
    /**
     * @ORM\PrePersist 
     * @ORM\PreUpdate
     */
    public function assertNotOverlapping(\Doctrine\ORM\Event\LifecycleEventArgs $event) {
    	if (!$this->start || !$this->end) throw new \RangeException('Start date and End date must be set');
    	if ($this->start>$this->end) throw new \RangeException('Start date must be beofre of End date');
    	$em = $event->getEntityManager();
    	$qb = $em->createQueryBuilder();
	    
	    $overlaps = $qb->select('r.id')
	    	->from('\\Adserver\\Models\\CampaignRuntime', 'r')
	    	->where($qb->expr()->andX(
	    		   $qb->expr()->eq('r.campaign', '?3'),
	    		   $qb->expr()->lte('r.start', '?2'),
			       $qb->expr()->gte('r.end', '?1')
			   ))
			->setMaxResults( 1 )
			->setParameter(1, $this->getStart())
			->setParameter(2, $this->getEnd())
			->setParameter(3, $this->getCampaign()->getId())
	    	->getQuery()
	    	->getOneOrNullResult(\Doctrine\ORM\AbstractQuery::HYDRATE_SCALAR);
	    
	    if($overlaps) throw new \RuntimeException("New runtime for campaign ".$this->campaign->getId()." overlaps with runtime ".$overlaps['id']);
    }    
    
}
