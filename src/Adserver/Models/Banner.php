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
    
    static function microtime_float()
    {
    	list($usec, $sec) = explode(" ", microtime());
    	return ((float)$usec + (float)$sec);
    }
    
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
    	
		
		$daycol  = 'd_'.strtolower($now->format('l'));
		$hourcol = 'h'.strtolower($now->format('G'));
		$sql =  <<<EOT
			SELECT DISTINCT b.id
			FROM      `$campaignRuntimeTable` rt
			JOIN      `$campaignTable` c 
			JOIN      `$bannerTable` b
			left join `$campaignRefererFilter` rf ON (c.id = rf.campaign_id)
			WHERE           	
				c.id = b.campaign_id
			AND c.id = rt.campaign_id
			AND c.active = true
			AND (c.delivered<c.goal)
			AND	((c.time_filter_active = true AND c.$daycol = true AND c.$hourcol = true) OR (c.time_filter_active = false))      
			AND ( b.width=:width AND b.height=:height )
			AND (rt.start <= :now AND rt.`end` >= :now)
EOT;

		if($referer){
			$sql .= ' AND ((rf.id is null) OR ((rf.hostname_only=true AND rf.referer = :refererdomain) OR (rf.hostname_only=false AND rf.referer = :refererfull))) ';
		}else{
			$sql .= ' AND (rf.id is null) ';
		}
		
		if($cookie){
			$sql .= ' AND ( c.cookie IS NULL || c.cookie = :cookie) ';
		}
    	
    	$stmt = $em->getConnection()->prepare($sql);
    	$stmt->bindValue('now', $now, "datetime");
    	$stmt->bindValue('width', $width);
    	$stmt->bindValue('height', $height);
    	if($cookie){
    		$stmt->bindValue('cookie', $cookie);
    	}
    	if($referer){
	    	$stmt->bindValue('refererfull', $referer);
	    	$stmt->bindValue('refererdomain', self::parseDomain($referer));
    	}    	
    	$stmt->execute();
    	$ids = $stmt->fetchAll(\PDO::FETCH_COLUMN);
    	
    	return Banner::find($em, array_rand($ids));
    	
    }
    
}
