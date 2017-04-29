<?php

namespace AppBundle\Controller;

use AppBundle\Model\QueryParams;
use Pagerfanta\Adapter\ElasticaAdapter;
use Pagerfanta\Pagerfanta;
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
        $defaultPerPage = $this->getParameter('default_per_page');
        $query          = $request->get('query');
        $page           = $request->get('page', 1);

        $queryParams = new QueryParams();
        $queryParams->setSearchQuery($query);

        $data = $this->prepareViewData($queryParams, [
            'page'     => $page,
            'per_page' => $defaultPerPage,
        ]);

        $data = array_merge($data, [
            'query' => $query,
        ]);

        return $this->render('@App/Product/list.html.twig', $data);
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
        $query        = $queryService->buildQuery($queryParams);
        $queryResult  = $queryService->query($query);
        $products     = $queryResult->getResults();

        if (!$product = array_shift($products)) {
            throw $this->createNotFoundException();
        } else {
            $product = $product->getSource();
        }

        return $this->render('@App/Product/view.html.twig', [
            'product' => $product,
        ]);
    }

    /**
     * @param QueryParams $queryParams
     * @param array       $params
     *
     * @return array
     */
    protected function prepareViewData($queryParams, $params)
    {
        $queryService = $this->get('query_service');
        $query        = $queryService->buildQuery($queryParams);
        $searchable   = $this->get('fos_elastica.index.products.product');
        $adapter      = new ElasticaAdapter($searchable, $query);
        $paginator    = new Pagerfanta($adapter);

        $paginator->setMaxPerPage($params['per_page']);
        $paginator->setCurrentPage($params['page']);

        return ['products' => $paginator,];
    }
}
