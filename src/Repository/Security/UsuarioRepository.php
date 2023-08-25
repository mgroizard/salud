<?php

namespace App\Repository\Security;

use App\Entity\Security\Usuario;
use App\Repository\BaseRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Request;

/**
 * @extends ServiceEntityRepository<Usuario>
 *
 * @method Usuario|null find($id, $lockMode = null, $lockVersion = null)
 * @method Usuario|null findOneBy(array $criteria, array $orderBy = null)
 * @method Usuario[]    findAll()
 * @method Usuario[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UsuarioRepository extends BaseRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Usuario::class);
    }

    public function add(Usuario $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Usuario $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function getQueryRows()
    {
        return 'SELECT u FROM  ' . Usuario::class . ' u WHERE (u.id LIKE ?1 OR u.nombre LIKE ?1 OR u.apellido LIKE ?1)';
    }

    public function getQueryTotal()
    {
        return 'SELECT COUNT(DISTINCT u) FROM  ' . Usuario::class . ' u ';
    }

    public function getQueryFiltered()
    {
        return 'SELECT COUNT(DISTINCT u) FROM  ' . Usuario::class . ' u WHERE (u.id LIKE ?1 OR u.nombre LIKE ?1 OR u.apellido LIKE ?1)';
    }

    public function getOrderColumn($column)
    {
        if(property_exists(Usuario::class,$column)){
            return 'u. ' . $column;
        }

        return 'u.id';
    }
}
