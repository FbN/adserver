<?php

namespace Adserver\Models;

use Doctrine\ORM\Mapping as ORM;
use \Doctrine\Common\Collections\ArrayCollection;
use JMS\Serializer\Annotation as SERIALIZER;
use Doctrine\ORM\Query\ResultSetMapping;
use Doctrine\ORM\Query\ResultSetMappingBuilder;

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
    
    static private function parseDomain($url){
    	if($url==null) return null;
    	$parse = parse_url($url);
    	return $parse['host'];
    }
    
    static function deliverNext(\Doctrine\ORM\EntityManager $em, $cookie=null, $referer=null){
    	
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
EOT;
    	
    	$rsm = new ResultSetMapping();
    	$query = $em->createNativeQuery("SELECT DISTINCT count(b.id) as tot ".$sql, $rsm);
    	$query->setParameter('now', $now);
    	$query->setParameter('cookie', $cookie);
    	$query->setParameter('refererfull', $referer);
    	$query->setParameter('refererdomain', self::parseDomain($referer));
    	$rsm->addScalarResult('tot','tot');
    	$tot = $query->getSingleResult()['tot'];
    	
    	$rsm = new ResultSetMappingBuilder($em);
    	$rand = rand(0,$tot-1);
    	$query = $em->createNativeQuery("SELECT b.* ".$sql. " LIMIT $rand,1", $rsm);
    	$query->setParameter('now', $now);
    	$query->setParameter('cookie', $cookie);
    	$query->setParameter('refererfull', $referer);
    	$query->setParameter('refererdomain', self::parseDomain($referer));
    	$rsm->addRootEntityFromClassMetadata('\\Adserver\\Models\\Banner', 'b');
    	$banner = $query->getSingleResult();
    	
    	return $banner;
    	
    }
    
}
