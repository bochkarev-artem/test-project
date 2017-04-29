<?php
/**
 * @author Artyom Bochkarev
 */

namespace AppBundle\Routing;

use Symfony\Cmf\Component\Routing\NestedMatcher\FinalMatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\Routing\Matcher\RequestMatcherInterface;
use Symfony\Component\Routing\RequestContext;

class RequestMatcher implements RequestMatcherInterface
{
    /**
     * @var RequestContext
     */
    protected $context;

    /**
     * @var RouteProvider
     */
    protected $routeProvider;

    /**
     * @var FinalMatcherInterface
     */
    protected $finalMatcher;

    /**
     * @var array
     */
    protected $cache = [];

    /**
     * @param RouteProvider $routeProvider
     */
    public function __construct(RouteProvider $routeProvider)
    {
        $this->routeProvider = $routeProvider;
    }

    /**
     * {@inheritDoc}
     */
    public function matchRequest(Request $request)
    {
        $requestHash = spl_object_hash($request);
        if (isset($this->cache[$requestHash])) {
            $collection = $this->cache[$requestHash];
        } else {
            $collection = $this->routeProvider->getRouteCollectionForRequest($request);
            $this->cache[$requestHash] = $collection;
        }
        if (!count($collection)) {
            throw new ResourceNotFoundException();
        }

        $attributes = $this->finalMatcher->finalMatch($collection, $request);

        return $attributes;
    }

    /**
     * {@inheritDoc}
     */
    public function setContext(RequestContext $context)
    {
        $this->context = $context;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getContext()
    {
        return $this->context;
    }

    /**
     * @param FinalMatcherInterface $final
     *
     * @return $this
     */
    public function setFinalMatcher(FinalMatcherInterface $final)
    {
        $this->finalMatcher = $final;

        return $this;
    }
}
