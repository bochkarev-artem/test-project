<?php

namespace AppBundle\Controller;

use AppBundle\Model\QueryParams;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ProductController extends Controller
{
    /**
     * @param Request $request
     *
     * @return Response
     */
    public function indexAction(Request $request)
    {
        $query       = $request->get('query');
        $page        = $request->get('page', 1);
        $queryParams = new QueryParams();
        $queryParams->setSearchQuery($query);

        $queryService = $this->get('query_service');
        $paginator    = $queryService->find($queryParams, $page);

        return $this->render('@App/Product/list.html.twig', [
            'products' => $paginator,
            'query'    => $query,
        ]);
    }

    /**
     * @param integer $id
     *
     * @return Response
     */
    public function showProductAction($id)
    {
        $queryParams = new QueryParams();
        $queryParams->setFilterId($id);

        $queryService = $this->get('query_service');
        $paginator    = $queryService->find($queryParams, 1);

        return $this->render('@App/Product/view.html.twig', [
            'products' => $paginator,
        ]);
    }
}
