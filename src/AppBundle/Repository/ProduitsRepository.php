<?php

namespace AppBundle\Repository;




/**
 * ProduitsRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class ProduitsRepository extends \Doctrine\ORM\EntityRepository
{

    /**
     * @param $categorie
     * @return mixed
     */
    function produitsByCategorie($categorie){
        /*
            $query = $this->getEntityManager()->createQuery(
                'SELECT u
                    FROM Produits u
                    WHERE u.categorie = :categorie
                    ORDER BY u.id ASC'
            )->setParameter('categorie', $categorie);

           */

        $query = $this->createQueryBuilder('u')
            ->where('u.categorie = :categorie')
            ->andWhere('u.disponible = 1')
            ->setParameter('categorie', $categorie)
            ->orderBy('u.id', 'ASC')
            ->getQuery();

            $produits = $query->getResult();
            return $produits;
        }



    /**
     * @param $chaine
     * @return mixed
     */
    function rechercheProduit($chaine){
        /*
                    $query = $this->getEntityManager()->createQuery(
                        'SELECT u
                            FROM Produits u
                            WHERE u.nom like :chaine
                            ORDER BY u.id ASC'
                  )->setParameter('chaine', '%'.$chaine.'%');

         */

        $query = $this->createQueryBuilder('u')
            ->where('u.nom like :chaine')
            ->setParameter('chaine', '%'.$chaine.'%')
            ->orderBy('u.id', 'ASC')
            ->getQuery();

        $produits = $query->getResult();
        return $produits;
    }


    /**
     * @param
     * @return mixed
     */
    function findArray($array){

        $query = $this->createQueryBuilder('u')
            ->where('u.id IN(:array)')
            ->setParameter('array', $array)
            ->orderBy('u.id', 'ASC')
            ->getQuery();

        $produits = $query->getResult();
        return $produits;
    }


}
