<?php
/**
 * @author Artyom Bochkarev
 */

namespace AppBundle\Routing;

use Elastica\Query\BoolQuery;
use Elastica\Query\Term;
use Elastica\Type;
use Symfony\Cmf\Component\Routing\RouteProviderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Exception\RouteNotFoundException;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

class RouteProvider implements RouteProviderInterface
{
    /**
     * @var Type
     */
    private $repository;

    /**
     * @param Type $repository
     */
    public function __construct(Type $repository)
    {
        $this->repository = $repository;
    }

    /**
     * {@inheritDoc}
     */
    public function getRouteCollectionForRequest(Request $request)
    {
        $url  = ltrim(rawurldecode($request->getPathInfo()), '/');
        $searchUrl = rtrim(preg_replace('#^(.*?)(?:\.html)?$#iu', '$1', $url), '/');
        $boolQuery = new BoolQuery();
        $pathQuery = new Term();
        $pathQuery->setTerm('path', rawurldecode($searchUrl));
        $boolQuery->addMust($pathQuery);

        $results = $this->repository->search($boolQuery)->getResults();

        $collection = new RouteCollection();
        if ($results) {
            $routeData = $results[0]->getData();
            if ($url != $searchUrl) {
                $routeData = [
                    'params' => [
                        'defaults'     => [
                            '_controller' => 'FrameworkBundle:Redirect:urlRedirect',
                            'path'        => '/' . $searchUrl,
                            'permanent'   => true,
                        ],
                        'requirements' => [],
                        'options'      => [],
                    ]
                ];
            }

            $internalParams = [
                '_path'   => $url,
                '_params' => array_keys($routeData['params']['defaults']),
            ];

            $collection->add(
                'dynamic_route',
                new Route(
                    $url,
                    array_merge($routeData['params']['defaults'], $internalParams),
                    $routeData['params']['requirements'],
                    $routeData['params']['options']
                )
            );
        }

        return $collection;
    }

    /**
     * {@inheritDoc}
     */
    public function getRouteByName($name, $parameters = [])
    {
        throw new RouteNotFoundException;
    }

    /**
     * {@inheritDoc}
     */
    public function getRoutesByNames($names, $parameters = [])
    {
        return [];
    }
}
