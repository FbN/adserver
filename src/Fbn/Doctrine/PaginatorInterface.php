<?php
namespace Fbn\Doctrine;


use Symfony\Component\HttpFoundation\Request;
use Doctrine\Common\Collections\Collection;
use Fbn\Utils\RoutedUrl;
use Doctrine\Common\Collections\ArrayCollection;

interface PaginatorInterface {

	/**
	 *
	 * @return Doctrine\ORM\QueryBuilder
	 */
	public function getQueryBuilder();

	public function hidrate(\Symfony\Component\HttpFoundation\Request $request);

	/**
	 * @return Doctrine\Common\Collections\ArrayCollection
	 */
	public function getCollection();

	/**
	 * @param unknown $routedUrl
	 * @return array
	 */
	public function getPaging( \Fbn\Utils\RoutedUrl $routedUrl);

	/**
	 * @param unknown $routedUrl
	 * @param unknown $urlGenerator
	 */
	public function renderPaging( \Fbn\Utils\RoutedUrl $routedUrl, \Symfony\Component\Routing\Generator\UrlGeneratorInterface  $urlGenerator);

}