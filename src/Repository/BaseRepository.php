<?php

namespace App\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Component\HttpFoundation\Request;

abstract class BaseRepository extends ServiceEntityRepository
{
    public function getQueryCollection(Request $request)
    {
        $column = $this->getOrderColumn($request->get('order', 'id'));
        $dir = $request->get('dir', 'DESC');
        if ((strtoupper($dir) != 'ASC') || (strtoupper($dir) != 'DESC')) {
            $dir = 'DESC';
        }

        return [
            'rows' => $this->getEntityManager()
                ->createQuery($this->getQueryRows() . ' ORDER BY ' . $column . ' ' . $dir)
                ->setParameter(1, '%' . $request->get('search', '') . '%')
                ->setMaxResults($request->get('length', 100))
                ->setFirstResult($request->get('start', 0))
                ->getResult(),
            'total' => $this->getEntityManager()
                ->createQuery($this->getQueryTotal())
                ->getSingleScalarResult(),
            'filtered' => $this->getEntityManager()
                ->createQuery($this->getQueryFiltered())
                ->setParameter(1, '%' . $request->get('search', '') . '%')
                ->getSingleScalarResult(),
        ];
    }
}
