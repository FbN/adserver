<?php

namespace Adserver\Models;

use Doctrine\ORM\Mapping as ORM;
use \Doctrine\Common\Collections\ArrayCollection;
use JMS\Serializer\Annotation as SERIALIZER;
use Doctrine\ORM\Query\ResultSetMapping;
use Doctrine\ORM\Query\ResultSetMappingBuilder;
use Doctrine\ORM\EntityManager;

/**
 * User
 *
 * @ORM\Table(name="banner",indexes={@ORM\Index(name="search_width", columns={"width"}), @ORM\Index(name="search_height", columns={"height"})})
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
     * @var string
     *
     * @ORM\Column(name="file", type="string", length=25)
     */
    protected $file;
    
    /**
     * @var string
     *
     * @ORM\Column(name="width", type="integer")
     */
    protected $width;
    
    /**
     * @var string
     *
     * @ORM\Column(name="height", type="integer")
     */
    protected $height;    
    
    /**
     * @ORM\ManyToOne(targetEntity="Campaign", inversedBy="bannerList")
     * @ORM\JoinColumn(name="campaign_id", referencedColumnName="id", onDelete="CASCADE")
     **/
    protected $campaign;
    
    public function getBanner(){
    	return "http://lorempixel.com/".$this->width."/".$this->height."/";
    }
    
    static private function parseDomain($url){
    	if($url==null) return null;
    	$parse = parse_url($url);
    	return $parse['host'];
    }
    
    public function incDelivered(EntityManager $em){
    	$campaignTable = $em->getClassMetadata('\\Adserver\\Models\\Campaign')->getTableName();    	
    	$cid = current($em->getUnitOfWork()->getEntityIdentifier($this->getCampaign()));
    	$em->getConnection()->executeUpdate('UPDATE '.$campaignTable.' SET delivered = delivered+1 WHERE id = ?', array($cid));    	
    }
    
    static function deliverNext(\Doctrine\ORM\EntityManager $em, $width, $height, $cookie=null, $referer=null){
    	
    	$now = new \DateTime();
    	    	
    	$bannerTable = $em->getClassMetadata('\\Adserver\\Models\\Banner')->getTableName();
    	$campaignTable = $em->getClassMetadata('\\Adserver\\Models\\Campaign')->getTableName();
    	$campaignRuntimeTable = $em->getClassMetadata('\\Adserver\\Models\\CampaignRuntime')->getTableName();
    	$campaignRefererFilter = $em->getClassMetadata('\\Adserver\\Models\\CampaignRefererFilter')->getTableName();

    	$sql =  <<<EOT
				FROM      `$campaignRuntimeTable` rt
				LEFT JOIN `$campaignTable` c
				ON        ( c.id = rt.campaign_id )
				LEFT JOIN `$campaignRefererFilter` rf
				ON        ( c.id = rf.campaign_id )
				JOIN `$bannerTable` b
				WHERE     c.active = true
				AND		  c.delivered<c.goal
				AND       rt.start <= :now
				AND       rt.end >= :now
				AND       (
				                    c.time_filter_active = false
				          OR        (d_sunday = true AND h16 = true))
				AND       (			c.cookie IS NULL || c.cookie = :cookie)
				AND       ((
				                              rf.campaign_id IS NULL)
				          OR        ( (
				                                        rf.hostname_only=true
				                              AND       rf.referer = :refererdomain)
				                    OR        (
				                                        rf.hostname_only=false
				                              AND       rf.referer = :refererfull) ))
				AND (c.id=b.campaign_id)
    			AND ( b.width=:width AND b.height=:height )
EOT;
    	
    	$rsm = new ResultSetMapping();
    	$query = $em->createNativeQuery("SELECT DISTINCT count(b.id) as tot ".$sql, $rsm);
    	$query->setParameter('now', $now);
    	$query->setParameter('cookie', $cookie);
    	$query->setParameter('refererfull', $referer);
    	$query->setParameter('refererdomain', self::parseDomain($referer));
    	$query->setParameter('width', $width);
    	$query->setParameter('height', $height);
    	$rsm->addScalarResult('tot','tot');
    	$tot = $query->getSingleResult()['tot'];
    	
    	$rsm = new ResultSetMappingBuilder($em);
    	$rand = rand(0,$tot-1);
    	$query = $em->createNativeQuery("SELECT b.* ".$sql. " LIMIT $rand,1", $rsm);
    	$query->setParameter('now', $now);
    	$query->setParameter('cookie', $cookie);
    	$query->setParameter('width', $width);
    	$query->setParameter('height', $height);
    	$query->setParameter('refererfull', $referer);
    	$query->setParameter('refererdomain', self::parseDomain($referer));
    	$rsm->addRootEntityFromClassMetadata('\\Adserver\\Models\\Banner', 'b');
    	$banner = $query->getSingleResult();
    	
    	return $banner;
    	
    }
    
}
